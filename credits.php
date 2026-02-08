<?php
require_once 'session.php';
require_once 'src/language.php';
$page_title = __('credits_page_title');
$current_page = 'credits.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';
require_once 'db.php';
?>

<style>
    .pagination-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0.5rem 0.75rem;
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: rgb(209, 213, 219);
        border-radius: 0.625rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .pagination-btn:hover:not(:disabled):not(.opacity-50) {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .pagination-btn:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }

    .pagination-btn.bg-primary {
        background-color: var(--color-primary, #059669);
        border-color: var(--color-primary, #059669);
        color: white;
    }

    .pagination-btn.bg-primary:hover {
        background-color: var(--color-primary-hover, #047857);
    }
</style>

<main class="flex-1 flex flex-col relative overflow-hidden bg-dark">
    <div class="absolute top-0 right-[-10%] w-[500px] h-[500px] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>

    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-4 md:px-8 relative z-10 shrink-0">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="material-icons-round text-primary">credit_score</span>
            <span class="hidden sm:inline"><?php echo __('credits_page_title'); ?></span>
        </h2>
        
        <div class="bg-red-500/10 border border-red-500/20 px-4 py-2 rounded-xl flex items-center gap-2">
            <span class="text-xs text-red-400 uppercase font-bold tracking-wider"><?php echo __('total_debt'); ?></span>
            <span id="total-debt-display" class="text-lg font-bold text-red-500">0.00</span>
        </div>
    </header>

    <div class="p-6 flex flex-col md:flex-row gap-4 items-center justify-between relative z-10 shrink-0">
        <div class="relative w-full md:w-96">
            <span class="material-icons-round absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">search</span>
            <input type="text" id="search-input" placeholder="<?php echo __('search_customers_placeholder'); ?>"
                class="w-full bg-dark/50 border border-white/10 text-white text-right pr-10 pl-10 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
        </div>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden p-6 pt-0 relative z-10">
        <div class="flex-1 flex flex-col bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl glass-panel overflow-hidden">
            <div class="flex-1 overflow-y-auto">
                <table class="w-full">
                    <thead class="hidden md:table-header-group sticky top-0 bg-dark-surface/95 backdrop-blur-sm z-10 border-b border-white/5">
                        <tr class="text-right">
                            <th class="p-4 text-sm font-medium text-gray-300"><?php echo __('customer_name'); ?></th>
                            <th class="p-4 text-sm font-medium text-gray-300"><?php echo __('phone_number'); ?></th>
                            <th class="p-4 text-sm font-medium text-gray-300"><?php echo __('debt_amount'); ?></th>
                            <th class="p-4 text-sm font-medium text-gray-300 text-center w-48"><?php echo __('actions'); ?></th> 
                        </tr>
                    </thead>
                    <tbody id="credits-table-body" class="grid grid-cols-1 md:table-row-group gap-4 p-4 md:p-0 md:divide-y md:divide-white/5">
                        <!-- Rows will be injected here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Pay Debt Modal -->
<div id="pay-debt-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl w-full max-w-md border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white"><?php echo __('debt_payment_modal_title'); ?></h3>
            <button id="close-pay-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div class="bg-red-500/10 border border-red-500/20 p-4 rounded-xl text-center">
                <p class="text-gray-400 text-xs mb-1"><?php echo __('current_balance'); ?></p>
                <p id="modal-current-balance" class="text-2xl font-bold text-red-500">0.00</p>
                <p id="modal-customer-name" class="text-white font-medium mt-2"></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2"><?php echo __('payment_amount'); ?></label>
                <input type="text" id="payment-amount" class="w-full bg-dark/50 border border-white/10 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 text-center text-xl font-bold" placeholder="0.00">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2"><?php echo __('notes'); ?></label>
                <textarea id="payment-notes" rows="2" class="w-full bg-dark/50 border border-white/10 text-white px-4 py-2 rounded-xl focus:outline-none focus:border-primary/50"></textarea>
            </div>
            
            <input type="hidden" id="pay-customer-id">
            
            <button id="confirm-pay-btn" class="w-full bg-primary hover:bg-primary-hover text-white py-3 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2">
                <span class="material-icons-round">payments</span>
                <?php echo __('confirm_payment'); ?>
            </button>
        </div>
    </div>
</div>

<!-- History Modal -->
<div id="history-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl w-full max-w-2xl border border-white/10 m-4 h-[80vh] flex flex-col">
        <div class="p-6 border-b border-white/5 flex justify-between items-center shrink-0">
            <h3 class="text-lg font-bold text-white"><?php echo __('debt_history'); ?></h3>
            <button id="close-history-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <table class="w-full">
                <thead>
                    <tr class="text-right border-b border-white/10">
                        <th class="pb-3 text-sm text-gray-400 font-medium"><?php echo __('date'); ?></th>
                        <th class="pb-3 text-sm text-gray-400 font-medium"><?php echo __('amount'); ?></th>
                        <th class="pb-3 text-sm text-gray-400 font-medium"><?php echo __('notes'); ?></th>
                    </tr>
                </thead>
                <tbody id="history-table-body" class="divide-y divide-white/5"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Constants
    const currency = '<?php $res = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'"); echo ($res && $res->num_rows > 0) ? $res->fetch_assoc()['setting_value'] : 'MAD'; ?>';

    // Elements
    const searchInput = document.getElementById('search-input');
    const tableBody = document.getElementById('credits-table-body');
    const totalDebtDisplay = document.getElementById('total-debt-display');
    
    // Modals
    const payModal = document.getElementById('pay-debt-modal');
    const historyModal = document.getElementById('history-modal');
    
    // Pay Modal Elements
    const modalCurrentBalance = document.getElementById('modal-current-balance');
    const modalCustomerName = document.getElementById('modal-customer-name');
    const paymentAmountInput = document.getElementById('payment-amount');
    const paymentNotesInput = document.getElementById('payment-notes');
    const payCustomerIdInput = document.getElementById('pay-customer-id');
    const confirmPayBtn = document.getElementById('confirm-pay-btn');

    // Load Data
    async function loadCredits() {
        const search = searchInput.value;
        try {
            const response = await fetch(`api.php?action=get_debt_customers&search=${search}`);
            const result = await response.json();
            
            if (result.success) {
                displayCredits(result.data);
                totalDebtDisplay.textContent = parseFloat(result.total_debt).toFixed(2) + ' ' + currency;
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function displayCredits(customers) {
        tableBody.innerHTML = '';
        if (customers.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-12 text-gray-500">${window.__('no_debts_found') || 'No debts found'}</td></tr>`;
            return;
        }

        customers.forEach(customer => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-white/5 transition-colors group border border-white/5 rounded-xl p-4 mb-4 bg-white/5 md:bg-transparent md:border-b md:border-white/5 md:rounded-none md:p-0 md:mb-0 flex flex-col md:table-row relative';
            
            row.innerHTML = `
                <td class="p-2 md:p-4 flex justify-between md:table-cell items-center">
                    <span class="md:hidden text-gray-400 font-normal text-xs uppercase">${window.__('customer_name') || 'Name'}</span>
                    <div class="font-bold text-white text-sm">${customer.name}</div>
                </td>
                <td class="p-2 md:p-4 flex justify-between md:table-cell items-center">
                    <span class="md:hidden text-gray-400 font-normal text-xs uppercase">${window.__('phone_number') || 'Phone'}</span>
                    <div class="text-sm text-gray-300 font-mono dir-ltr text-right">${customer.phone || '-'}</div>
                </td>
                <td class="p-2 md:p-4 flex justify-between md:table-cell items-center">
                    <span class="md:hidden text-gray-400 font-normal text-xs uppercase">${window.__('debt_amount') || 'Debt'}</span>
                    <div class="text-sm font-bold text-red-500">${parseFloat(customer.balance).toFixed(2)} ${currency}</div>
                </td>
                <td class="p-2 md:p-4 flex justify-end md:justify-center md:table-cell mt-2 md:mt-0 border-t border-white/5 md:border-0 pt-3 md:pt-4">
                    <div class="flex items-center gap-2">
                        <button class="pay-btn w-8 h-8 flex items-center justify-center rounded-lg text-emerald-400 hover:text-white hover:bg-emerald-500/20 transition-all" 
                            data-id="${customer.id}" data-name="${customer.name}" data-balance="${customer.balance}" title="${window.__('pay_debt') || 'Pay'}">
                            <span class="material-icons-round text-[18px]">payments</span>
                        </button>
                        <button class="history-btn w-8 h-8 flex items-center justify-center rounded-lg text-blue-400 hover:text-white hover:bg-blue-500/20 transition-all" 
                            data-id="${customer.id}" title="${window.__('debt_history') || 'History'}">
                            <span class="material-icons-round text-[18px]">history</span>
                        </button>
                    </div>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    // Search
    searchInput.addEventListener('input', loadCredits);

    // Click Handlers
    tableBody.addEventListener('click', (e) => {
        const payBtn = e.target.closest('.pay-btn');
        const historyBtn = e.target.closest('.history-btn');

        if (payBtn) {
            const id = payBtn.dataset.id;
            const name = payBtn.dataset.name;
            const balance = payBtn.dataset.balance;
            openPayModal(id, name, balance);
        } else if (historyBtn) {
            const id = historyBtn.dataset.id;
            openHistoryModal(id);
        }
    });

    // Pay Modal Functions
    function openPayModal(id, name, balance) {
        payCustomerIdInput.value = id;
        modalCustomerName.textContent = name;
        modalCurrentBalance.textContent = parseFloat(balance).toFixed(2) + ' ' + currency;
        paymentAmountInput.value = '';
        paymentNotesInput.value = '';
        payModal.classList.remove('hidden');
        paymentAmountInput.focus();
    }

    document.getElementById('close-pay-modal').addEventListener('click', () => {
        payModal.classList.add('hidden');
    });

    confirmPayBtn.addEventListener('click', async () => {
        const id = payCustomerIdInput.value;
        const amount = paymentAmountInput.value;
        const notes = paymentNotesInput.value;

        if (!amount || isNaN(amount) || parseFloat(amount) <= 0) {
            alert(window.__('enter_valid_number') || 'Invalid amount');
            return;
        }

        try {
            const response = await fetch('api.php?action=pay_debt', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ customer_id: id, amount: amount, notes: notes })
            });
            const result = await response.json();
            
            if (result.success) {
                payModal.classList.add('hidden');
                loadCredits(); // Refresh list
                // Show success toast (if implemented globally) or alert
                // alert(result.message); 
            } else {
                alert(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });

    // History Modal Functions
    async function openHistoryModal(id) {
        const tbody = document.getElementById('history-table-body');
        tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4">Loading...</td></tr>';
        historyModal.classList.remove('hidden');

        try {
            const response = await fetch(`api.php?action=get_debt_history&customer_id=${id}`);
            const result = await response.json();
            
            tbody.innerHTML = '';
            if (result.success && result.data.length > 0) {
                result.data.forEach(item => {
                    const row = document.createElement('tr');
                    const date = new Date(item.payment_date).toLocaleDateString('en-GB');
                    row.innerHTML = `
                        <td class="py-3 text-sm text-gray-300">${date}</td>
                        <td class="py-3 text-sm font-bold text-emerald-400">${parseFloat(item.amount).toFixed(2)} ${currency}</td>
                        <td class="py-3 text-sm text-gray-400">${item.notes || '-'}</td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="3" class="text-center py-4 text-gray-500">${window.__('no_data') || 'No data'}</td></tr>`;
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    document.getElementById('close-history-modal').addEventListener('click', () => {
        historyModal.classList.add('hidden');
    });

    // Initial Load
    loadCredits();
</script>

<?php require_once 'src/footer.php'; ?>