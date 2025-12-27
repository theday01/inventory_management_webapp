<?php
$page_title = 'المستخدمين - Smart Shop';
$current_page = 'users.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

// Check if the user is an admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Handle add user form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the database
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed_password, $role);

    if ($stmt->execute()) {
        $success_message = "تم إضافة المستخدم بنجاح";
    } else {
        // Handle insertion error
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all users
$users = [];
$sql = "SELECT id, username, role FROM users";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
$conn->close();
?>

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden">
    <div
        class="absolute top-0 left-0 w-[600px] h-[600px] bg-primary/5 rounded-full blur-[120px] pointer-events-none">
    </div>

    <!-- Header -->
    <header
        class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <h2 class="text-xl font-bold text-white">إدارة المستخدمين</h2>
        <div class="flex items-center gap-4">
            <button
                class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all hover:-translate-y-0.5">
                حفظ التغييرات
            </button>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto p-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Settings Menu -->
            <div class="lg:col-span-1">
                <div
                    class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl glass-panel overflow-hidden">
                    <nav class="flex flex-col">
                        <a href="settings.php"
                            class="px-6 py-4 flex items-center gap-3 text-gray-400 hover:text-white hover:bg-white/5 transition-colors border-r-2 border-transparent">
                            <span class="material-icons-round">store</span>
                            <span class="font-bold">إعدادات المتجر</span>
                        </a>
                        <a href="invoices.php"
                            class="px-6 py-4 flex items-center gap-3 text-gray-400 hover:text-white hover:bg-white/5 transition-colors border-r-2 border-transparent">
                            <span class="material-icons-round">receipt</span>
                            <span class="font-bold">الفواتير والضريبة</span>
                        </a>
                        <a href="users.php"
                            class="px-6 py-4 flex items-center gap-3 bg-primary/10 text-primary border-r-2 border-primary">
                            <span class="material-icons-round">group</span>
                            <span class="font-bold">المستخدمين</span>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Users Content -->
            <div class="lg:col-span-2 space-y-6">
                <section
                    class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                    <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                        <span class="material-icons-round text-primary">group</span>
                        قائمة المستخدمين
                    </h3>

                    <?php if (!empty($success_message)): ?>
                        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-right">
                            <thead>
                                <tr class="border-b border-white/10">
                                    <th class="p-4 text-sm font-bold text-gray-400">الاسم</th>
                                    <th class="p-4 text-sm font-bold text-gray-400">الدور</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-gray-500">
                                            No data to display at this time.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td class="p-4"><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td class="p-4"><?php echo htmlspecialchars($user['role']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                     <div class="mt-6">
                         <button id="addUserBtn" class="w-full bg-primary/10 hover:bg-primary/20 text-primary px-6 py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
                            <span class="material-icons-round">add_circle</span>
                            إضافة مستخدم جديد
                        </button>
                    </div>
                </section>
            </div>
        </div>
    </div>
</main>

<!-- Add User Modal -->
<div id="addUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-dark-surface rounded-lg p-8 w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6">إضافة مستخدم جديد</h2>
        <form action="users.php" method="POST">
            <div class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-300 mb-2">اسم المستخدم</label>
                    <input type="text" id="username" name="username" class="w-full bg-dark/50 border border-dark-border text-white placeholder-gray-500 rounded-xl px-4 py-3" required>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">كلمة المرور</label>
                    <input type="password" id="password" name="password" class="w-full bg-dark/50 border border-dark-border text-white placeholder-gray-500 rounded-xl px-4 py-3" required>
                </div>
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-300 mb-2">الدور</label>
                    <select id="role" name="role" class="w-full bg-dark/50 border border-dark-border text-white rounded-xl px-4 py-3">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-4">
                <button type="button" id="closeModalBtn" class="px-6 py-2 rounded-xl text-gray-300 hover:bg-white/5">إلغاء</button>
                <button type="submit" name="add_user" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold">إضافة</button>
            </div>
        </form>
    </div>
</div>

<script>
    const addUserBtn = document.getElementById('addUserBtn');
    const addUserModal = document.getElementById('addUserModal');
    const closeModalBtn = document.getElementById('closeModalBtn');

    addUserBtn.addEventListener('click', () => {
        addUserModal.classList.remove('hidden');
    });

    closeModalBtn.addEventListener('click', () => {
        addUserModal.classList.add('hidden');
    });

    window.addEventListener('click', (event) => {
        if (event.target === addUserModal) {
            addUserModal.classList.add('hidden');
        }
    });
</script>

<?php require_once 'src/footer.php'; ?>
