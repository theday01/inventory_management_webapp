<?php
$page_title = 'المنتجات';
$current_page = 'products.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

// Fetch currency setting
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';
?>

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden">
    <div
        class="absolute top-0 right-[-10%] w-[500px] h-[500px] bg-primary/5 rounded-full blur-[120px] pointer-events-none">
    </div>

    <!-- Header -->
    <header
        class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <h2 class="text-xl font-bold text-white">إدارة المنتجات</h2>

        <div class="flex items-center gap-4">
            <button id="add-product-btn"
                class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">add</span>
                <span>منتج جديد</span>
            </button>
            <button id="manage-categories-btn"
                class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-xl font-bold shadow-lg flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">category</span>
                <span>إدارة الفئات</span>
            </button>
        </div>
    </header>

    <!-- Filters & Actions -->
    <div class="p-6 pb-0 flex flex-col md:flex-row gap-4 items-center justify-between relative z-10 shrink-0">
        <div class="flex items-center gap-4 w-full md:w-auto flex-1 max-w-2xl">
            <div class="relative flex-1">
                <span
                    class="material-icons-round absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">search</span>
                <input type="text" id="product-search-input" placeholder="بحث عن اسم المنتج، الباركود..."
                    class="w-full bg-dark/50 border border-white/10 text-white text-right pr-10 pl-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                <button id="scan-barcode-btn" class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400 hover:text-white">
                    <span class="material-icons-round">qr_code_scanner</span>
                </button>
            </div>

            <div class="relative min-w-[140px]">
                <select id="product-category-filter"
                    class="w-full appearance-none bg-dark/50 border border-white/10 text-white text-right pr-4 pl-8 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 cursor-pointer">
                    <option value="">جميع الفئات</option>
                </select>
                <span
                    class="material-icons-round absolute top-1/2 left-2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button class="p-2.5 bg-white/5 rounded-xl text-white hover:bg-white/10 transition-colors tooltip"
                title="عرض كجدول">
                <span class="material-icons-round">table_chart</span>
            </button>
            <button
                class="p-2.5 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-colors tooltip"
                title="عرض كبطاقات">
                <span class="material-icons-round">grid_view</span>
            </button>
        </div>
    </div>

    <!-- Products Table -->
    <div class="flex-1 overflow-auto p-6 relative z-10">
        <div
            class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl glass-panel overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-white/5 border-b border-white/5 text-right">
                        <th class="p-4 text-sm font-medium text-gray-300 w-16">
                            <input type="checkbox"
                                class="rounded border-gray-600 bg-dark text-primary focus:ring-primary">
                        </th>
                        <th class="p-4 text-sm font-medium text-gray-300">المنتج</th>
                        <th class="p-4 text-sm font-medium text-gray-300">الصورة</th>
                        <th class="p-4 text-sm font-medium text-gray-300">الفئة</th>
                        <th class="p-4 text-sm font-medium text-gray-300">السعر</th>
                        <th class="p-4 text-sm font-medium text-gray-300">الكمية</th>
                        <th class="p-4 text-sm font-medium text-gray-300">تفاصيل</th>
                        <th class="p-4 text-sm font-medium text-gray-300 w-20"></th>
                    </tr>
                </thead>
                <tbody id="products-table-body" class="divide-y divide-white/5">
                    <!-- Products will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- Add/Edit Product Modal -->
<div id="product-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-lg border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 id="product-modal-title" class="text-lg font-bold text-white">إضافة منتج جديد</h3>
            <button id="close-product-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form id="product-form" enctype="multipart/form-data">
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <input type="hidden" id="product-id" name="id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="product-name" class="block text-sm font-medium text-gray-300 mb-2">اسم المنتج</label>
                        <input type="text" id="product-name" name="name" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" required>
                    </div>
                    <div class="mb-4">
                        <label for="product-category" class="block text-sm font-medium text-gray-300 mb-2">الفئة</label>
                        <select id="product-category" name="category_id" class="w-full appearance-none bg-dark/50 border border-white/10 text-white text-right pr-4 pl-8 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 cursor-pointer">
                            <option value="">اختر فئة</option>
                            <!-- Categories will be loaded here -->
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="product-price" class="block text-sm font-medium text-gray-300 mb-2">السعر</label>
                        <input type="number" id="product-price" name="price" step="0.01" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" required>
                    </div>
                    <div class="mb-4">
                        <label for="product-quantity" class="block text-sm font-medium text-gray-300 mb-2">الكمية</label>
                        <input type="number" id="product-quantity" name="quantity" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" required>
                    </div>
                    <div class="mb-4 col-span-2">
                        <label for="product-barcode" class="block text-sm font-medium text-gray-300 mb-2">الباركود</label>
                        <input type="text" id="product-barcode" name="barcode" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                    </div>
                    <div class="mb-4 col-span-2">
                        <label for="product-image" class="block text-sm font-medium text-gray-300 mb-2">صورة المنتج</label>
                        <input type="file" id="product-image" name="image" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                    </div>
                </div>

                <div id="custom-fields-container" class="my-4 space-y-4">
                    <!-- Custom fields will be loaded here -->
                </div>
            </div>
            <div class="p-6 border-t border-white/5 flex justify-end gap-4">
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all">حفظ المنتج</button>
            </div>
        </form>
    </div>
</div>


<!-- Barcode Scanner Modal -->
<div id="barcode-scanner-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-md border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">مسح الباركود</h3>
            <button id="close-barcode-scanner-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="p-6">
            <video id="barcode-video" class="w-full h-auto rounded-lg"></video>
        </div>
    </div>
</div>

<!-- Product Details Modal -->
<div id="product-details-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-md border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">تفاصيل المنتج</h3>
            <button id="close-product-details-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div id="product-details-content" class="p-6">
            <!-- Details will be loaded here -->
        </div>
    </div>
</div>

<!-- Category Management Modal -->
<div id="category-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-lg border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">إدارة الفئات</h3>
            <button id="close-category-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="p-6 max-h-[70vh] overflow-y-auto">
            <form id="category-form">
                <input type="hidden" id="category-id" name="id">
                <div class="mb-4">
                    <label for="category-name" class="block text-sm font-medium text-gray-300 mb-2">اسم الفئة</label>
                    <input type="text" id="category-name" name="name"
                        class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50"
                        required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">حقول مخصصة (مفصولة بفاصلة)</label>
                    <input type="text" id="category-fields" name="fields"
                        class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50"
                        placeholder="مثال: الحجم, اللون, المادة">
                </div>
                <div class="flex justify-end gap-4">
                    <button type="button" id="cancel-category-edit" class="text-gray-400 hover:text-white px-4 py-2 rounded-xl transition-colors hidden">إلغاء التعديل</button>
                    <button type="submit"
                        class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all">حفظ الفئة</button>
                </div>
            </form>
            <hr class="border-white/10 my-6">
            <div>
                <h4 class="text-md font-bold text-white mb-4">الفئات الحالية</h4>
                <div id="category-list" class="space-y-2 max-h-80 overflow-y-auto">
                    <!-- Categories will be loaded here via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@zxing/library@latest/umd/index.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const manageCategoriesBtn = document.getElementById('manage-categories-btn');
        const categoryModal = document.getElementById('category-modal');
        const closeCategoryModalBtn = document.getElementById('close-category-modal');
        const categoryForm = document.getElementById('category-form');
        const categoryList = document.getElementById('category-list');
        const categoryIdInput = document.getElementById('category-id');
        const categoryNameInput = document.getElementById('category-name');
        const categoryFieldsInput = document.getElementById('category-fields');
        const cancelCategoryEditBtn = document.getElementById('cancel-category-edit');

        const addProductBtn = document.getElementById('add-product-btn');
        const productModal = document.getElementById('product-modal');
        const closeProductModalBtn = document.getElementById('close-product-modal');
        const productForm = document.getElementById('product-form');
        const productCategorySelect = document.getElementById('product-category');
        const customFieldsContainer = document.getElementById('custom-fields-container');
        const productsTableBody = document.getElementById('products-table-body');
        const searchInput = document.getElementById('product-search-input');
        const categoryFilter = document.getElementById('product-category-filter');

        loadProducts();
        loadCategoriesIntoFilter();
        searchInput.addEventListener('input', loadProducts);
        categoryFilter.addEventListener('change', loadProducts);

        async function loadProducts() {
            const searchQuery = searchInput.value;
            const categoryId = categoryFilter.value;

            try {
                const response = await fetch(`api.php?action=getProducts&search=${searchQuery}&category_id=${categoryId}`);
                const result = await response.json();
                if (result.success) {
                    displayProducts(result.data);
                }
            } catch (error) {
                console.error('Error loading products:', error);
            }
        }

        function displayProducts(products) {
            productsTableBody.innerHTML = '';
            if (products.length === 0) {
                productsTableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-gray-500">لا توجد منتجات لعرضها.</td></tr>';
                return;
            }
            products.forEach(product => {
                const productRow = document.createElement('tr');
                productRow.innerHTML = `
                    <td class="p-4 text-sm text-gray-300 w-16">
                        <input type="checkbox" class="rounded border-gray-600 bg-dark text-primary focus:ring-primary">
                    </td>
                    <td class="p-4 text-sm text-gray-300">${product.name}</td>
                    <td class="p-4 text-sm text-gray-300">
                        <img src="${product.image || 'src/img/default-product.png'}" alt="${product.name}" class="w-10 h-10 rounded-md object-cover">
                    </td>
                    <td class="p-4 text-sm text-gray-300">${product.category_name || 'N/A'}</td>
                    <td class="p-4 text-sm text-gray-300">${product.price} <?php echo $currency; ?></td>
                    <td class="p-4 text-sm text-gray-300">${product.quantity}</td>
                    <td class="p-4 text-sm text-gray-300">
                        <button class="view-details-btn p-1.5 text-gray-400 hover:text-primary transition-colors" data-id="${product.id}">
                            <span class="material-icons-round text-lg">visibility</span>
                        </button>
                    </td>
                    <td class="p-4 text-sm text-gray-300 w-20"></td>
                `;
                productsTableBody.appendChild(productRow);
            });
        }

        const productDetailsModal = document.getElementById('product-details-modal');
        const closeProductDetailsModalBtn = document.getElementById('close-product-details-modal');
        const productDetailsContent = document.getElementById('product-details-content');

        productsTableBody.addEventListener('click', async (e) => {
            if (e.target.closest('.view-details-btn')) {
                const btn = e.target.closest('.view-details-btn');
                const productId = btn.dataset.id;
                const product = await getProductDetails(productId);
                if (product) {
                    displayProductDetails(product);
                    productDetailsModal.classList.remove('hidden');
                }
            }
        });

        closeProductDetailsModalBtn.addEventListener('click', () => {
            productDetailsModal.classList.add('hidden');
        });

        async function getProductDetails(id) {
            try {
                const response = await fetch(`api.php?action=getProductDetails&id=${id}`);
                const result = await response.json();
                return result.success ? result.data : null;
            } catch (error) {
                console.error('Error fetching product details:', error);
                return null;
            }
        }

        function displayProductDetails(product) {
            let fieldsHtml = product.custom_fields.map(field => `
                <div class="flex justify-between">
                    <span class="font-medium text-gray-400">${field.field_name}:</span>
                    <span class="text-white">${field.value}</span>
                </div>
            `).join('');

            productDetailsContent.innerHTML = `
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="font-medium text-gray-400">الاسم:</span><span class="text-white">${product.name}</span></div>
                    <div class="flex justify-between"><span class="font-medium text-gray-400">الفئة:</span><span class="text-white">${product.category_name}</span></div>
                    <div class="flex justify-between"><span class="font-medium text-gray-400">السعر:</span><span class="text-white">${product.price} <?php echo $currency; ?></span></div>
                    <div class="flex justify-between"><span class="font-medium text-gray-400">الكمية:</span><span class="text-white">${product.quantity}</span></div>
                    <div class="flex justify-between"><span class="font-medium text-gray-400">الباركود:</span><span class="text-white">${product.barcode || 'N/A'}</span></div>
                    <hr class="border-white/10 my-3">
                    <h4 class="text-md font-bold text-white pt-2">حقول مخصصة</h4>
                    ${fieldsHtml || '<p class="text-gray-500">لا توجد حقول مخصصة.</p>'}
                </div>
            `;
        }


        addProductBtn.addEventListener('click', async () => {
            await loadCategoriesIntoSelect();
            productModal.classList.remove('hidden');
        });

        closeProductModalBtn.addEventListener('click', () => {
            productModal.classList.add('hidden');
        });

        productCategorySelect.addEventListener('change', async (e) => {
            const categoryId = e.target.value;
            if (categoryId) {
                const fields = await getCategoryFields(categoryId);
                displayCustomFields(fields);
            } else {
                customFieldsContainer.innerHTML = '';
            }
        });

        async function getCategoryFields(categoryId) {
            try {
                const response = await fetch(`api.php?action=getCategoryFields&category_id=${categoryId}`);
                const result = await response.json();
                return result.success ? result.data : [];
            } catch (error) {
                console.error('Error fetching category fields:', error);
                return [];
            }
        }

        function displayCustomFields(fields) {
            customFieldsContainer.innerHTML = '';
            fields.forEach(field => {
                const fieldEl = document.createElement('div');
                fieldEl.innerHTML = `
                    <label for="custom-field-${field.id}" class="block text-sm font-medium text-gray-300 mb-2">${field.field_name}</label>
                    <input type="text" id="custom-field-${field.id}" name="custom_fields[${field.id}]" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                `;
                customFieldsContainer.appendChild(fieldEl);
            });
        }
        
        productForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(productForm);
            
            // Append custom fields to formData
            const customFields = [];
            for (const [key, value] of formData.entries()) {
                if (key.startsWith('custom_fields')) {
                    const fieldId = key.match(/\[(\d+)\]/)[1];
                    customFields.push({ id: fieldId, value });
                }
            }
            formData.append('fields', JSON.stringify(customFields));

            try {
                const response = await fetch('api.php?action=addProduct', {
                    method: 'POST',
                    body: formData, // FormData handles the Content-Type
                });
                const result = await response.json();
                if (result.success) {
                    productModal.classList.add('hidden');
                    productForm.reset();
                    loadProducts(); // Refresh the product list
                    showToast('Product added successfully.');
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error adding product:', error);
            }
        });

        manageCategoriesBtn.addEventListener('click', () => {
            categoryModal.classList.remove('hidden');
            loadCategories();
        });

        closeCategoryModalBtn.addEventListener('click', () => {
            categoryModal.classList.add('hidden');
        });

        async function loadCategories() {
            try {
                const response = await fetch('api.php?action=getCategories');
                const result = await response.json();
                if (result.success) {
                    displayCategories(result.data);
                    return result.data;
                }
            } catch (error) {
                console.error('Error loading categories:', error);
            }
            return [];
        }

        async function loadCategoriesIntoSelect() {
            const categories = await loadCategories();
            productCategorySelect.innerHTML = '<option value="">اختر فئة</option>';
            if(categories) {
                categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    productCategorySelect.appendChild(option);
                });
            }
            productCategorySelect.setAttribute('data-loaded', 'true');
        }

        async function loadCategoriesIntoFilter() {
            const categories = await loadCategories();
            categoryFilter.innerHTML = '<option value="">جميع الفئات</option>';
            if(categories) {
                categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    categoryFilter.appendChild(option);
                });
            }
        }

        function displayCategories(categories) {
            categoryList.innerHTML = '';
            if (categories.length === 0) {
                categoryList.innerHTML = '<p class="text-gray-500">لا توجد فئات حاليًا.</p>';
                return;
            }
            categories.forEach(category => {
                const categoryEl = document.createElement('div');
                categoryEl.className = 'flex justify-between items-center bg-dark/50 p-3 rounded-lg';
                categoryEl.innerHTML = `
                    <div>
                        <span class="font-bold text-white">${category.name}</span>
                        <p class="text-sm text-gray-400">${category.fields || 'لا توجد حقول مخصصة'}</p>
                    </div>
                    <div class="flex gap-2">
                        <button class="edit-category-btn p-1.5 text-gray-400 hover:text-primary transition-colors" data-id="${category.id}" data-name="${category.name}" data-fields="${category.fields || ''}">
                            <span class="material-icons-round text-lg">edit</span>
                        </button>
                        <button class="delete-category-btn p-1.5 text-gray-400 hover:text-red-500 transition-colors" data-id="${category.id}">
                            <span class="material-icons-round text-lg">delete</span>
                        </button>
                    </div>
                `;
                categoryList.appendChild(categoryEl);
            });
        }

        categoryForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const id = categoryIdInput.value;
            const name = categoryNameInput.value;
            const fields = categoryFieldsInput.value.split(/,|،/).map(s => s.trim()).filter(Boolean);

            const url = id ? 'api.php?action=updateCategory' : 'api.php?action=addCategory';
            const method = 'POST';

            try {
                const response = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, name, fields }),
                });
                const result = await response.json();
                if (result.success) {
                    resetForm();
                    loadCategories();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error submitting form:', error);
            }
        });

        categoryList.addEventListener('click', function (e) {
            if (e.target.closest('.edit-category-btn')) {
                const btn = e.target.closest('.edit-category-btn');
                categoryIdInput.value = btn.dataset.id;
                categoryNameInput.value = btn.dataset.name;
                categoryFieldsInput.value = btn.dataset.fields;
                cancelCategoryEditBtn.classList.remove('hidden');
            }

            if (e.target.closest('.delete-category-btn')) {
                const btn = e.target.closest('.delete-category-btn');
                const id = btn.dataset.id;
                if (confirm('هل أنت متأكد من حذف هذه الفئة؟')) {
                    deleteCategory(id);
                }
            }
        });
        
        cancelCategoryEditBtn.addEventListener('click', resetForm);

        function resetForm() {
            categoryForm.reset();
            categoryIdInput.value = '';
            cancelCategoryEditBtn.classList.add('hidden');
        }

        async function deleteCategory(id) {
            try {
                const response = await fetch('api.php?action=deleteCategory', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id }),
                });
                const result = await response.json();
                if (result.success) {
                    loadCategories();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error deleting category:', error);
            }
        }

        const scanBarcodeBtn = document.getElementById('scan-barcode-btn');
        const barcodeScannerModal = document.getElementById('barcode-scanner-modal');
        const closeBarcodeScannerModalBtn = document.getElementById('close-barcode-scanner-modal');
        const videoElement = document.getElementById('barcode-video');
        let codeReader;

        scanBarcodeBtn.addEventListener('click', () => {
            barcodeScannerModal.classList.remove('hidden');
            startBarcodeScanner();
        });

        closeBarcodeScannerModalBtn.addEventListener('click', () => {
            barcodeScannerModal.classList.add('hidden');
            stopBarcodeScanner();
        });

        function startBarcodeScanner() {
            codeReader = new ZXing.BrowserMultiFormatReader();
            codeReader.listVideoInputDevices()
                .then((videoInputDevices) => {
                    if (videoInputDevices.length === 0) {
                        alert('No camera found');
                        return;
                    }
                    const firstDeviceId = videoInputDevices[0].deviceId;
                    codeReader.decodeFromVideoDevice(firstDeviceId, 'barcode-video', (result, err) => {
                        if (result) {
                            searchInput.value = result.text;
                            stopBarcodeScanner();
                            barcodeScannerModal.classList.add('hidden');
                            loadProducts();
                        }
                        if (err && !(err instanceof ZXing.NotFoundException)) {
                            console.error(err);
                        }
                    });
                })
                .catch((err) => {
                    console.error(err);
                });
        }

        function stopBarcodeScanner() {
            if (codeReader) {
                codeReader.reset();
            }
        }
    });
</script>

<?php require_once 'src/footer.php'; ?>
