<?php
require_once 'session.php';
require_once 'db.php';

$page_title = 'حساب الزكاة';
$current_page = 'zakat_calculator.php';

require_once 'src/header.php';
require_once 'src/sidebar.php';

// Fetch Currency
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';
?>

<div class="flex-1 overflow-y-auto p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white dark:bg-dark-surface rounded-xl shadow-sm border border-gray-200 dark:border-white/5 p-6 mb-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                    <span class="material-icons-round text-primary text-2xl">mosque</span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">حساب الزكاة</h1>
                    <p class="text-gray-600 dark:text-gray-400">احسب زكاة مالك بطريقة إسلامية دقيقة</p>
                </div>
            </div>
        </div>

        <!-- Calculator Form -->
        <div class="bg-white dark:bg-dark-surface rounded-xl shadow-sm border border-gray-200 dark:border-white/5 p-6">
            <form id="zakatForm" class="space-y-6">
                <!-- Year Check -->
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <span class="material-icons-round text-amber-600 dark:text-amber-400 mt-0.5">warning</span>
                        <div>
                            <h3 class="font-semibold text-amber-800 dark:text-amber-200">تذكير مهم</h3>
                            <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                الزكاة تجب بعد مرور عام هجري كامل على المال. تأكد من مرور العام قبل الحساب.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Assets Section -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="material-icons-round text-primary">account_balance_wallet</span>
                        الأموال الزكوية
                    </h2>

                    <!-- Gold and Silver Prices -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 dark:text-blue-200 mb-3">أسعار المعادن الحالية</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-blue-700 dark:text-blue-300 mb-2">
                                    سعر جرام الذهب (<?php echo $currency; ?>)
                                </label>
                                <input type="number" id="gold_price" name="gold_price" min="0" step="0.01"
                                       class="w-full px-3 py-2 border border-blue-300 dark:border-blue-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-dark-surface text-gray-900 dark:text-white"
                                       placeholder="أدخل سعر جرام الذهب" value="500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-blue-700 dark:text-blue-300 mb-2">
                                    سعر جرام الفضة (<?php echo $currency; ?>)
                                </label>
                                <input type="number" id="silver_price" name="silver_price" min="0" step="0.01"
                                       class="w-full px-3 py-2 border border-blue-300 dark:border-blue-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-dark-surface text-gray-900 dark:text-white"
                                       placeholder="أدخل سعر جرام الفضة" value="6">
                            </div>
                        </div>
                    </div>

                    <!-- Cash -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                النقود والعملات (<?php echo $currency; ?>)
                            </label>
                            <input type="number" id="cash" name="cash" min="0" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-dark-surface text-gray-900 dark:text-white"
                                   placeholder="أدخل قيمة النقود">
                        </div>

                        <!-- Gold -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                الذهب (جرام)
                            </label>
                            <input type="number" id="gold" name="gold" min="0" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-dark-surface text-gray-900 dark:text-white"
                                   placeholder="أدخل وزن الذهب">
                        </div>
                    </div>

                    <!-- Silver -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                الفضة (جرام)
                            </label>
                            <input type="number" id="silver" name="silver" min="0" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-dark-surface text-gray-900 dark:text-white"
                                   placeholder="أدخل وزن الفضة">
                        </div>

                        <!-- Trade Goods -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                البضائع التجارية (<?php echo $currency; ?>)
                            </label>
                            <input type="number" id="trade_goods" name="trade_goods" min="0" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-dark-surface text-gray-900 dark:text-white"
                                   placeholder="قيمة البضائع المعدة للتجارة">
                        </div>
                    </div>
                </div>

                <!-- Deductions Section -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="material-icons-round text-orange-500">remove_circle</span>
                        المستقطعات (الطرح)
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Debts -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                الديون المستحقة عليك (<?php echo $currency; ?>)
                            </label>
                            <input type="number" id="debts" name="debts" min="0" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-dark-surface text-gray-900 dark:text-white"
                                   placeholder="الديون التي تدين بها">
                        </div>

                        <!-- Basic Needs -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                الحاجات الأساسية (<?php echo $currency; ?>)
                            </label>
                            <input type="number" id="basic_needs" name="basic_needs" min="0" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-dark-surface text-gray-900 dark:text-white"
                                   placeholder="المصاريف الأساسية للسنة">
                        </div>
                    </div>
                </div>

                <!-- Calculate Button -->
                <div class="flex justify-center pt-4">
                    <button type="button" onclick="calculateZakat()"
                            class="px-8 py-3 bg-primary hover:bg-primary/90 text-white font-semibold rounded-lg transition-colors flex items-center gap-2">
                        <span class="material-icons-round">calculate</span>
                        احسب الزكاة
                    </button>
                </div>
            </form>

            <!-- Results Section -->
            <div id="results" class="mt-8 space-y-4 hidden">
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="material-icons-round text-green-500">receipt</span>
                        نتائج الحساب
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Total Assets -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">إجمالي الأموال الزكوية</span>
                                <span id="totalAssets" class="font-bold text-gray-900 dark:text-white">0 <?php echo $currency; ?></span>
                            </div>
                        </div>

                        <!-- Net Assets -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">صافي الأموال بعد الطرح</span>
                                <span id="netAssets" class="font-bold text-gray-900 dark:text-white">0 <?php echo $currency; ?></span>
                            </div>
                        </div>

                        <!-- Nisab Check -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">النصاب الشرعي</span>
                                <span id="nisabAmount" class="font-bold text-gray-900 dark:text-white">0 <?php echo $currency; ?></span>
                            </div>
                        </div>

                        <!-- Zakat Amount -->
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-green-700 dark:text-green-300">مقدار الزكاة المستحقة</span>
                                <span id="zakatAmount" class="font-bold text-green-800 dark:text-green-200 text-xl">0 <?php echo $currency; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Message -->
                    <div id="statusMessage" class="mt-4 p-4 rounded-lg hidden">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Islamic Guidelines -->
        <div class="bg-white dark:bg-dark-surface rounded-xl shadow-sm border border-gray-200 dark:border-white/5 p-6 mt-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-icons-round text-blue-500">info</span>
                الإرشادات الإسلامية
            </h2>
            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                <p>• النصاب للذهب: 85 جرام ذهب خالص (قيمة 20 مثقال شرعي)</p>
                <p>• النصاب للفضة: 595 جرام فضة خالصة (قيمة 200 درهم شرعي)</p>
                <p>• الزكاة: 2.5% من صافي المال بعد طرح الديون والحاجات الأساسية</p>
                <p>• تجب الزكاة بعد مرور عام هجري كامل على المال</p>
                <p>• يجب إخراج الزكاة في مصارفها الشرعية الثمانية</p>
                <p>• الأموال الزكوية تشمل: النقود، الذهب، الفضة، البضائع التجارية، الزروع، الثمار، النعم</p>
            </div>
        </div>
    </div>
</div>

<script>
// Constants
const NISAB_GOLD_GRAMS = 85;
const NISAB_SILVER_GRAMS = 595;
const ZAKAT_RATE = 0.025; // 2.5%

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
        statusClass = 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200';
    } else {
        statusMessage = 'لم يبلغ مالك النصاب الشرعي (' + nisabValue.toFixed(2) + ' <?php echo $currency; ?>)، لا زكاة عليك هذا العام';
        statusClass = 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200';
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

// Auto-calculate on input change
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('input', function() {
        if (document.getElementById('results').classList.contains('hidden') === false) {
            calculateZakat();
        }
    });
});
</script>

<?php require_once 'src/footer.php'; ?>