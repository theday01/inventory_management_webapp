<?php
$page_title = 'حساب الزكاة';
$current_page = 'zakat_calculator.php';
require_once 'session.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';
require_once 'db.php';

// Fetch Currency
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';
?>

<main class="flex-1 flex flex-col relative bg-dark">
    <div class="absolute top-0 right-[-10%] w-[500px] h-[500px] bg-primary/5 rounded-full blur-[120px] pointer-events-none">
    </div>
    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <div class="flex items-center gap-3">
            <span class="material-icons-round text-primary text-2xl">mosque</span>
            <h2 class="text-xl font-bold text-white">حساب الزكاة</h2>
        </div>
        <p class="text-gray-400 text-sm">احسب زكاة مالك بطريقة إسلامية دقيقة</p>
    </header>
    <div class="flex-1 overflow-y-auto p-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Islamic Guidelines - Left Side -->
                <div class="lg:col-span-1">
                    <div class="bg-dark-surface/60 backdrop-blur-md rounded-2xl shadow-lg border border-white/5 p-6 glass-panel sticky top-6">
                        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <span class="material-icons-round text-blue-400">info</span>
                            الإرشادات الإسلامية
                        </h2>
                        <div class="space-y-3 text-sm text-gray-400">
                            <p>• النصاب للذهب: 85 جرام ذهب خالص (قيمة 20 مثقال شرعي)</p>
                            <p>• النصاب للفضة: 595 جرام فضة خالصة (قيمة 200 درهم شرعي)</p>
                            <p>• الزكاة: 2.5% من صافي المال بعد طرح الديون والحاجات الأساسية</p>
                            <p>• تجب الزكاة بعد مرور عام هجري كامل على المال</p>
                            <p>• يجب إخراج الزكاة في مصارفها الشرعية الثمانية</p>
                            <p>• الأموال الزكوية تشمل: النقود، الذهب، الفضة، البضائع التجارية، الزروع، الثمار، النعم</p>
                        </div>
                    </div>

                    <!-- Contact Us Section -->
                    <div class="bg-dark-surface/60 backdrop-blur-md rounded-2xl shadow-lg border border-white/5 p-6 glass-panel mt-6">
                        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <span class="material-icons-round text-green-400">support_agent</span>
                            تواصل معنا
                        </h2>
                        <p class="text-sm text-gray-400 mb-4">
                            في حال وجدت أي مشكلة، او تضن أن هنالك خطء في حساب الزكاة، أو لديك فكرة أفضل لنتائج أحسن، رجاءً لا تتردد في تواصل معنا في أقرب وقت
                        </p>
                        <div class="space-y-3">
                            <!-- WhatsApp -->
                            <a href="https://wa.me/212700979284" target="_blank" class="flex items-center gap-3 p-3 bg-green-900/20 border border-green-800 rounded-lg hover:bg-green-900/40 transition-all">
                                <span class="material-icons-round text-green-400">call</span>
                                <div>
                                    <p class="text-sm font-medium text-green-300">واتساب</p>
                                    <p class="text-xs text-gray-400" dir="ltr">+212 700-979284</p>
                                </div>
                            </a>

                            <!-- Email -->
                            <a href="mailto:support@eagleshadow.technology" class="flex items-center gap-3 p-3 bg-blue-900/20 border border-blue-800 rounded-lg hover:bg-blue-900/40 transition-all">
                                <span class="material-icons-round text-blue-400">email</span>
                                <div>
                                    <p class="text-sm font-medium text-blue-300">البريد الإلكتروني</p>
                                    <p class="text-xs text-gray-400">ssupport@eagleshadow.technology</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Main Content - Right Side -->
                <div class="lg:col-span-2">
                    <div class="space-y-6">
            <!-- Calculator Form -->
            <div class="bg-dark-surface/60 backdrop-blur-md rounded-2xl shadow-lg border border-white/5 p-6 glass-panel">
                <form id="zakatForm" class="space-y-6">
                <!-- Year Check -->
                <div class="bg-amber-50/10 dark:bg-amber-900/20 border border-amber-200/30 dark:border-amber-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <span class="material-icons-round text-amber-400 mt-0.5">warning</span>
                        <div>
                            <h3 class="font-semibold text-amber-300">تذكير مهم</h3>
                            <p class="text-sm text-amber-200 mt-1">
                                الزكاة تجب بعد مرور عام هجري كامل على المال. تأكد من مرور العام قبل الحساب.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Assets Section -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-icons-round text-primary">account_balance_wallet</span>
                        الأموال الزكوية
                    </h2>

                    <!-- Gold and Silver Prices -->
                    <div class="bg-blue-900/20 border border-blue-800 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-300 mb-3">أسعار المعادن الحالية</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-blue-300 mb-2">
                                    سعر جرام الذهب (<?php echo $currency; ?>)
                                </label>
                                <input type="text" id="gold_price" name="gold_price" min="0" step="0.01" inputmode="numeric"
                                       class="w-full px-3 py-2 border border-blue-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-dark/50 text-white"
                                       placeholder="أدخل سعر جرام الذهب" value="500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-blue-300 mb-2">
                                    سعر جرام الفضة (<?php echo $currency; ?>)
                                </label>
                                <input type="text" id="silver_price" name="silver_price" min="0" step="0.01" inputmode="numeric"
                                       class="w-full px-3 py-2 border border-blue-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-dark/50 text-white"
                                       placeholder="أدخل سعر جرام الفضة" value="6">
                            </div>
                        </div>
                    </div>

                    <!-- Cash -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                النقود والعملات (<?php echo $currency; ?>)
                            </label>
                            <input type="text" id="cash" name="cash" min="0" step="0.01" inputmode="numeric"
                                   class="w-full px-3 py-2 border border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-dark/50 text-white"
                                   placeholder="أدخل قيمة النقود">
                        </div>

                        <!-- Gold -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                الذهب (جرام)
                            </label>
                            <input type="text" id="gold" name="gold" min="0" step="0.01" inputmode="numeric"
                                   class="w-full px-3 py-2 border border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-dark/50 text-white"
                                   placeholder="أدخل وزن الذهب">
                        </div>
                    </div>

                    <!-- Silver -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                الفضة (جرام)
                            </label>
                            <input type="text" id="silver" name="silver" min="0" step="0.01" inputmode="numeric"
                                   class="w-full px-3 py-2 border border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-dark/50 text-white"
                                   placeholder="أدخل وزن الفضة">
                        </div>

                        <!-- Trade Goods -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                البضائع التجارية (<?php echo $currency; ?>)
                            </label>
                            <input type="text" id="trade_goods" name="trade_goods" min="0" step="0.01" inputmode="numeric"
                                   class="w-full px-3 py-2 border border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-dark/50 text-white"
                                   placeholder="قيمة البضائع المعدة للتجارة">
                        </div>
                    </div>
                </div>

                <!-- Deductions Section -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-icons-round text-orange-400">remove_circle</span>
                        المستقطعات (الطرح)
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Debts -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                الديون المستحقة عليك (<?php echo $currency; ?>)
                            </label>
                            <input type="text" id="debts" name="debts" min="0" step="0.01" inputmode="numeric"
                                   class="w-full px-3 py-2 border border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-dark/50 text-white"
                                   placeholder="الديون التي تدين بها">
                        </div>

                        <!-- Basic Needs -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                الحاجات الأساسية (<?php echo $currency; ?>)
                            </label>
                            <input type="text" id="basic_needs" name="basic_needs" min="0" step="0.01" inputmode="numeric"
                                   class="w-full px-3 py-2 border border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-dark/50 text-white"
                                   placeholder="المصاريف الأساسية للسنة">
                        </div>
                    </div>
                </div>

                <!-- Calculate Button -->
                <div class="flex justify-center pt-4">
                    <button type="button" onclick="calculateZakat()"
                            class="px-8 py-3 bg-primary hover:bg-primary-hover text-white font-semibold rounded-lg transition-colors flex items-center gap-2 shadow-lg shadow-primary/20">
                        <span class="material-icons-round">calculate</span>
                        احسب الزكاة
                    </button>
                </div>
            </form>

            <!-- Results Section -->
            <div id="results" class="mt-8 space-y-4 hidden">
                <div class="border-t border-white/10 pt-6">
                    <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <span class="material-icons-round text-green-400">receipt</span>
                        نتائج الحساب
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Total Assets -->
                        <div class="bg-dark/50 rounded-lg p-4 border border-white/10">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-400">إجمالي الأموال الزكوية</span>
                                <span id="totalAssets" class="font-bold text-white">0 <?php echo $currency; ?></span>
                            </div>
                        </div>

                        <!-- Net Assets -->
                        <div class="bg-dark/50 rounded-lg p-4 border border-white/10">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-400">صافي الأموال بعد الطرح</span>
                                <span id="netAssets" class="font-bold text-white">0 <?php echo $currency; ?></span>
                            </div>
                        </div>

                        <!-- Nisab Check -->
                        <div class="bg-dark/50 rounded-lg p-4 border border-white/10">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-400">النصاب الشرعي</span>
                                <span id="nisabAmount" class="font-bold text-white">0 <?php echo $currency; ?></span>
                            </div>
                        </div>

                        <!-- Zakat Amount -->
                        <div class="bg-green-900/20 border border-green-800 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-green-300">مقدار الزكاة المستحقة</span>
                                <span id="zakatAmount" class="font-bold text-green-300 text-xl">0 <?php echo $currency; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Message -->
                    <div id="statusMessage" class="mt-4 p-4 rounded-lg hidden border">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Constants
const NISAB_GOLD_GRAMS = 85;
const NISAB_SILVER_GRAMS = 595;
const ZAKAT_RATE = 0.025; // 2.5%

function toEnglishNumbers(str) {
    if (typeof str === 'undefined' || str === null) {
        return '';
    }
    const arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    
    let result = str.toString();
    for (let i = 0; i < 10; i++) {
        result = result.replace(new RegExp(arabicNumbers[i], 'g'), englishNumbers[i]);
    }
    return result;
}

function calculateZakat() {
    // Get values
    const goldPrice = parseFloat(document.getElementById('gold_price').value) || 0;
    const silverPrice = parseFloat(document.getElementById('silver_price').value) || 0;
    const cash = parseFloat(document.getElementById('cash').value) || 0;
    const goldWeight = parseFloat(document.getElementById('gold').value) || 0;
    const silverWeight = parseFloat(document.getElementById('silver').value) || 0;
    const tradeGoods = parseFloat(document.getElementById('trade_goods').value) || 0;
    const debts = parseFloat(document.getElementById('debts').value) || 0;
    const basicNeeds = parseFloat(document.getElementById('basic_needs').value) || 0;

    // Calculate asset values
    const goldValue = goldWeight * goldPrice;
    const silverValue = silverWeight * silverPrice;

    // Total assets
    const totalAssets = cash + goldValue + silverValue + tradeGoods;

    // Net assets after deductions
    const netAssets = Math.max(0, totalAssets - debts - basicNeeds);

    // Nisab calculation (minimum of gold or silver nisab)
    const goldNisabValue = NISAB_GOLD_GRAMS * goldPrice;
    const silverNisabValue = NISAB_SILVER_GRAMS * silverPrice;
    const nisabValue = Math.min(goldNisabValue, silverNisabValue);

    // Zakat calculation
    let zakatAmount = 0;
    let statusMessage = '';
    let statusClass = '';

    if (netAssets >= nisabValue) {
        zakatAmount = netAssets * ZAKAT_RATE;
        statusMessage = 'يجب عليك إخراج الزكاة بمقدار ' + zakatAmount.toFixed(2) + ' <?php echo $currency; ?> في مصارفها الشرعية الثمانية';
        statusClass = 'bg-green-900/20 border-green-800 text-green-300';
    } else {
        statusMessage = 'لم يبلغ مالك النصاب الشرعي (' + nisabValue.toFixed(2) + ' <?php echo $currency; ?>)، لا زكاة عليك هذا العام';
        statusClass = 'bg-blue-900/20 border-blue-800 text-blue-300';
    }

    // Update results
    document.getElementById('totalAssets').textContent = totalAssets.toFixed(2) + ' <?php echo $currency; ?>';
    document.getElementById('netAssets').textContent = netAssets.toFixed(2) + ' <?php echo $currency; ?>';
    document.getElementById('nisabAmount').textContent = nisabValue.toFixed(2) + ' <?php echo $currency; ?>';
    document.getElementById('zakatAmount').textContent = zakatAmount.toFixed(2) + ' <?php echo $currency; ?>';

    // Show status message
    const statusDiv = document.getElementById('statusMessage');
    statusDiv.className = `mt-4 p-4 rounded-lg border ${statusClass}`;
    statusDiv.textContent = statusMessage;
    statusDiv.classList.remove('hidden');

    // Show results
    document.getElementById('results').classList.remove('hidden');
}

// Add input validation for numeric inputs
const numericInputs = document.querySelectorAll('input[inputmode="numeric"]');
numericInputs.forEach(input => {
    input.addEventListener('keydown', function(e) {
        const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
        if (allowedKeys.includes(e.key)) return;
        if (!/[0-9٠-٩.]/.test(e.key)) {
            e.preventDefault();
        }
    });
    input.addEventListener('input', function() {
        let value = this.value;
        value = toEnglishNumbers(value);
        value = value.replace(/[^0-9.]/g, '');
        this.value = value;
    });
});

// Auto-calculate on input change
document.querySelectorAll('input[inputmode="numeric"]').forEach(input => {
    input.addEventListener('input', function() {
        if (document.getElementById('results').classList.contains('hidden') === false) {
            calculateZakat();
        }
    });
});
</script>

<?php require_once 'src/footer.php'; ?>