<?php
require_once 'src/language.php';
$page_title = __('contact_page_title');
$current_page = 'contact.php';
require_once 'session.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';
?>

<main class="flex-1 flex flex-col relative overflow-hidden bg-dark">
    <!-- Background Blobs -->
    <div class="absolute top-[-5%] right-[-5%] w-[400px] h-[400px] bg-primary/5 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[300px] h-[300px] bg-accent/5 rounded-full blur-[80px] pointer-events-none"></div>

    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-4 lg:px-8 relative z-20 shrink-0">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="material-icons-round text-primary">support_agent</span>
            <?= __('support_and_contact') ?>
        </h2>
    </header>

    <div class="flex-1 flex flex-col lg:flex-row overflow-hidden relative z-10">
        <?php require_once 'src/settings_sidebar.php'; ?>

        <div id="settings-content-area" class="flex-1 overflow-y-auto p-4 lg:p-8 custom-scrollbar">
            <div class="max-w-4xl mx-auto">
                
                <!-- Contact Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Email -->
                    <a href="mailto:support@eagleshadow.technology" class="group bg-dark-surface/60 hover:bg-dark-surface/80 backdrop-blur-md border border-white/5 hover:border-primary/30 rounded-2xl p-6 flex flex-col items-center text-center transition-all stat-card hover:-translate-y-1">
                        <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                            <span class="material-icons-round text-primary text-3xl">email</span>
                        </div>
                        <h3 class="text-white font-bold text-lg"><?= __('email_title') ?></h3>
                        <p class="text-sm text-gray-400 mt-1">support@eagleshadow.technology</p>
                    </a>
                    <!-- WhatsApp -->
                    <a href="https://wa.me/212700979284" target="_blank" class="group bg-dark-surface/60 hover:bg-dark-surface/80 backdrop-blur-md border border-white/5 hover:border-green-500/30 rounded-2xl p-6 flex flex-col items-center text-center transition-all stat-card hover:-translate-y-1">
                        <div class="w-16 h-16 rounded-full bg-green-500/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                            <span class="material-icons-round text-green-500 text-3xl">call</span>
                        </div>
                        <h3 class="text-white font-bold text-lg"><?= __('whatsapp') ?></h3>
                        <p class="text-sm text-gray-400 mt-1" dir="ltr">+212 700-979284</p>
                    </a>
                    <!-- Website -->
                    <a href="https://eagleshadow.technology" target="_blank" class="group bg-dark-surface/60 hover:bg-dark-surface/80 backdrop-blur-md border border-white/5 hover:border-accent/30 rounded-2xl p-6 flex flex-col items-center text-center transition-all stat-card hover:-translate-y-1">
                        <div class="w-16 h-16 rounded-full bg-accent/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                            <span class="material-icons-round text-accent text-3xl">link</span>
                        </div>
                        <h3 class="text-white font-bold text-lg"><?= __('website') ?></h3>
                        <p class="text-sm text-gray-400 mt-1">eagleshadow.technology</p>
                    </a>
                </div>

                <!-- Contact Form -->
                <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-3xl overflow-hidden glass-panel">
                    <div class="p-8 border-b border-white/5 bg-white/5">
                        <h2 class="text-xl font-bold text-white"><?= __('send_message_title') ?></h2>
                        <p class="text-sm text-gray-400 mt-1"><?= __('contact_form_desc') ?></p>
                    </div>

                    <form action="https://formspree.io/f/mnjpnrrr" id="contact-form" method="POST" class="p-8 space-y-6">
                        <!-- Fallback Redirect -->
                        <input type="hidden" name="_next" value="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>?status=sent">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-300 mb-2"><?= __('full_name') ?></label>
                                <input type="text" id="name" name="name" required class="w-full bg-dark border border-white/10 text-white rounded-lg px-4 py-3 focus:outline-none focus:border-primary transition-colors" placeholder="<?= __('name_placeholder_example') ?>">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-300 mb-2"><?= __('email_title') ?></label>
                                <input type="email" id="email" name="email" required class="w-full bg-dark border border-white/10 text-white rounded-lg px-4 py-3 focus:outline-none focus:border-primary transition-colors" placeholder="<?= __('email_placeholder_example') ?>">
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-300 mb-2"><?= __('subject') ?></label>
                            <input type="text" id="subject" name="subject" required class="w-full bg-dark border border-white/10 text-white rounded-lg px-4 py-3 focus:outline-none focus:border-primary transition-colors" placeholder="<?= __('subject_placeholder_example') ?>">
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-300 mb-2"><?= __('message') ?></label>
                            <textarea id="message" name="message" rows="6" required class="w-full bg-dark border border-white/10 text-white rounded-lg px-4 py-3 focus:outline-none focus:border-primary transition-colors resize-y" placeholder="<?= __('message_placeholder') ?>"></textarea>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" id="submit-btn" class="bg-primary hover:bg-primary-hover text-white font-bold py-3 px-8 rounded-xl flex items-center gap-2 transition-all duration-300 transform hover:-translate-y-1 shadow-lg shadow-primary/20">
                                <span id="submit-text"><?= __('send_message_btn') ?></span>
                                <span id="submit-spinner" class="material-icons-round animate-spin hidden">sync</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Developer Logo -->
                <div class="mt-8 text-center">
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 inline-block">
                        <a href="https://eagleshadow.technology" target="_blank" class="block">
                            <img src="src/support/logo.png" alt="<?= __('developer_logo_alt') ?>" class="h-25 w-auto mx-auto mb-4 opacity-80 hover:opacity-100 transition-opacity duration-300 cursor-pointer">
                        </a>
                        <p class="text-sm text-gray-400"><?= __('developed_by') ?></p>
                        <p class="text-white font-semibold">EagleShadow Technology</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle form submission with Formspree
    const form = document.getElementById('contact-form');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            // Prevent standard form submission
            event.preventDefault();
            
            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');
            const submitSpinner = document.getElementById('submit-spinner');
            
            // Show loading state
            if (submitBtn) submitBtn.disabled = true;
            if (submitText) submitText.textContent = '<?= __('sending') ?>';
            if (submitSpinner) submitSpinner.classList.remove('hidden');
            
            // Get form data
            const formData = new FormData(form);
            
            // Submit to Formspree using fetch (AJAX)
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
                // Success - Show custom message
                Swal.fire({
                    icon: 'success',
                    title: '<?= __('message_sent_success_title') ?>',
                    text: '<?= __('message_sent_success_text') ?>',
                    background: '#1F2937',
                    color: '#ffffff',
                    confirmButtonColor: '#3B82F6',
                    confirmButtonText: '<?= __('ok') ?>'
                });
                form.reset(); // Clear the form
            })
            .catch(error => {
                // Error - Show custom message
                Swal.fire({
                    icon: 'error',
                    title: '<?= __('message_sent_fail_title') ?>',
                    text: '<?= __('message_sent_fail_text') ?>',
                    background: '#1F2937',
                    color: '#ffffff',
                    confirmButtonColor: '#EF4444',
                    confirmButtonText: '<?= __('try_again') ?>'
                });
            })
            .finally(() => {
                // Reset button state
                if (submitBtn) submitBtn.disabled = false;
                if (submitText) submitText.textContent = '<?= __('send_message_btn') ?>';
                if (submitSpinner) submitSpinner.classList.add('hidden');
            });
        });

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
    }

    // Handle status=sent parameter (fallback if redirection occurs)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'sent') {
        Swal.fire({
            icon: 'success',
            title: '<?= __('message_sent_success_title') ?>',
            text: '<?= __('message_sent_success_text') ?>',
            background: '#1F2937',
            color: '#ffffff',
            confirmButtonColor: '#3B82F6',
            confirmButtonText: '<?= __('ok') ?>'
        });
        // Clean URL
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
});
</script>

<div id="loading-overlay" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[9999] hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl p-12 border border-white/10 flex flex-col items-center gap-6">
        <div class="relative w-20 h-20">
            <div class="absolute inset-0 border-4 border-transparent border-t-primary border-r-primary rounded-full animate-spin"></div>
            <div class="absolute inset-2 border-4 border-transparent border-b-primary/50 rounded-full animate-spin" style="animation-direction: reverse;"></div>
        </div>
        <div class="text-center">
            <h3 class="text-lg font-bold text-white mb-2"><?= __('loading') ?></h3>
            <p id="loading-message" class="text-sm text-gray-400"><?= __('please_wait') ?></p>
        </div>
    </div>
</div>

<script>
    // دوال إدارة شاشة التحميل
    function showLoadingOverlay(message = '<?= __('processing_data_msg') ?>') {
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
