<?php
$page_title = 'إدارة العملاء';
$current_page = 'customers.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';
require_once 'db.php';

?>

<main class="flex-1 flex flex-col relative bg-dark">
    <div
        class="absolute top-0 right-[-10%] w-[500px] h-[500px] bg-primary/5 rounded-full blur-[120px] pointer-events-none">
    </div>
    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 sticky top-0 z-30 shrink-0">
        <h2 class="text-xl font-bold text-white">إدارة العملاء</h2>
        <div class="flex items-center gap-4">
            <button id="add-customer-btn"
                class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">add</span>
                <span>إضافة عميل</span>
            </button>
        </div>
    </header>
    <div class="p-6 flex items-center justify-between">
        <div class="relative w-full max-w-md">
            <span class="material-icons-round absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">search</span>
            <input type="text" id="customer-search-input" placeholder="بحث عن اسم العميل، رقم الهاتف..."
                class="w-full bg-dark/50 border border-white/10 text-white text-right pr-10 pl-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
        </div>
    </div>
    <div class="flex-1 flex flex-col p-6 pt-0">
        <div class="flex-1 flex flex-col bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl glass-panel overflow-hidden">
            <div class="flex-1 overflow-y-auto">
                <table class="w-full min-w-full table-auto">
                    <thead>
                        <tr class="text-right">
                            <th class="sticky top-0 bg-dark-surface/80 backdrop-blur-sm p-4 text-sm font-medium text-gray-300">الاسم</th>
                            <th class="sticky top-0 bg-dark-surface/80 backdrop-blur-sm p-4 text-sm font-medium text-gray-300">الهاتف</th>
                            <th class="sticky top-0 bg-dark-surface/80 backdrop-blur-sm p-4 text-sm font-medium text-gray-300">البريد الإلكتروني</th>
                            <th class="sticky top-0 bg-dark-surface/80 backdrop-blur-sm p-4 text-sm font-medium text-gray-300">العنوان</th>
                            <th class="sticky top-0 bg-dark-surface/80 backdrop-blur-sm p-4 text-sm font-medium text-gray-300">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="customers-table-body" class="divide-y divide-white/5">
                        <!-- TODO: Implement JavaScript logic to fetch and display customers here -->
                        <tr>
                            <td colspan="5" class="text-center py-4 text-gray-500">
                                لم يتم إضافة أي عملاء بعد.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="pagination-container" class="sticky bottom-0 p-4 flex justify-center items-center z-20">
            </div>
        </div>
    </div>
</main>
<div id="customer-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-lg border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 id="customer-modal-title" class="text-lg font-bold text-white">إضافة عميل جديد</h3>
            <button id="close-customer-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form id="customer-form">
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <input type="hidden" id="customer-id" name="id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="customer-name" class="block text-sm font-medium text-gray-300 mb-2">اسم العميل</label>
                        <input type="text" id="customer-name" name="name" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" required>
                    </div>
                    <div class="mb-4">
                        <label for="customer-phone" class="block text-sm font-medium text-gray-300 mb-2">الهاتف</label>
                        <input type="text" id="customer-phone" name="phone" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                    </div>
                    <div class="mb-4 col-span-2">
                        <label for="customer-email" class="block text-sm font-medium text-gray-300 mb-2">البريد الإلكتروني</label>
                        <input type="email" id="customer-email" name="email" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                    </div>
                    <div class="mb-4 col-span-2">
                        <label for="customer-address" class="block text-sm font-medium text-gray-300 mb-2">العنوان</label>
                        <textarea id="customer-address" name="address" rows="3" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50"></textarea>
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-white/5 flex justify-end gap-4">
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all">حفظ العميل</button>
            </div>
        </form>
    </div>
</div>
<div id="customer-details-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-md border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">تفاصيل العميل</h3>
            <button id="close-customer-details-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div id="customer-details-content" class="p-6 max-h-[70vh] overflow-y-auto">
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addCustomerBtn = document.getElementById('add-customer-btn');
    const customerModal = document.getElementById('customer-modal');
    const closeCustomerModalBtn = document.getElementById('close-customer-modal');
    const customerForm = document.getElementById('customer-form');
    const customersTableBody = document.getElementById('customers-table-body');
    const searchInput = document.getElementById('customer-search-input');
    const paginationContainer = document.getElementById('pagination-container');
    const customerDetailsModal = document.getElementById('customer-details-modal');
    const closeCustomerDetailsModalBtn = document.getElementById('close-customer-details-modal');

    let currentPage = 1;
    const customersPerPage = 10;

    async function loadCustomers() {
        const searchQuery = searchInput.value;
        try {
            const response = await fetch(`api.php?action=getCustomers&search=${searchQuery}&page=${currentPage}&limit=${customersPerPage}`);
            const result = await response.json();
            if (result.success) {
                displayCustomers(result.data);
                renderPagination(result.total_customers);
            }
        } catch (error) {
            console.error('Error loading customers:', error);
        }
    }

    function displayCustomers(customers) {
        customersTableBody.innerHTML = '';
        if (customers.length === 0) {
            customersTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">لا يوجد عملاء لعرضهم.</td></tr>';
            return;
        }

        customers.forEach(customer => {
            const customerRow = document.createElement('tr');
            customerRow.className = 'hover:bg-white/5 transition-colors';
            customerRow.innerHTML = `
                <td class="p-4 text-sm text-white font-medium">${customer.name}</td>
                <td class="p-4 text-sm text-gray-300">${customer.phone || '-'}</td>
                <td class="p-4 text-sm text-gray-300">${customer.email || '-'}</td>
                <td class="p-4 text-sm text-gray-300">${customer.address || '-'}</td>
                <td class="p-4 text-sm">
                    <div class="flex items-center gap-2">
                        <button class="view-details-btn p-1.5 text-gray-400 hover:text-primary transition-colors" data-id="${customer.id}" title="عرض التفاصيل">
                            <span class="material-icons-round text-lg">visibility</span>
                        </button>
                        <button class="edit-customer-btn p-1.5 text-gray-400 hover:text-yellow-500 transition-colors" data-id="${customer.id}" title="تعديل">
                            <span class="material-icons-round text-lg">edit</span>
                        </button>
                    </div>
                </td>
            `;
            customersTableBody.appendChild(customerRow);
        });
    }

    function renderPagination(totalCustomers) {
        const totalPages = Math.ceil(totalCustomers / customersPerPage);
        paginationContainer.innerHTML = '';

        if (totalPages <= 1) return;

        let paginationHTML = '<div class="flex items-center gap-2">';
        for (let i = 1; i <= totalPages; i++) {
            paginationHTML += `<button class="pagination-btn ${i === currentPage ? 'bg-primary text-white' : ''}" data-page="${i}">${i}</button>`;
        }
        paginationHTML += '</div>';
        paginationContainer.innerHTML = paginationHTML;
    }

    paginationContainer.addEventListener('click', e => {
        if (e.target.classList.contains('pagination-btn')) {
            currentPage = parseInt(e.target.dataset.page);
            loadCustomers();
        }
    });

    addCustomerBtn.addEventListener('click', () => {
        customerForm.reset();
        document.getElementById('customer-id').value = '';
        document.getElementById('customer-modal-title').textContent = 'إضافة عميل جديد';
        customerModal.classList.remove('hidden');
    });

    closeCustomerModalBtn.addEventListener('click', () => {
        customerModal.classList.add('hidden');
    });

    customerForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(customerForm);
        const customerData = Object.fromEntries(formData.entries());
        const customerId = customerData.id;

        const url = customerId ? 'api.php?action=updateCustomer' : 'api.php?action=addCustomer';
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(customerData),
            });
            const result = await response.json();
            if (result.success) {
                customerModal.classList.add('hidden');
                loadCustomers();
            } else {
                alert(result.message);
            }
        } catch (error) {
            console.error('Error saving customer:', error);
        }
    });

    customersTableBody.addEventListener('click', async (e) => {
        if (e.target.closest('.edit-customer-btn')) {
            const btn = e.target.closest('.edit-customer-btn');
            const customerId = btn.dataset.id;
            const customer = await getCustomerDetails(customerId);
            if (customer) {
                document.getElementById('customer-id').value = customer.id;
                document.getElementById('customer-name').value = customer.name;
                document.getElementById('customer-phone').value = customer.phone;
                document.getElementById('customer-email').value = customer.email;
                document.getElementById('customer-address').value = customer.address;
                document.getElementById('customer-modal-title').textContent = 'تعديل بيانات العميل';
                customerModal.classList.remove('hidden');
            }
        }

        if (e.target.closest('.view-details-btn')) {
            const btn = e.target.closest('.view-details-btn');
            const customerId = btn.dataset.id;
            const customer = await getCustomerDetails(customerId);
            if (customer) {
                displayCustomerDetails(customer);
                customerDetailsModal.classList.remove('hidden');
            }
        }
    });

    closeCustomerDetailsModalBtn.addEventListener('click', () => {
        customerDetailsModal.classList.add('hidden');
    });

    async function getCustomerDetails(id) {
        try {
            const response = await fetch(`api.php?action=getCustomerDetails&id=${id}`);
            const result = await response.json();
            return result.success ? result.data : null;
        } catch (error) {
            console.error('Error fetching customer details:', error);
            return null;
        }
    }

    function displayCustomerDetails(customer) {
        const content = document.getElementById('customer-details-content');
        content.innerHTML = `
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="font-medium text-gray-400">الاسم:</span><span class="text-white">${customer.name}</span></div>
                <div class="flex justify-between"><span class="font-medium text-gray-400">الهاتف:</span><span class="text-white">${customer.phone || '-'}</span></div>
                <div class="flex justify-between"><span class="font-medium text-gray-400">البريد الإلكتروني:</span><span class="text-white">${customer.email || '-'}</span></div>
                <div class="flex justify-between"><span class="font-medium text-gray-400">العنوان:</span><span class="text-white">${customer.address || '-'}</span></div>
            </div>
        `;
    }

    searchInput.addEventListener('input', () => {
        currentPage = 1;
        loadCustomers();
    });

    loadCustomers();
});
</script>
<?php require_once 'src/footer.php'; ?>