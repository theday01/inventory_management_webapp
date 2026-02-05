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
        
        return [
            'year' => $this->year,
            'stats' => $stats,
            'growth' => $this->calculateGrowth($stats, $prevStats),
            'monthly' => $monthlyStats,
            'top_products' => $topProducts,
            'advice' => $this->generateAdvice($stats, $prevStats, $monthlyStats),
            'score' => $this->calculateHealthScore($stats, $prevStats)
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

    private function generateAdvice($stats, $prevStats, $monthly) {
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

        return $advice;
    }

    private function calculateHealthScore($stats, $prevStats) {
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
