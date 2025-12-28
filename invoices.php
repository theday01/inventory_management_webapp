<?php
$page_title = 'الفواتير والضريبة';
$current_page = 'invoices.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';
?>

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden">
    <div
        class="absolute top-0 left-0 w-[600px] h-[600px] bg-primary/5 rounded-full blur-[120px] pointer-events-none">
    </div>

    <!-- Header -->
    <header
        class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <h2 class="text-xl font-bold text-white">الفواتير والضريبة</h2>
    </header>

    <div class="flex-1 overflow-y-auto p-8 relative z-10" style="max-height: calc(100vh - 5rem);">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Invoices Content -->
            <div class="lg:col-span-2 space-y-6">
                <section
                    class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                    <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                        <span class="material-icons-round text-primary">receipt</span>
                        الفواتير
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-right">
                            <thead>
                                <tr class="border-b border-white/10">
                                    <th class="p-4 text-sm font-bold text-gray-400">رقم الفاتورة</th>
                                    <th class="p-4 text-sm font-bold text-gray-400">التاريخ</th>
                                    <th class="p-4 text-sm font-bold text-gray-400">العميل</th>
                                    <th class="p-4 text-sm font-bold text-gray-400">المبلغ</th>
                                    <th class="p-4 text-sm font-bold text-gray-400"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-gray-500">
                                        لا توجد أي بيانات لعرضها الآن.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Invoice Modal -->
    <div id="invoiceModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden" onclick="closeInvoiceModal()">
        <div class="bg-dark-surface rounded-2xl w-full max-w-lg mx-4" onclick="event.stopPropagation()">
            <div class="p-6 border-b border-white/10 flex justify-between items-center">
                <h3 class="text-lg font-bold">تفاصيل الفاتورة</h3>
                <button onclick="closeInvoiceModal()" class="text-gray-400 hover:text-white">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <div class="p-6 text-center" id="invoiceContent">
                <!-- Dynamic content will be injected here -->
            </div>
            <div class="p-6 bg-dark/50 rounded-b-2xl flex justify-end">
                 <button onclick="printInvoice()" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all hover:-translate-y-0.5 flex items-center gap-2">
                    <span class="material-icons-round">print</span>
                    طباعة
                </button>
            </div>
        </div>
    </div>
</main>

<?php require_once 'src/footer.php'; ?>