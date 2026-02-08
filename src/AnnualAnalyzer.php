<?php

class AnnualAnalyzer {
    private $conn;
    private $year;
    private $currency;

    public function __construct($conn, $year, $currency = 'MAD') {
        $this->conn = $conn;
        $this->year = (int)$year;
        $this->currency = $currency;
    }

    public function getAnalysis() {
        $stats = $this->getYearlyStats();
        $prevStats = $this->getPreviousYearStats();
        $monthlyStats = $this->getMonthlyStats();
        $topProducts = $this->getTopProducts();
        
        $debtStats = $this->getDebtStats();

        return [
            'year' => $this->year,
            'stats' => $stats,
            'debt' => $debtStats,
            'growth' => $this->calculateGrowth($stats, $prevStats),
            'monthly' => $monthlyStats,
            'top_products' => $topProducts,
            'advice' => $this->generateAdvice($stats, $prevStats, $monthlyStats, $debtStats),
            'score' => $this->calculateHealthScore($stats, $prevStats, $debtStats)
        ];
    }

    private function getDebtStats() {
        // 1. Current Total Outstanding Debt (All time)
        $sqlTotal = "SELECT COALESCE(SUM(balance), 0) as total_debt, COUNT(*) as debtor_count FROM customers WHERE balance > 0";
        $totalRes = $this->conn->query($sqlTotal)->fetch_assoc();

        // 2. Debt generated this year (Unpaid invoices from this year)
        $startDate = "{$this->year}-01-01 00:00:00";
        $endDate = "{$this->year}-12-31 23:59:59";
        
        $sqlYearlyDebt = "SELECT COALESCE(SUM(total - paid_amount), 0) as yearly_debt 
                          FROM invoices 
                          WHERE created_at BETWEEN ? AND ? 
                          AND payment_status != 'paid'";
        
        $stmt = $this->conn->prepare($sqlYearlyDebt);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $yearlyDebt = $stmt->get_result()->fetch_assoc()['yearly_debt'];

        return [
            'total_outstanding' => (float)$totalRes['total_debt'],
            'debtor_count' => (int)$totalRes['debtor_count'],
            'yearly_debt_creation' => (float)$yearlyDebt
        ];
    }

    private function getYearlyStats() {
        $startDate = "{$this->year}-01-01 00:00:00";
        $endDate = "{$this->year}-12-31 23:59:59";

        // Revenue & Orders
        $sql = "SELECT 
                    COUNT(DISTINCT i.id) as total_orders,
                    COALESCE(SUM(i.total), 0) as total_revenue,
                    COALESCE(SUM(i.delivery_cost), 0) as total_delivery,
                    COUNT(DISTINCT i.customer_id) as total_customers
                FROM invoices i
                WHERE i.created_at BETWEEN ? AND ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        // Refunds
        $sqlRefunds = "SELECT COALESCE(SUM(amount), 0) as total_refunds FROM refunds WHERE created_at BETWEEN ? AND ?";
        $stmtRefunds = $this->conn->prepare($sqlRefunds);
        $stmtRefunds->bind_param("ss", $startDate, $endDate);
        $stmtRefunds->execute();
        $refunds = $stmtRefunds->get_result()->fetch_assoc()['total_refunds'];

        // COGS (Cost of Goods Sold)
        $sqlCOGS = "SELECT COALESCE(SUM(ii.quantity * ii.cost_price), 0) as total_cogs
                    FROM invoice_items ii
                    JOIN invoices i ON ii.invoice_id = i.id
                    WHERE i.created_at BETWEEN ? AND ?";
        $stmtCOGS = $this->conn->prepare($sqlCOGS);
        $stmtCOGS->bind_param("ss", $startDate, $endDate);
        $stmtCOGS->execute();
        $cogs = $stmtCOGS->get_result()->fetch_assoc()['total_cogs'];

        // Expenses
        $sqlExp = "SELECT COALESCE(SUM(amount), 0) as total_expenses FROM expenses WHERE expense_date BETWEEN ? AND ?";
        $dStart = "{$this->year}-01-01";
        $dEnd = "{$this->year}-12-31";
        $stmtExp = $this->conn->prepare($sqlExp);
        $stmtExp->bind_param("ss", $dStart, $dEnd);
        $stmtExp->execute();
        $expenses = $stmtExp->get_result()->fetch_assoc()['total_expenses'];

        // Calculations
        $netRevenue = $result['total_revenue'] - $refunds;
        $grossProfit = $netRevenue - $cogs - $expenses; // Profit after COGS and Expenses (Operating Profit)
        
        return [
            'total_orders' => (int)$result['total_orders'],
            'total_revenue' => (float)$netRevenue, // Net Revenue
            'gross_revenue' => (float)$result['total_revenue'],
            'total_refunds' => (float)$refunds,
            'total_cogs' => (float)$cogs,
            'total_expenses' => (float)$expenses,
            'net_profit' => (float)$grossProfit,
            'total_customers' => (int)$result['total_customers'],
            'profit_margin' => $netRevenue > 0 ? ($grossProfit / $netRevenue) * 100 : 0,
            'avg_order_value' => $result['total_orders'] > 0 ? $netRevenue / $result['total_orders'] : 0
        ];
    }

    private function getPreviousYearStats() {
        $prevYear = $this->year - 1;
        $analyzer = new AnnualAnalyzer($this->conn, $prevYear, $this->currency);
        return $analyzer->getYearlyStats(); // Basic recursion, safe as it goes down only 1 level usually
    }

    private function calculateGrowth($current, $prev) {
        $revenueGrowth = 0;
        $profitGrowth = 0;
        $ordersGrowth = 0;

        if ($prev['total_revenue'] > 0) {
            $revenueGrowth = (($current['total_revenue'] - $prev['total_revenue']) / $prev['total_revenue']) * 100;
        }
        
        if ($prev['net_profit'] > 0) { // Avoid division by zero or negative flip issues
             $profitGrowth = (($current['net_profit'] - $prev['net_profit']) / abs($prev['net_profit'])) * 100;
        }

        if ($prev['total_orders'] > 0) {
            $ordersGrowth = (($current['total_orders'] - $prev['total_orders']) / $prev['total_orders']) * 100;
        }

        return [
            'revenue_growth' => $revenueGrowth,
            'profit_growth' => $profitGrowth,
            'orders_growth' => $ordersGrowth
        ];
    }

    private function getMonthlyStats() {
        $startDate = "{$this->year}-01-01 00:00:00";
        $endDate = "{$this->year}-12-31 23:59:59";

        $sql = "SELECT 
                    MONTH(created_at) as month, 
                    SUM(total) as revenue 
                FROM invoices 
                WHERE created_at BETWEEN ? AND ? 
                GROUP BY MONTH(created_at) 
                ORDER BY MONTH(created_at)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();

        $months = [];
        $bestMonth = ['month' => 0, 'revenue' => 0];
        $worstMonth = ['month' => 0, 'revenue' => PHP_FLOAT_MAX];

        while ($row = $result->fetch_assoc()) {
            $rev = (float)$row['revenue'];
            $months[$row['month']] = $rev;
            
            if ($rev > $bestMonth['revenue']) {
                $bestMonth = ['month' => $row['month'], 'revenue' => $rev];
            }
            if ($rev < $worstMonth['revenue'] && $rev > 0) {
                $worstMonth = ['month' => $row['month'], 'revenue' => $rev];
            }
        }
        
        // Fill missing months with 0
        for($i=1; $i<=12; $i++) {
            if(!isset($months[$i])) $months[$i] = 0;
        }

        // If no sales at all
        if ($worstMonth['revenue'] == PHP_FLOAT_MAX) $worstMonth['revenue'] = 0;

        return [
            'data' => $months,
            'best_month' => $bestMonth,
            'worst_month' => $worstMonth
        ];
    }

    private function getTopProducts() {
        $startDate = "{$this->year}-01-01 00:00:00";
        $endDate = "{$this->year}-12-31 23:59:59";

        $sql = "SELECT 
                    p.name, 
                    SUM(ii.quantity) as qty,
                    SUM(ii.quantity * ii.price) as revenue
                FROM invoice_items ii
                JOIN invoices i ON ii.invoice_id = i.id
                LEFT JOIN products p ON ii.product_id = p.id
                WHERE i.created_at BETWEEN ? AND ?
                GROUP BY ii.product_id
                ORDER BY qty DESC
                LIMIT 5";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        return $products;
    }

    private function getTopExpenseCategory() {
        $startDate = "{$this->year}-01-01";
        $endDate = "{$this->year}-12-31";

        $sql = "SELECT category, SUM(amount) as total 
                FROM expenses 
                WHERE expense_date BETWEEN ? AND ? 
                GROUP BY category 
                ORDER BY total DESC 
                LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
        return null;
    }

    private function generateAdvice($stats, $prevStats, $monthly, $debtStats = null) {
        $advice = [];

        // Check if year is current year (Incomplete)
        if ($this->year == date('Y')) {
            $remaining = $this->getRemainingTime();
            $advice[] = [
                'type' => 'warning',
                'title' => __('advice_year_incomplete_title'),
                'text' => sprintf(__('advice_year_incomplete_text'), $remaining)
            ];
        }

        // 1. Profit Margin Analysis
        if ($stats['total_revenue'] == 0) {
             $advice[] = [
                'type' => 'info',
                'title' => __('advice_no_data_title'),
                'text' => __('advice_no_data_text')
            ];
            return $advice;
        }

        if ($stats['profit_margin'] < 10) {
            $advice[] = [
                'type' => 'danger',
                'title' => __('advice_margin_critical_title'),
                'text' => sprintf(__('advice_margin_critical_text'), number_format($stats['profit_margin'], 1) . "%")
            ];
        } elseif ($stats['profit_margin'] < 20) {
            $advice[] = [
                'type' => 'warning',
                'title' => __('advice_margin_low_title'),
                'text' => sprintf(__('advice_margin_low_text'), number_format($stats['profit_margin'], 1) . "%")
            ];
        } else {
             $advice[] = [
                'type' => 'success',
                'title' => __('advice_margin_good_title'),
                'text' => sprintf(__('advice_margin_good_text'), number_format($stats['profit_margin'], 1) . "%")
            ];
        }

        // 2. Expenses Analysis
        $expenseRatio = ($stats['total_expenses'] / $stats['total_revenue']) * 100;
        if ($expenseRatio > 30) {
            $advice[] = [
                'type' => 'warning',
                'title' => __('advice_expenses_high_title'),
                'text' => sprintf(__('advice_expenses_high_text'), number_format($expenseRatio, 1) . "%")
            ];
        }

        // 3. Seasonality
        if ($monthly['best_month']['revenue'] > 0) {
            $bestMonthName = $this->getMonthName($monthly['best_month']['month']);
            $advice[] = [
                'type' => 'info',
                'title' => __('advice_seasonality_title'),
                'text' => sprintf(__('advice_seasonality_text'), $bestMonthName)
            ];
        }

        // 4. Growth (if previous year exists)
        if ($prevStats['total_revenue'] > 0) {
            $growth = $this->calculateGrowth($stats, $prevStats);
            if ($growth['revenue_growth'] < 0) {
                 $advice[] = [
                    'type' => 'danger',
                    'title' => __('advice_growth_negative_title'),
                    'text' => sprintf(__('advice_growth_negative_text'), number_format(abs($growth['revenue_growth']), 1) . "%")
                ];
            } elseif ($growth['revenue_growth'] > 20) {
                 $advice[] = [
                    'type' => 'success',
                    'title' => __('advice_growth_positive_title'),
                    'text' => sprintf(__('advice_growth_positive_text'), number_format($growth['revenue_growth'], 1) . "%")
                ];
            }
        }

        // 5. Invoices & AOV
        if ($stats['avg_order_value'] < 50) { 
             $advice[] = [
                'type' => 'warning',
                'title' => __('advice_aov_low_title'),
                'text' => sprintf(__('advice_aov_low_text'), number_format($stats['avg_order_value'], 2) . ' ' . $this->currency)
            ];
        } else {
             $advice[] = [
                'type' => 'success',
                'title' => __('advice_aov_good_title'),
                'text' => sprintf(__('advice_aov_good_text'), number_format($stats['avg_order_value'], 2) . ' ' . $this->currency)
            ];
        }

        // 6. Returns Analysis
        if ($stats['gross_revenue'] > 0) {
            $returnRate = ($stats['total_refunds'] / $stats['gross_revenue']) * 100;
            if ($returnRate > 5) {
                 $advice[] = [
                    'type' => 'danger',
                    'title' => __('advice_returns_high_title'),
                    'text' => sprintf(__('advice_returns_high_text'), number_format($returnRate, 1) . "%")
                ];
            } else {
                 $advice[] = [
                    'type' => 'success',
                    'title' => __('advice_returns_low_title'),
                    'text' => sprintf(__('advice_returns_low_text'), number_format($returnRate, 1) . "%")
                ];
            }
        }

        // 7. Expenses Detailed Analysis
        if ($stats['total_expenses'] > 0) {
            $topCategory = $this->getTopExpenseCategory();
            if ($topCategory) {
                $catPercentage = ($topCategory['total'] / $stats['total_expenses']) * 100;
                $catNameTranslated = __($topCategory['category']);
                
                $advice[] = [
                    'type' => 'info',
                    'title' => sprintf(__('advice_expense_top_cat_title'), $catNameTranslated),
                    'text' => sprintf(__('advice_expense_top_cat_text'), $catNameTranslated, number_format($catPercentage, 1) . "%")
                ];
            }
        }

        // 8. Inventory Analysis
        $invStats = $this->getInventoryStats();
        
        if ($invStats['stagnant_count'] > 0) {
            $advice[] = [
                'type' => 'warning',
                'title' => __('advice_inventory_stagnant_title'),
                'text' => sprintf(__('advice_inventory_stagnant_text'), $invStats['stagnant_count'])
            ];
        }

        if ($invStats['turnover_ratio'] < 2 && $stats['total_orders'] > 0) {
             $advice[] = [
                'type' => 'info',
                'title' => __('advice_inventory_turnover_low_title'),
                'text' => __('advice_inventory_turnover_low_text')
            ];
        }

        if ($invStats['top_product_concentration'] > 50) {
             $advice[] = [
                'type' => 'warning',
                'title' => __('advice_inventory_concentration_title'),
                'text' => sprintf(__('advice_inventory_concentration_text'), 5, number_format($invStats['top_product_concentration'], 1) . "%")
            ];
        }

        // 9. Customer Analysis
        $custStats = $this->getCustomerStats();

        if ($custStats['total_customers'] > 10) { // Only if we have enough data
            if ($custStats['retention_rate'] < 10) {
                $advice[] = [
                    'type' => 'danger',
                    'title' => __('advice_customers_retention_low_title'),
                    'text' => sprintf(__('advice_customers_retention_low_text'), number_format($custStats['retention_rate'], 1) . "%")
                ];
            } elseif ($custStats['retention_rate'] > 40) {
                $advice[] = [
                    'type' => 'success',
                    'title' => __('advice_customers_retention_high_title'),
                    'text' => sprintf(__('advice_customers_retention_high_text'), number_format($custStats['retention_rate'], 1) . "%")
                ];
            }
        }

        if ($custStats['new_customers'] > 0) {
             $advice[] = [
                'type' => 'success',
                'title' => __('advice_customers_new_title'),
                'text' => sprintf(__('advice_customers_new_text'), $custStats['new_customers'])
            ];
        }

        // 10. Debt Analysis (New)
        if ($debtStats && $stats['total_revenue'] > 0) {
            $debtRatio = ($debtStats['total_outstanding'] / $stats['total_revenue']) * 100;

            if ($debtRatio > 20) {
                $advice[] = [
                    'type' => 'danger',
                    'title' => __('advice_debt_high_title'),
                    'text' => sprintf(__('advice_debt_high_text'), number_format($debtRatio, 1) . "%", number_format($debtStats['total_outstanding'], 2))
                ];
            } elseif ($debtRatio > 5) {
                $advice[] = [
                    'type' => 'warning',
                    'title' => __('advice_debt_moderate_title'),
                    'text' => sprintf(__('advice_debt_moderate_text'), number_format($debtRatio, 1) . "%")
                ];
            } else {
                $advice[] = [
                    'type' => 'success',
                    'title' => __('advice_debt_good_title'),
                    'text' => sprintf(__('advice_debt_good_text'), number_format($debtRatio, 1) . "%")
                ];
            }

            if ($debtStats['debtor_count'] > 10) {
                 $advice[] = [
                    'type' => 'info',
                    'title' => __('advice_debt_count_title'),
                    'text' => sprintf(__('advice_debt_count_text'), $debtStats['debtor_count'])
                ];
            }
        }

        return $advice;
    }

    private function getInventoryStats() {
        $startDate = "{$this->year}-01-01 00:00:00";
        $endDate = "{$this->year}-12-31 23:59:59";

        // 1. Stagnant Stock (Products with stock > 0 not sold in this year)
        // Note: This checks if product was EVER sold in the year.
        $sqlStagnant = "
            SELECT COUNT(*) as count 
            FROM products p
            WHERE p.quantity > 0 
            AND p.id NOT IN (
                SELECT DISTINCT ii.product_id
                FROM invoice_items ii
                JOIN invoices i ON ii.invoice_id = i.id
                WHERE i.created_at BETWEEN ? AND ?
            )
        ";
        $stmt = $this->conn->prepare($sqlStagnant);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $stagnantCount = $stmt->get_result()->fetch_assoc()['count'];

        // 2. Turnover Ratio (Approx: Total Items Sold / Current Total Stock)
        // A better formula uses average inventory, but we only have current stock.
        $sqlTotalStock = "SELECT COALESCE(SUM(quantity), 0) as total_stock FROM products";
        $totalStock = $this->conn->query($sqlTotalStock)->fetch_assoc()['total_stock'];
        
        $sqlSold = "
            SELECT COALESCE(SUM(ii.quantity), 0) as sold 
            FROM invoice_items ii 
            JOIN invoices i ON ii.invoice_id = i.id 
            WHERE i.created_at BETWEEN ? AND ?
        ";
        $stmt2 = $this->conn->prepare($sqlSold);
        $stmt2->bind_param("ss", $startDate, $endDate);
        $stmt2->execute();
        $soldCount = $stmt2->get_result()->fetch_assoc()['sold'];

        $turnoverRatio = $totalStock > 0 ? $soldCount / $totalStock : 0;

        // 3. Product Concentration (Revenue from top 5 products / Total Revenue)
        $topProducts = $this->getTopProducts();
        $topRevenue = 0;
        foreach($topProducts as $p) {
            $topRevenue += $p['revenue'];
        }
        
        // We need total revenue again or pass it. For simplicity, fetch it quickly or calculate relative to just products.
        // Let's use total revenue from products only to be accurate.
        $sqlTotalRev = "
            SELECT COALESCE(SUM(ii.quantity * ii.price), 0) as total_rev
            FROM invoice_items ii
            JOIN invoices i ON ii.invoice_id = i.id
            WHERE i.created_at BETWEEN ? AND ?
        ";
        $stmt3 = $this->conn->prepare($sqlTotalRev);
        $stmt3->bind_param("ss", $startDate, $endDate);
        $stmt3->execute();
        $totalProductRevenue = $stmt3->get_result()->fetch_assoc()['total_rev'];

        $concentration = $totalProductRevenue > 0 ? ($topRevenue / $totalProductRevenue) * 100 : 0;

        return [
            'stagnant_count' => $stagnantCount,
            'turnover_ratio' => $turnoverRatio,
            'top_product_concentration' => $concentration
        ];
    }

    private function getCustomerStats() {
        $startDate = "{$this->year}-01-01 00:00:00";
        $endDate = "{$this->year}-12-31 23:59:59";

        // 1. Retention Rate: % of customers who bought more than once in the period
        // We check unique customers in period, and how many of them have > 1 invoice.
        $sqlRetention = "
            SELECT 
                COUNT(customer_id) as total_active_customers,
                SUM(CASE WHEN order_count > 1 THEN 1 ELSE 0 END) as returning_customers
            FROM (
                SELECT customer_id, COUNT(id) as order_count
                FROM invoices
                WHERE created_at BETWEEN ? AND ?
                AND customer_id IS NOT NULL
                GROUP BY customer_id
            ) as sub
        ";
        $stmt = $this->conn->prepare($sqlRetention);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        
        $totalActive = $res['total_active_customers'];
        $returning = $res['returning_customers'];
        $retentionRate = $totalActive > 0 ? ($returning / $totalActive) * 100 : 0;

        // 2. New Customers (Approximation: Customers created in this year)
        // Assuming 'created_at' in customers table is reliable.
        $sqlNew = "SELECT COUNT(*) as new_count FROM customers WHERE created_at BETWEEN ? AND ?";
        $stmt2 = $this->conn->prepare($sqlNew);
        $stmt2->bind_param("ss", $startDate, $endDate);
        $stmt2->execute();
        $newCustomers = $stmt2->get_result()->fetch_assoc()['new_count'];

        return [
            'retention_rate' => $retentionRate,
            'new_customers' => $newCustomers,
            'total_customers' => $totalActive
        ];
    }

    private function calculateHealthScore($stats, $prevStats, $debtStats = null) {
        // If there's no significant data, score is 0
        if ($stats['total_revenue'] <= 0 && $stats['total_orders'] == 0) {
            return 0;
        }

        // Simple algorithm to score business health 0-100
        $score = 50; // Base score

        // Margin Impact
        if ($stats['profit_margin'] > 25) $score += 20;
        elseif ($stats['profit_margin'] > 15) $score += 10;
        elseif ($stats['profit_margin'] < 5) $score -= 20;

        // Growth Impact (if applicable)
        if ($prevStats['total_revenue'] > 0) {
            $growth = $this->calculateGrowth($stats, $prevStats);
            if ($growth['revenue_growth'] > 10) $score += 15;
            if ($growth['revenue_growth'] < -10) $score -= 15;
        }

        // Expenses Impact
        if ($stats['total_revenue'] > 0) {
            $expenseRatio = ($stats['total_expenses'] / $stats['total_revenue']) * 100;
            if ($expenseRatio < 15) $score += 10;
            if ($expenseRatio > 40) $score -= 15;
        }

        // Debt Impact
        if ($debtStats && $stats['total_revenue'] > 0) {
            $debtRatio = ($debtStats['total_outstanding'] / $stats['total_revenue']) * 100;
            if ($debtRatio > 20) $score -= 15; // High debt penalizes score
            elseif ($debtRatio < 5) $score += 5; // Low debt improves score
        }

        return max(0, min(100, $score));
    }

    private function getMonthName($monthNum) {
        $key = 'month_' . strtolower(date('F', mktime(0, 0, 0, $monthNum, 10)));
        // Assuming translation keys are like 'month_january', 'month_february'... 
        // Based on lang files, keys are 'month_january' etc.
        // Let's ensure monthNum matches
        $months = [
            1 => 'month_january', 2 => 'month_february', 3 => 'month_march', 4 => 'month_april',
            5 => 'month_may', 6 => 'month_june', 7 => 'month_july', 8 => 'month_august',
            9 => 'month_september', 10 => 'month_october', 11 => 'month_november', 12 => 'month_december'
        ];
        return isset($months[$monthNum]) ? __($months[$monthNum]) : '';
    }

    private function getRemainingTime() {
        $endOfYear = new \DateTime($this->year . '-12-31 23:59:59');
        $now = new \DateTime();
        
        // Ensure we don't get negative values if we are past the year (unlikely with year check, but for safety)
        if ($now > $endOfYear) {
            return '';
        }

        $diff = $now->diff($endOfYear);
        
        $parts = [];
        if ($diff->m > 0) {
            $parts[] = $diff->m . ' ' . __('time_months_plural');
        }
        if ($diff->d > 0) {
             $parts[] = $diff->d . ' ' . __('time_days_plural');
        }
        
        // Fallback if less than a day
        if (empty($parts) && $diff->h > 0) {
             $parts[] = $diff->h . ' ' . __('time_hours_short');
        } elseif (empty($parts)) {
             return "0 " . __('time_days_plural');
        }
        
        return implode(', ', $parts);
    }
}
