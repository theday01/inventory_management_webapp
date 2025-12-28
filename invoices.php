<?php
$page_title = 'الفواتير والضريبة';
$current_page = 'invoices.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopName'");
$shopName = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'Smart Shop';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopPhone'");
$shopPhone = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopAddress'");
$shopAddress = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'taxEnabled'");
$taxEnabled = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '1';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'taxRate'");
$taxRate = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '20';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'taxLabel'");
$taxLabel = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'TVA';
?>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #invoice-print-area, #invoice-print-area * {
        visibility: visible;
    }
    #invoice-print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: white;
    }
    .no-print {
        display: none !important;
    }
    
    /* تحسين الطباعة للمنتجات الكثيرة */
    .invoice-items-container {
        page-break-inside: auto;
    }
    
    .invoice-item-row {
        page-break-inside: avoid;
    }
}

/* تحسين عرض الفاتورة في Modal */
.invoice-modal-content {
    max-height: 80vh;
    overflow-y: auto;
}

.invoice-items-scrollable {
    max-height: 400px;
    overflow-y: auto;
    overflow-x: hidden;
}

/* تحسين شريط التمرير */
.invoice-items-scrollable::-webkit-scrollbar {
    width: 6px;
}

.invoice-items-scrollable::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.invoice-items-scrollable::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.invoice-items-scrollable::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden">
    <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>

    <!-- Header -->
    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <h2 class="text-xl font-bold text-white">الفواتير والضريبة</h2>
    </header>

    <div class="flex-1 overflow-y-auto p-8 relative z-10" style="max-height: calc(100vh - 5rem);">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Invoices Content -->
            <div class="lg:col-span-3 space-y-6">
                <section class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                    <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                        <span class="material-icons-round text-primary">receipt</span>
                        الفواتير الأخيرة
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
                            <tbody id="invoices-table-body">
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-gray-500">
                                        جاري تحميل البيانات...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</main>

<!-- Invoice Modal - محسّن -->
<div id="invoice-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-auto overflow-hidden flex flex-col" style="max-height: 90vh;">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-primary to-accent p-6 text-white no-print shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="material-icons-round text-3xl">receipt_long</span>
                    <div>
                        <h3 class="text-2xl font-bold">عرض الفاتورة</h3>
                        <p class="text-sm opacity-90">تفاصيل الفاتورة الكاملة</p>
                    </div>
                </div>
                <button id="close-invoice-modal" class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
        </div>

        <!-- Invoice Content - مع scroll -->
        <div class="flex-1 overflow-y-auto">
            <div id="invoice-print-area" class="p-8 bg-white text-gray-900">
                <!-- Shop Header -->
                <div class="text-center border-b-2 border-gray-300 pb-6 mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($shopName); ?></h1>
                    <?php if ($shopPhone): ?>
                        <p class="text-sm text-gray-600">هاتف: <?php echo htmlspecialchars($shopPhone); ?></p>
                    <?php endif; ?>
                    <?php if ($shopAddress): ?>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($shopAddress); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Invoice Info -->
                <div class="grid grid-cols-2 gap-6 mb-6 text-sm">
                    <div>
                        <p class="text-gray-600 mb-1">رقم الفاتورة</p>
                        <p class="font-bold text-lg" id="invoice-number">-</p>
                    </div>
                    <div class="text-left">
                        <p class="text-gray-600 mb-1">التاريخ</p>
                        <p class="font-bold" id="invoice-date">-</p>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h3 class="font-bold text-gray-900 mb-2">معلومات العميل</h3>
                    <div id="customer-info" class="text-sm text-gray-700"></div>
                </div>

                <!-- Items Table - مع scrolling للمنتجات الكثيرة -->
                <div class="mb-6">
                    <div class="invoice-items-scrollable">
                        <table class="w-full text-sm invoice-items-container">
                            <thead class="sticky top-0 bg-white">
                                <tr class="border-b-2 border-gray-300">
                                    <th class="text-right py-3 font-bold">#</th>
                                    <th class="text-right py-3 font-bold">المنتج</th>
                                    <th class="text-center py-3 font-bold">الكمية</th>
                                    <th class="text-center py-3 font-bold">السعر</th>
                                    <th class="text-left py-3 font-bold">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody id="invoice-items"></tbody>
                        </table>
                    </div>
                    <div id="items-count-badge" class="text-xs text-gray-500 mt-2 text-center hidden">
                        <!-- سيتم عرض عدد المنتجات هنا -->
                    </div>
                </div>

                <!-- Totals -->
                <div class="border-t-2 border-gray-300 pt-4">
                    <div class="flex justify-end">
                        <div class="w-64 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">المجموع الفرعي:</span>
                                <span class="font-medium" id="invoice-subtotal">-</span>
                            </div>
                            <div class="flex justify-between" id="invoice-tax-row">
                                <span class="text-gray-600"><span id="invoice-tax-label">TVA</span> (<span id="invoice-tax-rate">20</span>%):</span>
                                <span class="font-medium" id="invoice-tax-amount">-</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t-2 border-gray-300 pt-2">
                                <span>الإجمالي:</span>
                                <span class="text-primary" id="invoice-total">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-8 pt-6 border-t border-gray-200 text-xs text-gray-500">
                    <p class="font-semibold text-gray-700 mb-3" style="font-size: 14px;">شكرا لثقتكم بنا</p>
                    <?php if (!empty($shopName) || !empty($shopPhone) || !empty($shopAddress)): ?>
                        <div class="mt-3 text-gray-600 space-y-1">
                            <?php if (!empty($shopName)): ?>
                                <p class="font-medium"><?php echo htmlspecialchars($shopName); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($shopPhone)): ?>
                                <p>هاتف: <?php echo htmlspecialchars($shopPhone); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($shopAddress)): ?>
                                <p><?php echo htmlspecialchars($shopAddress); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="mt-3 space-y-1">
                            <p class="text-gray-600">تم تصميم وتطوير النظام من طرف حمزة سعدي 2025</p>
                            <p class="text-gray-600">الموقع الإلكتروني: <span class="text-blue-600">https://eagleshadow.technology</span></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-gray-50 p-6 flex gap-3 no-print border-t shrink-0">
            <button id="print-invoice-btn" class="flex-1 bg-primary hover:bg-primary-hover text-white py-3 px-6 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                <span class="material-icons-round">print</span>
                طباعة
            </button>
            <button id="download-pdf-btn" class="flex-1 bg-accent hover:bg-lime-500 text-white py-3 px-6 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                <span class="material-icons-round">picture_as_pdf</span>
                تحميل PDF
            </button>
            <button id="download-txt-btn" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-3 px-6 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                <span class="material-icons-round">text_snippet</span>
                تحميل TXT
            </button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const invoicesTableBody = document.getElementById('invoices-table-body');
    const invoiceModal = document.getElementById('invoice-modal');
    const closeInvoiceModal = document.getElementById('close-invoice-modal');
    const printInvoiceBtn = document.getElementById('print-invoice-btn');
    const downloadPdfBtn = document.getElementById('download-pdf-btn');
    const downloadTxtBtn = document.getElementById('download-txt-btn');
    
    let currentInvoiceData = null;
    const currency = '<?php echo $currency; ?>';
    const taxEnabled = <?php echo $taxEnabled; ?> == 1;
    const taxRate = <?php echo $taxRate; ?> / 100;
    const taxLabel = '<?php echo addslashes($taxLabel); ?>';
    const shopName = '<?php echo addslashes($shopName); ?>';

    // دالة لتحويل الأرقام العربية إلى أرقام إنجليزية
    function toEnglishNumbers(str) {
        const arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        let result = str.toString();
        for (let i = 0; i < 10; i++) {
            result = result.replace(new RegExp(arabicNumbers[i], 'g'), englishNumbers[i]);
        }
        return result;
    }

    // دالة لتنسيق التاريخ (ميلادي وهجري)
    function formatDualDate(date) {
        // التاريخ الميلادي
        const gregorianDate = date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
        
        // التاريخ الهجري
        const hijriDate = date.toLocaleDateString('ar-SA-u-ca-islamic', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // تحويل الأرقام إلى إنجليزية
        const hijriDateEng = toEnglishNumbers(hijriDate);
        
        return `${gregorianDate} - ${hijriDateEng}`;
    }

    async function loadInvoices() {
        try {
            const response = await fetch('api.php?action=getInvoices');
            const result = await response.json();
            
            if (result.success) {
                displayInvoices(result.data);
            } else {
                invoicesTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">فشل في تحميل الفواتير</td></tr>';
            }
        } catch (error) {
            console.error('خطأ في تحميل الفواتير:', error);
            invoicesTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">حدث خطأ في التحميل</td></tr>';
        }
    }

    function displayInvoices(invoices) {
        invoicesTableBody.innerHTML = '';
        
        if (invoices.length === 0) {
            invoicesTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">لا توجد فواتير حتى الآن</td></tr>';
            return;
        }

        invoices.forEach(invoice => {
            const row = document.createElement('tr');
            row.className = 'border-b border-white/5 hover:bg-white/5 transition-colors';
            
            const invoiceDate = new Date(invoice.created_at);
            const gregorianDate = invoiceDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
            const formattedDate = toEnglishNumbers(gregorianDate);
            
            row.innerHTML = `
                <td class="p-4 text-sm font-bold text-primary">#${String(invoice.id).padStart(6, '0')}</td>
                <td class="p-4 text-sm text-gray-300">${formattedDate}</td>
                <td class="p-4 text-sm text-gray-300">${invoice.customer_name || 'عميل نقدي'}</td>
                <td class="p-4 text-sm font-bold text-white">${parseFloat(invoice.total).toFixed(2)} ${currency}</td>
                <td class="p-4">
                    <button class="view-invoice-btn bg-primary/10 hover:bg-primary/20 text-primary px-4 py-2 rounded-lg text-sm font-bold transition-all" data-id="${invoice.id}">
                        عرض
                    </button>
                </td>
            `;
            
            invoicesTableBody.appendChild(row);
        });

        // Add click handlers
        document.querySelectorAll('.view-invoice-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const invoiceId = this.dataset.id;
                await viewInvoice(invoiceId);
            });
        });
    }

    async function viewInvoice(invoiceId) {
        try {
            const response = await fetch(`api.php?action=getInvoice&id=${invoiceId}`);
            const result = await response.json();
            
            if (result.success) {
                currentInvoiceData = result.data;
                displayInvoiceDetails(currentInvoiceData);
                invoiceModal.classList.remove('hidden');
            } else {
                showToast('فشل في تحميل الفاتورة', false);
            }
        } catch (error) {
            console.error('خطأ في تحميل الفاتورة:', error);
            showToast('حدث خطأ في تحميل الفاتورة', false);
        }
    }

    function displayInvoiceDetails(invoice) {
        document.getElementById('invoice-number').textContent = `#${String(invoice.id).padStart(6, '0')}`;
        
        const invoiceDate = new Date(invoice.created_at);
        document.getElementById('invoice-date').textContent = formatDualDate(invoiceDate);
        
        const customerInfo = document.getElementById('customer-info');
        if (invoice.customer_name) {
            customerInfo.innerHTML = `
                <p><strong>الاسم:</strong> ${invoice.customer_name}</p>
                ${invoice.customer_phone ? `<p><strong>الهاتف:</strong> ${invoice.customer_phone}</p>` : ''}
                ${invoice.customer_email ? `<p><strong>البريد:</strong> ${invoice.customer_email}</p>` : ''}
                ${invoice.customer_address ? `<p><strong>العنوان:</strong> ${invoice.customer_address}</p>` : ''}
            `;
        } else {
            customerInfo.innerHTML = '<p>عميل نقدي</p>';
        }
        
        const itemsTable = document.getElementById('invoice-items');
        itemsTable.innerHTML = '';
        
        // عرض badge لعدد المنتجات إذا كانت أكثر من 10
        const itemsCountBadge = document.getElementById('items-count-badge');
        if (invoice.items.length > 10) {
            itemsCountBadge.textContent = `إجمالي ${invoice.items.length} منتج في هذه الفاتورة`;
            itemsCountBadge.classList.remove('hidden');
        } else {
            itemsCountBadge.classList.add('hidden');
        }
        
        let subtotal = 0;
        invoice.items.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-200 invoice-item-row';
            row.innerHTML = `
                <td class="py-2">${index + 1}</td>
                <td class="py-2">${item.product_name}</td>
                <td class="py-2 text-center">${item.quantity}</td>
                <td class="py-2 text-center">${parseFloat(item.price).toFixed(2)} ${currency}</td>
                <td class="py-2 text-left font-medium">${itemTotal.toFixed(2)} ${currency}</td>
            `;
            itemsTable.appendChild(row);
        });
        
        const tax = taxEnabled ? subtotal * taxRate : 0;
        const total = subtotal + tax;
        
        document.getElementById('invoice-subtotal').textContent = `${subtotal.toFixed(2)} ${currency}`;
        
        if (taxEnabled) {
            document.getElementById('invoice-tax-row').style.display = 'flex';
            document.getElementById('invoice-tax-label').textContent = taxLabel;
            document.getElementById('invoice-tax-rate').textContent = (taxRate * 100).toFixed(0);
            document.getElementById('invoice-tax-amount').textContent = `${tax.toFixed(2)} ${currency}`;
        } else {
            document.getElementById('invoice-tax-row').style.display = 'none';
        }
        
        document.getElementById('invoice-total').textContent = `${total.toFixed(2)} ${currency}`;
    }

    closeInvoiceModal.addEventListener('click', () => {
        invoiceModal.classList.add('hidden');
    });

    printInvoiceBtn.addEventListener('click', () => {
        window.print();
    });

    downloadPdfBtn.addEventListener('click', async () => {
        const { jsPDF } = window.jspdf;
        
        try {
            showToast('جاري إنشاء ملف PDF...', true);
            
            // إخفاء scrollbar مؤقتاً
            const scrollableDiv = document.querySelector('.invoice-items-scrollable');
            const originalMaxHeight = scrollableDiv.style.maxHeight;
            scrollableDiv.style.maxHeight = 'none';
            scrollableDiv.style.overflow = 'visible';
            
            const element = document.getElementById('invoice-print-area');
            
            const canvas = await html2canvas(element, {
                scale: 2,
                backgroundColor: '#ffffff',
                logging: false,
                useCORS: true
            });
            
            // استعادة الـ scrollbar
            scrollableDiv.style.maxHeight = originalMaxHeight;
            scrollableDiv.style.overflow = 'auto';
            
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight();
            const imgWidth = pdfWidth;
            const imgHeight = (canvas.height * pdfWidth) / canvas.width;
            
            // إذا كانت الصورة أطول من صفحة واحدة، قسمها على صفحات متعددة
            if (imgHeight > pdfHeight) {
                let heightLeft = imgHeight;
                let position = 0;
                let page = 0;
                
                while (heightLeft > 0) {
                    if (page > 0) {
                        pdf.addPage();
                    }
                    
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pdfHeight;
                    position -= pdfHeight;
                    page++;
                }
            } else {
                pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);
            }
            
            pdf.save(`invoice-${currentInvoiceData.id}.pdf`);
            
            showToast('تم تحميل الفاتورة بصيغة PDF', true);
        } catch (error) {
            console.error('خطأ في تحميل PDF:', error);
            showToast('فشل في تحميل PDF', false);
        }
    });

    downloadTxtBtn.addEventListener('click', () => {
        if (!currentInvoiceData) return;
        
        const invoiceDate = new Date(currentInvoiceData.created_at);
        
        let txtContent = `${shopName}\n`;
        txtContent += `${'='.repeat(50)}\n\n`;
        txtContent += `رقم الفاتورة: #${String(currentInvoiceData.id).padStart(6, '0')}\n`;
        txtContent += `التاريخ: ${formatDualDate(invoiceDate)}\n\n`;
        
        if (currentInvoiceData.customer_name) {
            txtContent += `العميل: ${currentInvoiceData.customer_name}\n`;
            if (currentInvoiceData.customer_phone) {
                txtContent += `الهاتف: ${currentInvoiceData.customer_phone}\n`;
            }
        } else {
            txtContent += `العميل: عميل نقدي\n`;
        }
        
        txtContent += `\n${'-'.repeat(50)}\n`;
        txtContent += `المنتجات (${currentInvoiceData.items.length} منتج):\n`;
        txtContent += `${'-'.repeat(50)}\n\n`;
        
        let subtotal = 0;
        currentInvoiceData.items.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            
            txtContent += `${index + 1}. ${item.product_name}\n`;
            txtContent += `   الكمية: ${item.quantity} × ${parseFloat(item.price).toFixed(2)} ${currency} = ${itemTotal.toFixed(2)} ${currency}\n\n`;
        });
        
        const tax = taxEnabled ? subtotal * taxRate : 0;
        const total = subtotal + tax;
        
        txtContent += `${'-'.repeat(50)}\n`;
        txtContent += `المجموع الفرعي: ${subtotal.toFixed(2)} ${currency}\n`;
        
        if (taxEnabled) {
            txtContent += `${taxLabel} (${(taxRate * 100).toFixed(0)}%): ${tax.toFixed(2)} ${currency}\n`;
        }
        
        txtContent += `الإجمالي: ${total.toFixed(2)} ${currency}\n`;
        txtContent += `${'='.repeat(50)}\n\n`;
        
        // الفوتر المحدث
        txtContent += `شكرا لثقتكم بنا\n\n`;
        
        // التحقق من وجود بيانات المتجر
        const shopPhone = '<?php echo addslashes($shopPhone); ?>';
        const shopAddress = '<?php echo addslashes($shopAddress); ?>';
        
        if (shopName || shopPhone || shopAddress) {
            if (shopName) txtContent += `${shopName}\n`;
            if (shopPhone) txtContent += `هاتف: ${shopPhone}\n`;
            if (shopAddress) txtContent += `${shopAddress}\n`;
        } else {
            txtContent += `تم تصميم وتطوير النظام من طرف حمزة سعدي 2025\n`;
            txtContent += `الموقع الإلكتروني: https://eagleshadow.technology\n`;
        }
        
        const blob = new Blob([txtContent], { type: 'text/plain;charset=utf-8' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `invoice-${currentInvoiceData.id}.txt`;
        link.click();
        
        showToast('تم تحميل الفاتورة بصيغة TXT', true);
    });
    
    loadInvoices();
});
</script>

<?php require_once 'src/footer.php'; ?>