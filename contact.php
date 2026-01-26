<?php
$page_title = 'تواصل معنا';
$current_page = 'contact.php';
require_once 'session.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';
?>

<main class="flex-1 flex flex-col relative overflow-hidden bg-dark">
    <!-- Background Blobs -->
    <div class="absolute top-[-5%] right-[-5%] w-[400px] h-[400px] bg-primary/5 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[300px] h-[300px] bg-accent/5 rounded-full blur-[80px] pointer-events-none"></div>

    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-20 shrink-0">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="material-icons-round text-primary">support_agent</span>
            الدعم الفني والتواصل
        </h2>
    </header>

    <div class="flex-1 flex overflow-hidden relative z-10">
        <?php require_once 'src/settings_sidebar.php'; ?>

        <div id="settings-content-area" class="flex-1 overflow-y-auto p-8 custom-scrollbar">
            <div class="max-w-4xl mx-auto">
                
                <!-- Contact Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Email -->
                    <a href="mailto:support@eagleshadow.technology" class="group bg-dark-surface/60 hover:bg-dark-surface/80 backdrop-blur-md border border-white/5 hover:border-primary/30 rounded-2xl p-6 flex flex-col items-center text-center transition-all stat-card hover:-translate-y-1">
                        <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                            <span class="material-icons-round text-primary text-3xl">email</span>
                        </div>
                        <h3 class="text-white font-bold text-lg">البريد الإلكتروني</h3>
                        <p class="text-sm text-gray-400 mt-1">support@eagleshadow.technology</p>
                    </a>
                    <!-- WhatsApp -->
                    <a href="https://wa.me/212700979284" target="_blank" class="group bg-dark-surface/60 hover:bg-dark-surface/80 backdrop-blur-md border border-white/5 hover:border-green-500/30 rounded-2xl p-6 flex flex-col items-center text-center transition-all stat-card hover:-translate-y-1">
                        <div class="w-16 h-16 rounded-full bg-green-500/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                            <span class="material-icons-round text-green-500 text-3xl">call</span>
                        </div>
                        <h3 class="text-white font-bold text-lg">واتساب</h3>
                        <p class="text-sm text-gray-400 mt-1" dir="ltr">+212 700-979284</p>
                    </a>
                    <!-- Website -->
                    <a href="https://eagleshadow.technology" target="_blank" class="group bg-dark-surface/60 hover:bg-dark-surface/80 backdrop-blur-md border border-white/5 hover:border-accent/30 rounded-2xl p-6 flex flex-col items-center text-center transition-all stat-card hover:-translate-y-1">
                        <div class="w-16 h-16 rounded-full bg-accent/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                            <span class="material-icons-round text-accent text-3xl">link</span>
                        </div>
                        <h3 class="text-white font-bold text-lg">الموقع الإلكتروني</h3>
                        <p class="text-sm text-gray-400 mt-1">eagleshadow.technology</p>
                    </a>
                </div>

                <!-- Contact Form -->
                <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-3xl overflow-hidden glass-panel">
                    <div class="p-8 border-b border-white/5 bg-white/5">
                        <h2 class="text-xl font-bold text-white">أرسل رسالة مباشرة</h2>
                        <p class="text-sm text-gray-400 mt-1">نحن هنا لمساعدتك. املأ النموذج أدناه وسنعاود الاتصال بك في أقرب وقت ممكن (أقل من 24 ساعة)</p>
                    </div>

                    <form action="https://formspree.io/f/mnjpnrrr" id="contact-form" method="POST" class="p-8 space-y-6" onsubmit="handleFormSubmit(event)">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">الاسم الكامل</label>
                                <input type="text" id="name" name="name" required class="w-full bg-dark border border-white/10 text-white rounded-lg px-4 py-3 focus:outline-none focus:border-primary transition-colors" placeholder="مثال: حمزة سعدي">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">البريد الإلكتروني</label>
                                <input type="email" id="email" name="email" required class="w-full bg-dark border border-white/10 text-white rounded-lg px-4 py-3 focus:outline-none focus:border-primary transition-colors" placeholder="متال: support@eagleshadow.technology">
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-300 mb-2">الموضوع</label>
                            <input type="text" id="subject" name="subject" required class="w-full bg-dark border border-white/10 text-white rounded-lg px-4 py-3 focus:outline-none focus:border-primary transition-colors" placeholder="مثال: استفسار بخصوص ميزة التقارير">
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-300 mb-2">الرسالة</label>
                            <textarea id="message" name="message" rows="6" required class="w-full bg-dark border border-white/10 text-white rounded-lg px-4 py-3 focus:outline-none focus:border-primary transition-colors resize-y" placeholder="اكتب تفاصيل رسالتك هنا..."></textarea>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" id="submit-btn" class="bg-primary hover:bg-primary-hover text-white font-bold py-3 px-8 rounded-xl flex items-center gap-2 transition-all duration-300 transform hover:-translate-y-1 shadow-lg shadow-primary/20">
                                <span id="submit-text">إرسال الرسالة</span>
                                <span id="submit-spinner" class="material-icons-round animate-spin hidden">sync</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Developer Logo -->
                <div class="mt-8 text-center">
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 inline-block">
                        <a href="https://eagleshadow.technology" target="_blank" class="block">
                            <img src="src/support/logo.png" alt="شعار مطور الموقع" class="h-16 w-auto mx-auto mb-4 opacity-80 hover:opacity-100 transition-opacity duration-300 cursor-pointer">
                        </a>
                        <p class="text-sm text-gray-400">تم تطوير هذا النظام بواسطة</p>
                        <p class="text-white font-semibold">EagleShadow Technology</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Handle form submission with Formspree
function handleFormSubmit(event) {
    event.preventDefault();
    
    const form = document.getElementById('contact-form');
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');
    
    // Show loading state
    submitBtn.disabled = true;
    submitText.textContent = 'جاري الإرسال...';
    submitSpinner.classList.remove('hidden');
    
    // Get form data
    const formData = new FormData(form);
    
    // Submit to Formspree
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        }
        throw new Error('فشل الإرسال');
    })
    .then(data => {
        // Success
        Swal.fire({
            icon: 'success',
            title: 'تم الإرسال بنجاح!',
            text: 'شكراً لك على رسالتك. سنعاود الاتصال بك قريباً.',
            background: '#1F2937',
            color: '#ffffff',
            confirmButtonColor: '#3B82F6',
            confirmButtonText: 'حسناً'
        });
        form.reset(); // Clear the form
    })
    .catch(error => {
        // Error
        Swal.fire({
            icon: 'error',
            title: 'فشل الإرسال',
            text: 'حدث خطأ أثناء إرسال الرسالة. الرجاء محاولة مرة أخرى.',
            background: '#1F2937',
            color: '#ffffff',
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'حاول مرة أخرى'
        });
    })
    .finally(() => {
        // Reset button
        submitBtn.disabled = false;
        submitText.textContent = 'إرسال الرسالة';
        submitSpinner.classList.add('hidden');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Optional: You can add additional form validations here
    const form = document.getElementById('contact-form');
    
    // Add real-time validation feedback
    const inputs = form.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() === '' && this.required) {
                this.classList.add('border-red-500');
                this.classList.remove('border-white/10');
            } else {
                this.classList.remove('border-red-500');
                this.classList.add('border-white/10');
            }
        });
    });
});
</script>

<div id="loading-overlay" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[9999] hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl p-12 border border-white/10 flex flex-col items-center gap-6">
        <div class="relative w-20 h-20">
            <div class="absolute inset-0 border-4 border-transparent border-t-primary border-r-primary rounded-full animate-spin"></div>
            <div class="absolute inset-2 border-4 border-transparent border-b-primary/50 rounded-full animate-spin" style="animation-direction: reverse;"></div>
        </div>
        <div class="text-center">
            <h3 class="text-lg font-bold text-white mb-2">جاري التحميل...</h3>
            <p id="loading-message" class="text-sm text-gray-400">يرجى الانتظار قليلاً</p>
        </div>
    </div>
</div>

<script>
    // دوال إدارة شاشة التحميل
    function showLoadingOverlay(message = 'جاري معالجة البيانات...') {
        const loadingOverlay = document.getElementById('loading-overlay');
        const loadingMessage = document.getElementById('loading-message');
        loadingMessage.textContent = message;
        loadingOverlay.classList.remove('hidden');
    }

    function hideLoadingOverlay() {
        const loadingOverlay = document.getElementById('loading-overlay');
        loadingOverlay.classList.add('hidden');
    }
</script>

<style>
.stat-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.stat-card:hover {
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    border-color: rgba(59, 130, 246, 0.4);
}
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.02);
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>

<?php require_once 'src/footer.php'; ?>
