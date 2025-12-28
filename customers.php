<?php
$page_title = 'العملاء';
$current_page = 'customers.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';
?>

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden">
    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-accent/5 rounded-full blur-[120px] pointer-events-none"></div>

    <!-- Header -->
    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <h2 class="text-xl font-bold text-white">إدارة العملاء</h2>
        <div class="flex items-center gap-4">
            <button id="add-customer-btn" class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">person_add</span>
                <span>عميل جديد</span>
            </button>
        </div>
    </header>

    <!-- Search & Filter -->
    <div class="p-6 pb-0 flex gap-4 items-center relative z-10 shrink-0">
        <div class="relative flex-1 max-w-xl">
            <span class="material-icons-round absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">search</span>
            <input type="text" id="search-input" placeholder="بحث باسم العميل أو رقم الهاتف..."
                class="w-full bg-dark/50 border border-white/10 text-white text-right pr-10 pl-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
        </div>
    </div>

    <!-- Customers Grid -->
    <div class="flex-1 overflow-y-auto p-6 z-10" style="max-height: calc(100vh - 13rem);">
        <div id="customers-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div class="text-center py-4 text-gray-500 col-span-full">
                لا توجد أي بيانات لعرضها الآن.
            </div>
        </div>
    </div>
</main>

<!-- Add Customer Modal -->
<div id="add-customer-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-lg border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">إضافة عميل جديد</h3>
            <button id="close-add-customer-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form id="add-customer-form">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="add-customer-name" class="block text-sm font-medium text-gray-300 mb-2">الاسم</label>
                        <input type="text" id="add-customer-name" name="name" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" required>
                    </div>
                    <div class="mb-4">
                        <label for="add-customer-phone" class="block text-sm font-medium text-gray-300 mb-2">الهاتف</label>
                        <input type="text" id="add-customer-phone" name="phone" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                    </div>
                    <div class="mb-4 col-span-2">
                        <label for="add-customer-email" class="block text-sm font-medium text-gray-300 mb-2">البريد الإلكتروني</label>
                        <input type="email" id="add-customer-email" name="email" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                    </div>
                    <div class="mb-4">
                        <label for="add-customer-address" class="block text-sm font-medium text-gray-300 mb-2">العنوان</label>
                        <input type="text" id="add-customer-address" name="address" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                    </div>
                    <div class="mb-4">
                        <label for="add-customer-city" class="block text-sm font-medium text-gray-300 mb-2">المدينة</label>
                        <select id="add-customer-city" name="city" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                            <option value="">اختر المدينة</option>
                            <option value="طنجة">طنجة</option>
                            <option value="الدار البيضاء">الدار البيضاء</option>
                            <option value="الرباط">الرباط</option>
                            <option value="فاس">فاس</option>
                            <option value="مراكش">مراكش</option>
                            <option value="أغادير">أغادير</option>
                            <option value="مكناس">مكناس</option>
                            <option value="وجدة">وجدة</option>
                            <option value="طنجة أصيلة">طنجة أصيلة</option>
                            <option value="برشيد">برشيد</option>
                            <option value="إنزكان آيت ملول">إنزكان آيت ملول</option>
                            <option value="الهراويين">الهراويين</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-white/5 flex justify-end gap-4">
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all">حفظ العميل</button>
            </div>
        </form>
    </div>
</div>

<!-- Customer Details Modal -->
<div id="customer-details-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-lg border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">تفاصيل العميل</h3>
            <button id="close-details-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form id="edit-customer-form">
            <div class="p-6">
                <input type="hidden" id="edit-customer-id" name="id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="edit-customer-name" class="block text-sm font-medium text-gray-300 mb-2">الاسم</label>
                        <div class="relative">
                            <input type="text" id="edit-customer-name" name="name" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" required>
                            <button type="button" class="edit-field-btn absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors" data-field="name">
                                <span class="material-icons-round text-sm">edit</span>
                            </button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="edit-customer-phone" class="block text-sm font-medium text-gray-300 mb-2">الهاتف</label>
                        <div class="relative">
                            <input type="text" id="edit-customer-phone" name="phone" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                            <button type="button" class="edit-field-btn absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors" data-field="phone">
                                <span class="material-icons-round text-sm">edit</span>
                            </button>
                        </div>
                    </div>
                    <div class="mb-4 col-span-2">
                        <label for="edit-customer-email" class="block text-sm font-medium text-gray-300 mb-2">البريد الإلكتروني</label>
                        <div class="relative">
                            <input type="email" id="edit-customer-email" name="email" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                            <button type="button" class="edit-field-btn absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors" data-field="email">
                                <span class="material-icons-round text-sm">edit</span>
                            </button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="edit-customer-address" class="block text-sm font-medium text-gray-300 mb-2">العنوان</label>
                        <div class="relative">
                            <input type="text" id="edit-customer-address" name="address" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                            <button type="button" class="edit-field-btn absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors" data-field="address">
                                <span class="material-icons-round text-sm">edit</span>
                            </button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="edit-customer-city" class="block text-sm font-medium text-gray-300 mb-2">المدينة</label>
                        <div class="relative">
                            <select id="edit-customer-city" name="city" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                                <option value="">اختر المدينة</option>
                                <option value="طنجة">طنجة</option>
                                <option value="الدار البيضاء">الدار البيضاء</option>
                                <option value="الرباط">الرباط</option>
                                <option value="فاس">فاس</option>
                                <option value="مراكش">مراكش</option>
                                <option value="أغادير">أغادير</option>
                                <option value="مكناس">مكناس</option>
                                <option value="وجدة">وجدة</option>
                                <option value="طنجة أصيلة">طنجة أصيلة</option>
                                <option value="برشيد">برشيد</option>
                                <option value="إنزكان آيت ملول">إنزكان آيت ملول</option>
                                <option value="الهراويين">الهراويين</option>
                            </select>
                            <button type="button" class="edit-field-btn absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors" data-field="city">
                                <span class="material-icons-round text-sm">edit</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-white/5 flex justify-end gap-4">
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const addCustomerModal = document.getElementById('add-customer-modal');
    const customerDetailsModal = document.getElementById('customer-details-modal');
    const addCustomerBtn = document.getElementById('add-customer-btn');
    const closeAddCustomerModalBtn = document.getElementById('close-add-customer-modal');
    const closeDetailsModalBtn = document.getElementById('close-details-modal');
    const addCustomerForm = document.getElementById('add-customer-form');
    const editCustomerForm = document.getElementById('edit-customer-form');
    const customersGrid = document.getElementById('customers-grid');
    const searchInput = document.getElementById('search-input');

    // Make all edit fields read-only initially
    const editInputs = editCustomerForm.querySelectorAll('input[type="text"], input[type="email"], select');
    editInputs.forEach(input => {
        if (input.id !== 'edit-customer-id') {
            input.readOnly = true;
            if (input.tagName === 'SELECT') {
                input.disabled = true;
            }
        }
    });

    // Handle edit field buttons
    document.querySelectorAll('.edit-field-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const fieldName = this.dataset.field;
            const input = document.getElementById(`edit-customer-${fieldName}`);
            if (input.tagName === 'SELECT') {
                input.disabled = false;
            } else {
                input.readOnly = false;
            }
            input.focus();
        });
    });

    async function loadCustomers(search = '') {
        try {
            const response = await fetch(`api.php?action=getCustomers&search=${encodeURIComponent(search)}`);
            const result = await response.json();
            if (result.success) {
                displayCustomers(result.data);
            } else {
                showToast(result.message || 'فشل في تحميل العملاء', false);
            }
        } catch (error) {
            console.error('خطأ في تحميل العملاء:', error);
            showToast('حدث خطأ في تحميل العملاء', false);
        }
    }

    function displayCustomers(customers) {
        customersGrid.innerHTML = '';
        if (customers.length === 0) {
            customersGrid.innerHTML = '<div class="text-center py-4 text-gray-500 col-span-full">لا توجد أي بيانات لعرضها الآن.</div>';
            return;
        }
        customers.forEach(customer => {
            const customerCard = document.createElement('div');
            customerCard.className = 'bg-dark-surface/50 border border-white/5 rounded-2xl p-4 hover:border-primary/30 transition-all';
            customerCard.innerHTML = `
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-xl font-bold text-white">${customer.name.charAt(0)}</div>
                        <div>
                            <h3 class="font-bold text-white">${customer.name}</h3>
                            <p class="text-sm text-gray-400">${customer.phone || 'لا يوجد هاتف'}</p>
                            ${customer.city ? `<p class="text-xs text-gray-500">${customer.city}</p>` : ''}
                        </div>
                    </div>
                    <button class="view-details-btn text-primary hover:text-primary-hover text-sm font-bold transition-colors" data-id="${customer.id}">
                        تفاصيل
                    </button>
                </div>
            `;
            customersGrid.appendChild(customerCard);
        });

        // Add event listeners to details buttons
        document.querySelectorAll('.view-details-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const customerId = this.dataset.id;
                viewCustomerDetails(customerId);
            });
        });
    }

    async function viewCustomerDetails(customerId) {
        try {
            const response = await fetch(`api.php?action=getCustomerDetails&id=${customerId}`);
            const result = await response.json();
            if (result.success) {
                const customer = result.data;
                document.getElementById('edit-customer-id').value = customer.id;
                document.getElementById('edit-customer-name').value = customer.name || '';
                document.getElementById('edit-customer-phone').value = customer.phone || '';
                document.getElementById('edit-customer-email').value = customer.email || '';
                document.getElementById('edit-customer-address').value = customer.address || '';
                document.getElementById('edit-customer-city').value = customer.city || '';
                
                // Make all fields read-only/disabled again
                const editInputs = editCustomerForm.querySelectorAll('input[type="text"], input[type="email"], select');
                editInputs.forEach(input => {
                    if (input.id !== 'edit-customer-id') {
                        if (input.tagName === 'SELECT') {
                            input.disabled = true;
                        } else {
                            input.readOnly = true;
                        }
                    }
                });
                
                customerDetailsModal.classList.remove('hidden');
            } else {
                showToast(result.message || 'فشل في تحميل تفاصيل العميل', false);
            }
        } catch (error) {
            console.error('خطأ في تحميل تفاصيل العميل:', error);
            showToast('حدث خطأ في تحميل تفاصيل العميل', false);
        }
    }

    searchInput.addEventListener('input', function() {
        loadCustomers(this.value);
    });

    addCustomerBtn.addEventListener('click', () => {
        addCustomerModal.classList.remove('hidden');
    });

    closeAddCustomerModalBtn.addEventListener('click', () => {
        addCustomerModal.classList.add('hidden');
        addCustomerForm.reset();
    });

    closeDetailsModalBtn.addEventListener('click', () => {
        customerDetailsModal.classList.add('hidden');
        editCustomerForm.reset();
    });

    addCustomerForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(addCustomerForm);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('api.php?action=addCustomer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });
            const result = await response.json();
            if (result.success) {
                addCustomerModal.classList.add('hidden');
                addCustomerForm.reset();
                loadCustomers();
                showToast(result.message || 'تم إضافة العميل بنجاح', true);
            } else {
                showToast(result.message || 'فشل في إضافة العميل', false);
            }
        } catch (error) {
            console.error('خطأ في إضافة العميل:', error);
            showToast('حدث خطأ في إضافة العميل', false);
        }
    });

    editCustomerForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(editCustomerForm);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('api.php?action=updateCustomer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });
            const result = await response.json();
            if (result.success) {
                customerDetailsModal.classList.add('hidden');
                editCustomerForm.reset();
                loadCustomers();
                showToast(result.message || 'تم تحديث العميل بنجاح', true);
            } else {
                showToast(result.message || 'فشل في تحديث العميل', false);
            }
        } catch (error) {
            console.error('خطأ في تحديث العميل:', error);
            showToast('حدث خطأ في تحديث العميل', false);
        }
    });

    loadCustomers();
});
</script>
<?php require_once 'src/footer.php'; ?>