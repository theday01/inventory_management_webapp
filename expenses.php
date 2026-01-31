<?php
require_once 'session.php';
require_once 'db.php';

// Only admin can access expenses
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$page_title = 'إدارة المصاريف';
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
                    إدارة المصاريف
                </h2>
                <p class="text-sm text-gray-400 mt-1">تتبع وتسجيل جميع مصاريف المتجر</p>
            </div>

            <button onclick="openAddExpenseModal()" class="bg-primary hover:bg-primary-hover text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all flex items-center gap-2">
                <span class="material-icons-round">add</span>
                إضافة مصروف جديد
            </button>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto p-6">
        <!-- Expenses Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-dark-surface/60 border border-white/5 p-6 rounded-2xl">
                <p class="text-sm text-gray-400 mb-1">مصاريف اليوم</p>
                <h3 id="today-expenses" class="text-2xl font-bold text-white">0.00 <span class="text-sm text-gray-500 font-normal"><?php echo $currency; ?></span></h3>
            </div>
            <div class="bg-dark-surface/60 border border-white/5 p-6 rounded-2xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-1 h-full bg-primary/40 group-hover:bg-primary transition-all"></div>
                <p id="cycle-label" class="text-sm text-primary font-bold mb-1">مصاريف الدورة الحالية</p>
                <h3 id="cycle-expenses" class="text-2xl font-bold text-white">0.00 <span class="text-sm text-gray-500 font-normal"><?php echo $currency; ?></span></h3>
                <p id="cycle-dates" class="text-[10px] text-gray-500 mt-1">-</p>
            </div>
            <div class="bg-dark-surface/60 border border-white/5 p-6 rounded-2xl">
                <p class="text-sm text-gray-400 mb-1">مصاريف هذا الشهر</p>
                <h3 id="month-expenses" class="text-2xl font-bold text-white">0.00 <span class="text-sm text-gray-500 font-normal"><?php echo $currency; ?></span></h3>
            </div>
            <div class="bg-dark-surface/60 border border-white/5 p-6 rounded-2xl">
                <p class="text-sm text-gray-400 mb-1">إجمالي المصاريف</p>
                <h3 id="total-expenses" class="text-2xl font-bold text-white">0.00 <span class="text-sm text-gray-500 font-normal"><?php echo $currency; ?></span></h3>
            </div>
        </div>

        <!-- Expenses Table -->
        <div class="bg-dark-surface/40 border border-white/5 rounded-2xl overflow-hidden backdrop-blur-md">
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="bg-white/5 text-gray-400 text-xs uppercase tracking-wider">
                            <th class="px-6 py-4 font-bold">التاريخ</th>
                            <th class="px-6 py-4 font-bold">العنوان</th>
                            <th class="px-6 py-4 font-bold">الفئة</th>
                            <th class="px-6 py-4 font-bold">المبلغ</th>
                            <th class="px-6 py-4 font-bold">ملاحظات</th>
                            <th class="px-6 py-4 font-bold text-left">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="expenses-table-body" class="divide-y divide-white/5">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div id="pagination" class="p-6 border-t border-white/5 flex justify-center items-center gap-2"></div>
        </div>
    </div>
</main>

<!-- Add Expense Modal -->
<div id="expense-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-dark-surface border border-white/10 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden animate-scaleIn">
        <div class="p-6 border-b border-white/5 flex items-center justify-between">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="material-icons-round text-primary">add_circle</span>
                إضافة مصروف جديد
            </h3>
            <button onclick="closeExpenseModal()" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        
        <form id="expense-form" class="p-6 space-y-4">
            <div>
                <label class="block text-sm text-gray-400 mb-2">عنوان المصروف</label>
                <input type="text" id="expense-title" required class="w-full bg-dark/50 border border-white/10 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all" placeholder="مثال: فاتورة الكهرباء">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-2">المبلغ (<?php echo $currency; ?>)</label>
                    <input type="number" id="expense-amount" step="0.01" required class="w-full bg-dark/50 border border-white/10 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all" placeholder="0.00">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-2">التاريخ</label>
                    <input type="date" id="expense-date" required class="w-full bg-dark/50 border border-white/10 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                </div>
            </div>
            
            <div>
                <label class="block text-sm text-gray-400 mb-2">الفئة</label>
                <select id="expense-category" class="w-full bg-dark/50 border border-white/10 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                    <option value="general">عام</option>
                    <option value="utilities">فواتير (ماء، كهرباء، إنترنت)</option>
                    <option value="rent">إيجار</option>
                    <option value="salaries">رواتب</option>
                    <option value="supplies">مستلزمات محددة</option>
                    <option value="maintenance">صيانة</option>
                    <option value="marketing">تسويق</option>
                    <option value="other">أخرى</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm text-gray-400 mb-2">ملاحظات (اختياري)</label>
                <textarea id="expense-notes" rows="3" class="w-full bg-dark/50 border border-white/10 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all" placeholder="أي تفاصيل إضافية..."></textarea>
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeExpenseModal()" class="flex-1 bg-white/5 hover:bg-white/10 text-white font-bold py-3 rounded-xl transition-all">إلغاء</button>
                <button type="submit" class="flex-1 bg-primary hover:bg-primary-hover text-white font-bold py-3 rounded-xl shadow-lg shadow-primary/20 transition-all">حفظ المصروف</button>
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
        const response = await fetch(`api.php?action=getExpenses&page=${page}`);
        const result = await response.json();
        
        if (result.success) {
            displayExpenses(result.data);
            renderPagination(result.pagination);
            calculateSummaries(result.data); // This is just for the current page, better fetch actual summaries
            fetchSummaries();
        }
    } catch (error) {
        console.error('Error loading expenses:', error);
    }
}

async function fetchSummaries() {
    try {
        // We reuse get_period_summary for today and month if we want, 
        // but for now let's just use what we have or add a new API action.
        // I'll calculate it from all expenses for now or just add it to api.php later.
        // For simplicity, let's just fetch all for now and calculate.
    } catch (e) {}
}

function displayExpenses(expenses) {
    const tbody = document.getElementById('expenses-table-body');
    tbody.innerHTML = '';
    
    if (expenses.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-12 text-gray-500">لا توجد مصاريف مسجلة حالياً.</td></tr>';
        return;
    }
    
    const categoryNames = {
        'general': 'عام',
        'utilities': 'فواتير',
        'rent': 'إيجار',
        'salaries': 'رواتب',
        'supplies': 'مستلزمات',
        'maintenance': 'صيانة',
        'marketing': 'تسويق',
        'other': 'أخرى'
    };

    expenses.forEach(expense => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-white/5 transition-colors group';
        tr.innerHTML = `
            <td class="px-6 py-4 text-white text-sm">${expense.expense_date}</td>
            <td class="px-6 py-4 text-white font-bold">${expense.title}</td>
            <td class="px-6 py-4">
                <span class="bg-blue-500/10 text-blue-400 text-xs px-2 py-1 rounded-full border border-blue-500/20">
                    ${categoryNames[expense.category] || expense.category}
                </span>
            </td>
            <td class="px-6 py-4 text-red-400 font-bold">${parseFloat(expense.amount).toFixed(2)} ${currency}</td>
            <td class="px-6 py-4 text-gray-400 text-xs max-w-xs truncate">${expense.notes || '-'}</td>
            <td class="px-6 py-4 text-left">
                <button onclick="deleteExpense(${expense.id})" class="text-gray-500 hover:text-red-500 transition-colors p-2">
                    <span class="material-icons-round text-sm">delete</span>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function renderPagination(pagination) {
    const container = document.getElementById('pagination');
    container.innerHTML = '';
    
    if (pagination.total_pages <= 1) return;
    
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
        title: 'هل أنت متأكد؟',
        text: "لا يمكنك التراجع عن حذف هذا المصروف!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'نعم، احذفه!',
        cancelButtonText: 'إلغاء'
    });

    if (confirmed.isConfirmed) {
        try {
            const response = await fetch('api.php?action=deleteExpense', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const result = await response.json();
            if (result.success) {
                Swal.fire('تم الحذف!', result.message, 'success');
                loadExpenses(currentPage);
            } else {
                Swal.fire('خطأ!', result.message, 'error');
            }
        } catch (error) {
            Swal.fire('خطأ!', 'حدث خطأ غير متوقع', 'error');
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
        notes: document.getElementById('expense-notes').value
    };
    
    try {
        const response = await fetch('api.php?action=addExpense', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        if (result.success) {
            closeExpenseModal();
            Swal.fire('تم!', 'تم تسجيل المصروف بنجاح', 'success');
            loadExpenses(1);
        } else {
            Swal.fire('خطأ!', result.message, 'error');
        }
    } catch (error) {
        Swal.fire('خطأ!', 'حدث خطأ غير متوقع', 'error');
    }
});

// Load summaries properly (Total, Month, Today, and Cycle)
async function updateSummaries() {
    try {
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
                    document.getElementById('cycle-label').textContent = 'دورة النصف الأول (1-15)';
                } else {
                    cycleStart = `${year}-${month}-16`;
                    const lastDay = new Date(year, now.getMonth() + 1, 0).getDate();
                    cycleEnd = `${year}-${month}-${lastDay}`;
                    document.getElementById('cycle-label').textContent = 'دورة النصف الثاني (16-..)';
                }
            } else {
                cycleStart = `${year}-${month}-01`;
                const lastDay = new Date(year, now.getMonth() + 1, 0).getDate();
                cycleEnd = `${year}-${month}-${lastDay}`;
                document.getElementById('cycle-label').textContent = 'مصاريف الدورة الشهرية';
            }
            
            document.getElementById('cycle-dates').textContent = `${cycleStart} إلى ${cycleEnd}`;
            
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
        }
    } catch (e) {
        console.error('Error updating summaries:', e);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadExpenses();
    updateSummaries();
});
</script>

<style>
@keyframes scaleIn {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.animate-scaleIn { animation: scaleIn 0.2s ease-out forwards; }
</style>

<?php require_once 'src/footer.php'; ?>
