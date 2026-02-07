<?php
require_once 'session.php';
require_once 'db.php';
require_once 'src/language.php';

// Only admin can access expenses
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$page_title = __('expenses_management');
$current_page = 'expenses.php';

require_once 'src/header.php';
require_once 'src/sidebar.php';

// Fetch Currency
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';
?>

<main class="flex-1 flex flex-col relative overflow-hidden bg-dark transition-all duration-300">
    <header class="bg-dark-surface/50 backdrop-blur-md border-b border-white/5 p-6 sticky top-0 z-20">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                    <span class="material-icons-round text-red-500">payments</span>
                    <?php echo __('expenses_management'); ?>
                </h2>
                <p class="text-sm text-gray-400 mt-1"><?php echo __('track_and_record_expenses'); ?></p>
            </div>

            <button onclick="openAddExpenseModal()" class="bg-primary hover:bg-primary-hover text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all flex items-center gap-2 w-full md:w-auto justify-center md:justify-start">
                <span class="material-icons-round">add</span>
                <?php echo __('add_new_expense'); ?>
            </button>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto p-4 md:p-6">
        <!-- Expenses Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-6 mb-8">
            <div class="bg-dark-surface/60 border border-white/5 p-6 rounded-2xl">
                <p class="text-sm text-gray-400 mb-1"><?php echo __('today_expenses'); ?></p>
                <h3 id="today-expenses" class="text-2xl font-bold text-white">0.00 <span class="text-sm text-gray-500 font-normal"><?php echo $currency; ?></span></h3>
            </div>
            <div class="bg-dark-surface/60 border border-white/5 p-6 rounded-2xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-1 h-full bg-primary/40 group-hover:bg-primary transition-all"></div>
                <p id="cycle-label" class="text-sm text-primary font-bold mb-1"><?php echo __('current_cycle_expenses'); ?></p>
                <h3 id="cycle-expenses" class="text-2xl font-bold text-white">0.00 <span class="text-sm text-gray-500 font-normal"><?php echo $currency; ?></span></h3>
                <p id="cycle-dates" class="text-[10px] text-gray-500 mt-1">-</p>
            </div>
            <div class="bg-dark-surface/60 border border-white/5 p-6 rounded-2xl">
                <p class="text-sm text-gray-400 mb-1"><?php echo __('this_month_expenses'); ?></p>
                <h3 id="month-expenses" class="text-2xl font-bold text-white">0.00 <span class="text-sm text-gray-500 font-normal"><?php echo $currency; ?></span></h3>
            </div>
            <div class="bg-dark-surface/60 border border-white/5 p-6 rounded-2xl">
                <p class="text-sm text-gray-400 mb-1"><?php echo __('total_expenses_label'); ?></p>
                <h3 id="total-expenses" class="text-2xl font-bold text-white">0.00 <span class="text-sm text-gray-500 font-normal"><?php echo $currency; ?></span></h3>
            </div>
        </div>

        <!-- Expenses Table -->
        <div class="bg-transparent md:bg-dark-surface/40 md:border border-white/5 rounded-2xl overflow-hidden md:backdrop-blur-md">
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse block md:table">
                    <thead class="hidden md:table-header-group">
                        <tr class="bg-white/5 text-gray-400 text-xs uppercase tracking-wider">
                            <th class="px-6 py-4 font-bold"><?php echo __('date'); ?></th>
                            <th class="px-6 py-4 font-bold"><?php echo __('title'); ?></th>
                            <th class="px-6 py-4 font-bold"><?php echo __('category'); ?></th>
                            <th class="px-6 py-4 font-bold"><?php echo __('amount'); ?></th>
                            <th class="px-6 py-4 font-bold"><?php echo __('notes'); ?></th>
                            <th class="px-6 py-4 font-bold text-left"><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="expenses-table-body" class="block md:table-row-group divide-y divide-white/5 md:divide-white/5">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div id="pagination" class="p-6 border-t border-white/5 flex justify-center items-center gap-2 bg-dark-surface/40 md:bg-transparent rounded-2xl md:rounded-none mt-4 md:mt-0"></div>
        </div>
    </div>
</main>

<!-- Add Expense Modal -->
<div id="expense-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-dark-surface border border-white/10 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden animate-scaleIn max-h-[90vh] overflow-y-auto">
        <div class="p-4 md:p-6 border-b border-white/5 flex items-center justify-between sticky top-0 bg-dark-surface z-10">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="material-icons-round text-primary">add_circle</span>
                <?php echo __('add_new_expense'); ?>
            </h3>
            <button onclick="closeExpenseModal()" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        
        <form id="expense-form" class="p-4 md:p-6 space-y-4">
            <div>
                <label class="block text-sm text-gray-400 mb-2"><?php echo __('expense_title'); ?></label>
                <input type="text" id="expense-title" required class="w-full bg-dark/50 border border-white/10 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all" placeholder="<?php echo __('expense_title_placeholder'); ?>">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-2"><?php echo __('amount'); ?> (<?php echo $currency; ?>)</label>
                    <input type="number" id="expense-amount" step="0.01" required class="w-full bg-dark/50 border border-white/10 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all" placeholder="0.00">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-2"><?php echo __('date'); ?></label>
                    <input type="date" id="expense-date" required class="w-full bg-dark/50 border border-white/10 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                </div>
            </div>
            
            <div>
                <label class="block text-sm text-gray-400 mb-2"><?php echo __('category'); ?></label>
                <select id="expense-category" class="w-full bg-dark/50 border border-white/10 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                    <option value="general"><?php echo __('cat_general'); ?></option>
                    <option value="utilities"><?php echo __('cat_utilities'); ?></option>
                    <option value="rent"><?php echo __('cat_rent'); ?></option>
                    <option value="salaries"><?php echo __('cat_salaries'); ?></option>
                    <option value="supplies"><?php echo __('cat_supplies'); ?></option>
                    <option value="maintenance"><?php echo __('cat_maintenance'); ?></option>
                    <option value="marketing"><?php echo __('cat_marketing'); ?></option>
                    <option value="other"><?php echo __('cat_other'); ?></option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm text-gray-400 mb-2"><?php echo __('notes'); ?> (<?php echo __('optional'); ?>)</label>
                <textarea id="expense-notes" rows="3" class="w-full bg-dark/50 border border-white/10 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all" placeholder="<?php echo __('notes_placeholder'); ?>"></textarea>
            </div>

            <div class="flex items-center gap-3 bg-white/5 p-3 rounded-xl border border-white/10">
                <input type="checkbox" id="expense-from-drawer" class="w-5 h-5 rounded border-gray-600 text-primary focus:ring-primary bg-dark/50">
                <label for="expense-from-drawer" class="text-sm text-white cursor-pointer select-none">
                    <?php echo __('deduct_from_drawer'); ?>
                </label>
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeExpenseModal()" class="flex-1 bg-white/5 hover:bg-white/10 text-white font-bold py-3 rounded-xl transition-all"><?php echo __('cancel'); ?></button>
                <button type="submit" class="flex-1 bg-primary hover:bg-primary-hover text-white font-bold py-3 rounded-xl shadow-lg shadow-primary/20 transition-all"><?php echo __('save_expense'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
let currentPage = 1;
const currency = '<?php echo $currency; ?>';

function openAddExpenseModal() {
    document.getElementById('expense-form').reset();
    document.getElementById('expense-date').valueAsDate = new Date();
    document.getElementById('expense-modal').classList.remove('hidden');
}

function closeExpenseModal() {
    document.getElementById('expense-modal').classList.add('hidden');
}

async function loadExpenses(page = 1) {
    try {
        showLoadingOverlay(window.__('loading_expenses'));
        const response = await fetch(`api.php?action=getExpenses&page=${page}`);
        const result = await response.json();
        
        if (result.success) {
            displayExpenses(result.data);
            renderPagination(result.pagination);
            hideLoadingOverlay();
        }
    } catch (error) {
        console.error('Error loading expenses:', error);
        hideLoadingOverlay();
    }
}

function displayExpenses(expenses) {
    const tbody = document.getElementById('expenses-table-body');
    tbody.innerHTML = '';
    
    if (expenses.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-12 text-gray-500 block md:table-cell">${window.__('no_expenses_recorded')}</td></tr>`;
        return;
    }
    
    const categoryNames = {
        'general': window.__('cat_general'),
        'utilities': window.__('cat_utilities'),
        'rent': window.__('cat_rent'),
        'salaries': window.__('cat_salaries'),
        'supplies': window.__('cat_supplies'),
        'maintenance': window.__('cat_maintenance'),
        'marketing': window.__('cat_marketing'),
        'other': window.__('cat_other')
    };

    expenses.forEach(expense => {
        const tr = document.createElement('tr');
        // Mobile: card style. Desktop: table-row style.
        tr.className = 'block md:table-row bg-dark-surface/40 md:bg-transparent mb-4 md:mb-0 rounded-2xl md:rounded-none border border-white/5 md:border-b hover:bg-white/5 transition-colors group';
        
        tr.innerHTML = `
            <td class="px-4 py-3 md:px-6 md:py-4 text-white text-sm block md:table-cell flex justify-between items-center border-b border-white/5 md:border-0 last:border-0">
                <span class="text-gray-400 text-xs font-bold md:hidden uppercase tracking-wider">${window.__('date')}</span>
                <span>${escapeHtml(expense.expense_date)}</span>
            </td>
            <td class="px-4 py-3 md:px-6 md:py-4 text-white font-bold block md:table-cell flex justify-between items-center border-b border-white/5 md:border-0 last:border-0">
                <span class="text-gray-400 text-xs font-bold md:hidden uppercase tracking-wider">${window.__('title')}</span>
                <span>${escapeHtml(expense.title)}</span>
            </td>
            <td class="px-4 py-3 md:px-6 md:py-4 block md:table-cell flex justify-between items-center border-b border-white/5 md:border-0 last:border-0">
                <span class="text-gray-400 text-xs font-bold md:hidden uppercase tracking-wider">${window.__('category')}</span>
                <span class="bg-blue-500/10 text-blue-400 text-xs px-2 py-1 rounded-full border border-blue-500/20">
                    ${categoryNames[expense.category] || escapeHtml(expense.category)}
                </span>
            </td>
            <td class="px-4 py-3 md:px-6 md:py-4 text-red-400 font-bold block md:table-cell flex justify-between items-center border-b border-white/5 md:border-0 last:border-0">
                <span class="text-gray-400 text-xs font-bold md:hidden uppercase tracking-wider">${window.__('amount')}</span>
                <span>${parseFloat(expense.amount).toFixed(2)} ${currency}</span>
            </td>
            <td class="px-4 py-3 md:px-6 md:py-4 text-gray-400 text-xs max-w-full md:max-w-xs block md:table-cell flex justify-between items-center border-b border-white/5 md:border-0 last:border-0">
                <span class="text-gray-400 text-xs font-bold md:hidden uppercase tracking-wider flex-shrink-0 mr-2">${window.__('notes')}</span>
                <span class="truncate block max-w-[200px] md:max-w-xs text-right">${escapeHtml(expense.notes || '-')}</span>
            </td>
            <td class="px-4 py-3 md:px-6 md:py-4 text-left block md:table-cell flex justify-between items-center md:justify-start border-b border-white/5 md:border-0 last:border-0">
                <span class="text-gray-400 text-xs font-bold md:hidden uppercase tracking-wider">${window.__('actions')}</span>
                <button onclick="deleteExpense(${expense.id})" class="text-gray-500 hover:text-red-500 transition-colors p-2 bg-white/5 md:bg-transparent rounded-lg md:rounded-none">
                    <span class="material-icons-round text-sm">delete</span>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function escapeHtml(text) {
  if (text == null) return '';
  return String(text)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function renderPagination(pagination) {
    const container = document.getElementById('pagination');
    container.innerHTML = '';
    
    if (pagination.total_pages <= 1) {
        container.closest('div').classList.add('hidden'); // Hide container if no pagination needed
        return; 
    } else {
        container.closest('div').classList.remove('hidden');
    }
    
    for (let i = 1; i <= pagination.total_pages; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = `w-10 h-10 rounded-xl font-bold transition-all ${pagination.current_page === i ? 'bg-primary text-white' : 'bg-white/5 text-gray-400 hover:bg-white/10'}`;
        btn.onclick = () => loadExpenses(i);
        container.appendChild(btn);
    }
}

async function deleteExpense(id) {
    const confirmed = await Swal.fire({
        title: window.__('are_you_sure'),
        text: window.__('delete_expense_warning'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: window.__('yes_delete'),
        cancelButtonText: window.__('cancel')
    });

    if (confirmed.isConfirmed) {
        try {
            showLoadingOverlay(window.__('deleting_expense'));
            const response = await fetch('api.php?action=deleteExpense', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const result = await response.json();
            if (result.success) {
                hideLoadingOverlay();
                Swal.fire(window.__('deleted_successfully'), result.message, 'success');
                loadExpenses(currentPage);
                updateSummaries();
            } else {
                hideLoadingOverlay();
                Swal.fire(window.__('error'), result.message, 'error');
            }
        } catch (error) {
            hideLoadingOverlay();
            Swal.fire(window.__('error'), window.__('unexpected_error'), 'error');
        }
    }
}

document.getElementById('expense-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const data = {
        title: document.getElementById('expense-title').value,
        amount: document.getElementById('expense-amount').value,
        expense_date: document.getElementById('expense-date').value,
        category: document.getElementById('expense-category').value,
        notes: document.getElementById('expense-notes').value,
        paid_from_drawer: document.getElementById('expense-from-drawer').checked
    };
    
    try {
        showLoadingOverlay(window.__('adding_expense'));
        const response = await fetch('api.php?action=addExpense', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        if (result.success) {
            hideLoadingOverlay();
            closeExpenseModal();
            Swal.fire(window.__('done'), window.__('expense_added_successfully'), 'success');
            loadExpenses(1);
            updateSummaries();
        } else {
            hideLoadingOverlay();
            Swal.fire(window.__('error'), result.message, 'error');
        }
    } catch (error) {
        hideLoadingOverlay();
        Swal.fire(window.__('error'), window.__('unexpected_error'), 'error');
    }
});

// Load summaries properly (Total, Month, Today, and Cycle)
async function updateSummaries() {
    try {
        showLoadingOverlay(window.__('updating_data'));
        // Fetch cycle configuration from dashboard stats (efficient reuse)
        const statsRes = await fetch('api.php?action=getDashboardStats');
        const statsData = await statsRes.json();
        
        const response = await fetch('api.php?action=getExpenses&limit=1000');
        const result = await response.json();
        
        if (result.success) {
            const today = new Date().toISOString().split('T')[0];
            const thisMonth = today.substring(0, 7);
            
            let todayTotal = 0;
            let monthTotal = 0;
            let grandTotal = 0;
            let cycleTotal = 0;

            // Get Cycle Info from Dashboard Stats if available
            const cycleType = statsData.success ? statsData.data.expenseCycleType : 'monthly';
            
            // Calculate cycle dates manually for accuracy here
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = now.getDate();
            
            let cycleStart, cycleEnd;
            if (cycleType === 'bi-monthly') {
                if (day <= 15) {
                    cycleStart = `${year}-${month}-01`;
                    cycleEnd = `${year}-${month}-15`;
                    document.getElementById('cycle-label').textContent = window.__('cycle_first_half');
                } else {
                    cycleStart = `${year}-${month}-16`;
                    const lastDay = new Date(year, now.getMonth() + 1, 0).getDate();
                    cycleEnd = `${year}-${month}-${lastDay}`;
                    document.getElementById('cycle-label').textContent = window.__('cycle_second_half');
                }
            } else {
                cycleStart = `${year}-${month}-01`;
                const lastDay = new Date(year, now.getMonth() + 1, 0).getDate();
                cycleEnd = `${year}-${month}-${lastDay}`;
                document.getElementById('cycle-label').textContent = window.__('monthly_cycle_expenses');
            }
            
            document.getElementById('cycle-dates').textContent = `${cycleStart} ${window.__('to')} ${cycleEnd}`;
            
            result.data.forEach(exp => {
                const amount = parseFloat(exp.amount);
                grandTotal += amount;
                if (exp.expense_date === today) todayTotal += amount;
                if (exp.expense_date.startsWith(thisMonth)) monthTotal += amount;
                if (exp.expense_date >= cycleStart && exp.expense_date <= cycleEnd) cycleTotal += amount;
            });
            
            document.getElementById('today-expenses').innerHTML = `${todayTotal.toFixed(2)} <span class="text-sm text-gray-500 font-normal">${currency}</span>`;
            document.getElementById('cycle-expenses').innerHTML = `${cycleTotal.toFixed(2)} <span class="text-sm text-gray-500 font-normal">${currency}</span>`;
            document.getElementById('month-expenses').innerHTML = `${monthTotal.toFixed(2)} <span class="text-sm text-gray-500 font-normal">${currency}</span>`;
            document.getElementById('total-expenses').innerHTML = `${grandTotal.toFixed(2)} <span class="text-sm text-gray-500 font-normal">${currency}</span>`;
            hideLoadingOverlay();
        }
    } catch (e) {
        console.error('Error updating summaries:', e);
        hideLoadingOverlay();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadExpenses();
    updateSummaries();
});
</script>

<!-- Loading Overlay -->
<div id="loading-overlay" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[9999] hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl p-12 border border-white/10 flex flex-col items-center gap-6">
        <div class="relative w-20 h-20">
            <div class="absolute inset-0 border-4 border-transparent border-t-primary border-r-primary rounded-full animate-spin"></div>
            <div class="absolute inset-2 border-4 border-transparent border-b-primary/50 rounded-full animate-spin" style="animation-direction: reverse;"></div>
        </div>
        <div class="text-center">
            <h3 class="text-lg font-bold text-white mb-2"><?php echo __('loading'); ?></h3>
            <p id="loading-message" class="text-sm text-gray-400"><?php echo __('please_wait'); ?></p>
        </div>
    </div>
</div>

<script>
    // دوال إدارة شاشة التحميل
    function showLoadingOverlay(message = '<?php echo __('processing'); ?>') {
        const loadingOverlay = document.getElementById('loading-overlay');
        const loadingMessage = document.getElementById('loading-message');
        loadingMessage.textContent = message;
        loadingOverlay.classList.remove('hidden');
    }

    function hideLoadingOverlay() {
        const loadingOverlay = document.getElementById('loading-overlay');
        loadingOverlay.classList.add('hidden');
    }
</script>

<style>
@keyframes scaleIn {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.animate-scaleIn { animation: scaleIn 0.2s ease-out forwards; }
</style>

<?php require_once 'src/footer.php'; ?>
