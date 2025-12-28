<?php
$page_title = 'العملاء';
$current_page = 'customers.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';
?>

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden">
    <div
        class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-accent/5 rounded-full blur-[120px] pointer-events-none">
    </div>

    <!-- Header -->
    <header
        class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <h2 class="text-xl font-bold text-white">إدارة العملاء</h2>
        <div class="flex items-center gap-4">
            <button
                class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">person_add</span>
                <span>عميل جديد</span>
            </button>
        </div>
    </header>

    <!-- Search & Filter -->
    <div class="p-6 pb-0 flex gap-4 items-center relative z-10 shrink-0">
        <div class="relative flex-1 max-w-xl">
            <span class="material-icons-round absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">search</span>
            <input type="text" placeholder="بحث باسم العميل أو رقم الهاتف..."
                class="w-full bg-dark/50 border border-white/10 text-white text-right pr-10 pl-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
        </div>
    </div>

    <!-- Customers Grid -->
    <div class="flex-1 overflow-y-auto p-6 z-10" style="max-height: calc(100vh - 13rem);">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div class="text-center py-4 text-gray-500 col-span-full">
                لا توجد أي بيانات لعرضها الآن.
            </div>
        </div>
    </div>

</main>

<!-- Add/Edit Customer Modal -->
<div id="customer-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-lg border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 id="customer-modal-title" class="text-lg font-bold text-white">إضافة عميل جديد</h3>
            <button id="close-customer-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form id="customer-form">
            <div class="p-6">
                <input type="hidden" id="customer-id" name="id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="customer-name" class="block text-sm font-medium text-gray-300 mb-2">الاسم</label>
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
                        <input type="text" id="customer-address" name="address" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-white/5 flex justify-end gap-4">
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all">حفظ العميل</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const customerModal = document.getElementById('customer-modal');
    const addCustomerBtn = document.querySelector('button.bg-primary');
    const closeCustomerModalBtn = document.getElementById('close-customer-modal');
    const customerForm = document.getElementById('customer-form');
    const customersGrid = document.querySelector('.grid');

    async function loadCustomers() {
        try {
            const response = await fetch('api.php?action=getCustomers');
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
            customerCard.className = 'bg-dark-surface/50 border border-white/5 rounded-2xl p-4';
            customerCard.innerHTML = `
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-xl font-bold text-white">${customer.name.charAt(0)}</div>
                    <div>
                        <h3 class="font-bold text-white">${customer.name}</h3>
                        <p class="text-sm text-gray-400">${customer.phone || ''}</p>
                    </div>
                </div>
            `;
            customersGrid.appendChild(customerCard);
        });
    }

    addCustomerBtn.addEventListener('click', () => {
        customerModal.classList.remove('hidden');
    });

    closeCustomerModalBtn.addEventListener('click', () => {
        customerModal.classList.add('hidden');
    });

    customerForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(customerForm);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('api.php?action=addCustomer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });
            const result = await response.json();
            if (result.success) {
                customerModal.classList.add('hidden');
                customerForm.reset();
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

    loadCustomers();
});
</script>
<?php require_once 'src/footer.php'; ?>