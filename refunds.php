<?php
$page_title = 'الفواتير والمنتجات المسترجعة';
$current_page = 'refunds.php';
require_once 'session.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';
?>

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden">
    <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>

    <!-- Header -->
    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <h2 class="text-xl font-bold text-white flex items-center gap-3">
            <span class="material-icons-round text-red-500">assignment_return</span>
            سجل الفواتير والمنتجات المسترجعة
        </h2>
    </header>

    <div class="flex-1 overflow-y-auto p-8 relative z-10" style="max-height: calc(100vh - 5rem);">
        <div class="grid grid-cols-1 gap-8">
            <section class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">

                <form id="search-form" class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label for="search-term" class="text-sm font-medium text-gray-300 mb-1 block">بحث</label>
                        <div class="relative">
                            <input type="text" id="search-term" name="search" class="w-full bg-dark-surface/50 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-primary" placeholder="رقم الفاتورة, اسم العميل, سبب الاسترجاع...">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-5 py-2 rounded-lg font-bold flex items-center gap-2 transition-all">
                            <span class="material-icons-round">search</span>
                            <span>بحث</span>
                        </button>
                        <button type="button" id="clear-search-btn" class="bg-gray-600 hover:bg-gray-500 text-white px-5 py-2 rounded-lg font-bold flex items-center gap-2 transition-all">
                            <span class="material-icons-round">clear</span>
                            <span>مسح</span>
                        </button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-right">
                        <thead>
                            <tr class="border-b border-white/10">
                                <th class="p-4 text-sm font-bold text-gray-400">التاريخ</th>
                                <th class="p-4 text-sm font-bold text-gray-400">رقم الفاتورة</th>
                                <th class="p-4 text-sm font-bold text-gray-400">العميل</th>
                                <th class="p-4 text-sm font-bold text-gray-400 w-1/3">المنتجات المسترجعة</th>
                                <th class="p-4 text-sm font-bold text-gray-400">المبلغ</th>
                                <th class="p-4 text-sm font-bold text-gray-400">السبب</th>
                            </tr>
                        </thead>
                        <tbody id="refunds-table-body">
                            <tr>
                                <td colspan="6" class="text-center py-4 text-gray-500">
                                    جاري تحميل البيانات...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <!-- Pagination -->
    <div id="pagination-container" class="p-6 pt-2 flex justify-center items-center relative z-10 shrink-0">
        <!-- Pagination will be loaded here -->
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableBody = document.getElementById('refunds-table-body');
        const searchForm = document.getElementById('search-form');
        const searchTermInput = document.getElementById('search-term');
        const clearSearchBtn = document.getElementById('clear-search-btn');
        const paginationContainer = document.getElementById('pagination-container');

        let currentPage = 1;
        const limit = 50;
        const currency = '<?php echo $currency; ?>';

        function toEnglishNumbers(str) {
            const arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            let result = str.toString();
            for (let i = 0; i < 10; i++) {
                result = result.replace(new RegExp(arabicNumbers[i], 'g'), englishNumbers[i]);
            }
            return result;
        }

        async function loadRefunds(searchTerm = '') {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-gray-500">جاري تحميل البيانات...</td></tr>';
            
            const params = new URLSearchParams({
                action: 'getRefunds',
                search: searchTerm,
                page: currentPage,
                limit: limit
            });

            try {
                const response = await fetch(`api.php?${params.toString()}`);
                const result = await response.json();

                if (result.success) {
                    displayRefunds(result.data);
                    renderPagination(result.total_refunds);
                } else {
                    tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-gray-500">فشل في تحميل البيانات</td></tr>';
                }
            } catch (error) {
                console.error('Error loading refunds:', error);
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">حدث خطأ في التحميل</td></tr>';
            }
        }

        function displayRefunds(refunds) {
            tableBody.innerHTML = '';
            
            if (refunds.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">لا توجد سجلات استرجاع</td></tr>';
                return;
            }

            refunds.forEach(refund => {
                const row = document.createElement('tr');
                row.className = 'border-b border-white/5 hover:bg-white/5 transition-colors';
                
                const date = new Date(refund.created_at);
                const formattedDate = date.toLocaleDateString('en-US', {
                    year: 'numeric', month: 'short', day: 'numeric',
                    hour: '2-digit', minute: '2-digit', hour12: false
                });

                row.innerHTML = `
                    <td class="p-4 text-sm text-gray-300" dir="ltr">${formattedDate}</td>
                    <td class="p-4 text-sm font-bold text-primary">#${String(refund.invoice_id).padStart(6, '0')}</td>
                    <td class="p-4 text-sm text-gray-300">${refund.customer_name || 'عميل نقدي'}</td>
                    <td class="p-4 text-sm text-gray-300">
                        <div class="max-w-md truncate" title="${refund.items_summary || ''}">
                            ${refund.items_summary || '<span class="italic opacity-50">لا توجد تفاصيل</span>'}
                        </div>
                    </td>
                    <td class="p-4 text-sm font-bold text-white">${parseFloat(refund.amount).toFixed(2)} ${currency}</td>
                    <td class="p-4 text-sm text-gray-400">${refund.reason || '-'}</td>
                `;
                
                tableBody.appendChild(row);
            });
        }

        function renderPagination(totalItems) {
            const totalPages = Math.ceil(totalItems / limit);
            paginationContainer.innerHTML = '';

            if (totalPages <= 1) return;

            let paginationHTML = '<div class="flex items-center gap-2">';
            
            paginationHTML += `<button class="pagination-btn ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''}" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}><span class="material-icons-round">chevron_right</span></button>`;

            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    paginationHTML += `<button class="pagination-btn ${i === currentPage ? 'bg-primary text-white' : 'hover:bg-white/10'}" data-page="${i}">${i}</button>`;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    paginationHTML += `<span class="px-2 text-gray-500">...</span>`;
                }
            }

            paginationHTML += `<button class="pagination-btn ${currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''}" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}><span class="material-icons-round">chevron_left</span></button>`;
            paginationHTML += '</div>';
            
            paginationContainer.innerHTML = paginationHTML;
        }

        paginationContainer.addEventListener('click', e => {
            const btn = e.target.closest('.pagination-btn');
            if (btn && !btn.disabled) {
                currentPage = parseInt(btn.dataset.page);
                loadRefunds(searchTermInput.value);
            }
        });

        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            currentPage = 1;
            loadRefunds(searchTermInput.value);
        });

        clearSearchBtn.addEventListener('click', function() {
            searchForm.reset();
            currentPage = 1;
            loadRefunds();
        });

        loadRefunds();
    });
</script>

<style>
    .pagination-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0.5rem;
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: rgb(209, 213, 219);
        border-radius: 0.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .pagination-btn:hover:not(:disabled):not(.bg-primary) {
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
    }
    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .bg-primary {
        background-color: #059669; /* Adjust if primary color var differs */
    }
</style>

<?php require_once 'src/footer.php'; ?>
