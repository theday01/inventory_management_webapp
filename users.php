<?php
require_once 'session.php';
require_once 'db.php';
$current_page = 'users.php';
$current_page = 'settings.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// معالجة إضافة مستخدم
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed_password, $role);

    if ($stmt->execute()) {
        // إنشاء إشعار بإضافة المستخدم الجديد
        $created_by = $_SESSION['username'];
        $notification_message = "قام المدير '{$created_by}' بإنشاء حساب جديد باسم '{$username}' بصلاحيات '{$role}'";
        $notification_type = "user_registration";
        
        $notif_stmt = $conn->prepare("INSERT INTO notifications (message, type, status) VALUES (?, ?, 'unread')");
        $notif_stmt->bind_param("ss", $notification_message, $notification_type);
        $notif_stmt->execute();
        $notif_stmt->close();

        $stmt->close();
        header("Location: users.php?success=" . urlencode("تم إضافة المستخدم بنجاح"));
        exit();
    } else {
        $stmt->close();
        header("Location: users.php?error=" . urlencode("فشل في إضافة المستخدم"));
        exit();
    }
}

// معالجة تعديل مستخدم
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    
    // التحقق من أن المستخدم ليس آخر admin
    $check_admin = $conn->query("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
    $admin_count = $check_admin->fetch_assoc()['admin_count'];
    
    // الحصول على الدور الحالي للمستخدم
    $stmt_check = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $current_role = $stmt_check->get_result()->fetch_assoc()['role'];
    $stmt_check->close();
    
    // إذا كان آخر admin ويحاول تغيير دوره
    if ($current_role === 'admin' && $admin_count == 1 && $role !== 'admin') {
        header("Location: users.php?error=" . urlencode("لا يمكن تغيير دور المدير في النظام"));
        exit();
    }
    
    // إذا تم إدخال كلمة مرور جديدة
    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $hashed_password, $role, $user_id);
    } else {
        // تحديث بدون تغيير كلمة المرور
        $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $role, $user_id);
    }

    if ($stmt->execute()) {
        // إنشاء إشعار تحديث المستخدم
        $updated_by = $_SESSION['username'];
        $notification_message = "قام المدير '{$updated_by}' بتحديث حساب المستخدم '{$username}' إلى دور '{$role}'";
        $notification_type = "user_update";
        
        $notif_stmt = $conn->prepare("INSERT INTO notifications (message, type, status) VALUES (?, ?, 'unread')");
        $notif_stmt->bind_param("ss", $notification_message, $notification_type);
        $notif_stmt->execute();
        $notif_stmt->close();

        $stmt->close();
        header("Location: users.php?success=" . urlencode("تم تحديث المستخدم بنجاح"));
        exit();
    } else {
        $stmt->close();
        header("Location: users.php?error=" . urlencode("فشل في تحديث المستخدم"));
        exit();
    }
}

// معالجة حذف مستخدم
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    
    // منع حذف الحساب الحالي
    if ($user_id == $_SESSION['id']) {
        header("Location: users.php?error=" . urlencode("لا يمكنك حذف حسابك الخاص"));
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // إنشاء إشعار حذف المستخدم
        $deleted_by = $_SESSION['username'];
        $notification_message = "قام المدير '{$deleted_by}' بحذف حساب المستخدم '{$username}'";
        $notification_type = "user_deletion";
        
        $notif_stmt = $conn->prepare("INSERT INTO notifications (message, type, status) VALUES (?, ?, 'unread')");
        $notif_stmt->bind_param("ss", $notification_message, $notification_type);
        $notif_stmt->execute();
        $notif_stmt->close();

        $stmt->close();
        header("Location: users.php?success=" . urlencode("تم حذف المستخدم بنجاح"));
        exit();
    } else {
        $stmt->close();
        header("Location: users.php?error=" . urlencode("فشل في حذف المستخدم"));
        exit();
    }
}

// جلب بيانات المستخدمين
$users = [];
$sql = "SELECT id, username, role FROM users";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// حساب عدد المدراء
$admin_count_query = $conn->query("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
$admin_count = $admin_count_query->fetch_assoc()['admin_count'];

// الآن يمكن استدعاء header و sidebar بعد معالجة جميع POST requests
$page_title = 'المستخدمين';
require_once 'src/header.php';
require_once 'src/sidebar.php';
?>

<main class="flex-1 flex flex-col relative overflow-hidden bg-dark">
    <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-blue-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-20 shrink-0">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="material-icons-round text-primary">settings_suggest</span>
            إدارة المستخدمين والصلاحيات
        </h2>
    </header>

    <div class="flex-1 flex overflow-hidden relative z-10">

        <aside class="w-64 bg-dark-surface/30 backdrop-blur-md border-l border-white/5 flex flex-col overflow-y-auto shrink-0">
            <div class="p-4 space-y-2">
                <div class="px-4 py-2 text-xs font-bold text-gray-500 uppercase tracking-wider">
                    الإعدادات العامة
                </div>

                <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all group">
                    <span class="material-icons-round text-[20px] group-hover:text-primary transition-colors">store</span>
                    <span class="font-medium text-sm">إعدادات المتجر</span>
                </a>

                <a href="users.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary border border-primary/20 transition-all shadow-lg shadow-primary/5">
                    <span class="material-icons-round text-[20px]">people</span>
                    <span class="font-bold text-sm">المستخدمين</span>
                </a>

                <div class="my-2 border-t border-white/5"></div>

                <div class="px-4 py-2 text-xs font-bold text-gray-500 uppercase tracking-wider">
                    النظام
                </div>

                <a href="version.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all group">
                    <span class="material-icons-round text-[20px] group-hover:text-primary transition-colors">info</span>
                    <span class="font-medium text-sm">إصدار النظام</span>
                </a>
                 
                <a href="license.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all group">
                    <span class="material-icons-round text-[20px] group-hover:text-yellow-500 transition-colors">verified_user</span>
                    <span class="font-medium text-sm">الترخيص</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
            <div class="max-w-4xl mx-auto space-y-6">
                
                <section class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="material-icons-round text-primary">group</span>
                            قائمة المستخدمين (<?php echo count($users); ?>)
                        </h3>
                        <button id="addUserBtn" class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-xl font-bold text-sm flex items-center gap-2 shadow-lg shadow-primary/20 transition-all">
                            <span class="material-icons-round text-sm">add</span>
                            إضافة جديد
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-white/5">
                        <table class="w-full text-right">
                            <thead class="bg-white/5">
                                <tr>
                                    <th class="p-4 text-sm font-bold text-gray-400">الاسم</th>
                                    <th class="p-4 text-sm font-bold text-gray-400">الدور</th>
                                    <th class="p-4 text-sm font-bold text-gray-400 text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <?php foreach ($users as $user): ?>
                                    <tr class="hover:bg-white/5 transition-colors">
                                        <td class="p-4 text-sm text-white font-medium">
                                            <?php echo htmlspecialchars($user['username']); ?>
                                            <?php if ($user['id'] == $_SESSION['id']): ?>
                                                <span class="text-xs text-primary mr-2 bg-primary/10 px-2 py-0.5 rounded">(أنت)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 text-sm">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $user['role'] == 'admin' ? 'bg-primary/20 text-primary' : 'bg-gray-500/20 text-gray-400'; ?>">
                                                <?php echo htmlspecialchars($user['role']); ?>
                                            </span>
                                        </td>
                                        <td class="p-4 text-sm text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button onclick="openEditModal(<?php echo $user['id']; ?>, '<?php echo $user['username']; ?>', '<?php echo $user['role']; ?>', <?php echo $admin_count; ?>)" 
                                                    class="p-2 text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-all" title="تعديل">
                                                    <span class="material-icons-round text-lg">edit</span>
                                                </button>
                                                <?php if ($user['id'] != $_SESSION['id']): ?>
                                                    <button onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo $user['username']; ?>')" 
                                                        class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-all" title="حذف">
                                                        <span class="material-icons-round text-lg">delete</span>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>
        </div>
    </div>
</main>
<!-- Add User Modal -->
<div id="addUserModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-md border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">إضافة مستخدم جديد</h3>
            <button id="closeAddModalBtn" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form action="users.php" method="POST">
            <div class="p-6 space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-300 mb-2">اسم المستخدم</label>
                    <input type="text" id="username" name="username" 
                        class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" 
                        required>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">كلمة المرور</label>
                    <input type="password" id="password" name="password" 
                        class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" 
                        required>
                </div>
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-300 mb-2">الدور</label>
                    <select id="role" name="role" 
                        class="w-full appearance-none bg-dark/50 border border-white/10 text-white text-right pr-4 pl-8 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 cursor-pointer">
                        <option value="user">مستخدم</option>
                        <option value="admin">مدير</option>
                    </select>
                </div>
            </div>
            <div class="p-6 border-t border-white/5 flex justify-end gap-4">
                <button type="button" onclick="closeAddModal()" 
                    class="px-6 py-2 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                    إلغاء
                </button>
                <button type="submit" name="add_user" 
                    class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all">
                    إضافة
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-md border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">تعديل المستخدم</h3>
            <button id="closeEditModalBtn" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form action="users.php" method="POST">
            <input type="hidden" id="edit_user_id" name="user_id">
            <div class="p-6 space-y-4">
                <div>
                    <label for="edit_username" class="block text-sm font-medium text-gray-300 mb-2">اسم المستخدم</label>
                    <input type="text" id="edit_username" name="username" 
                        class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" 
                        required>
                </div>
                <div>
                    <label for="edit_password" class="block text-sm font-medium text-gray-300 mb-2">
                        كلمة المرور الجديدة 
                        <span class="text-xs text-gray-500">(اتركه فارغاً إذا كنت لا تريد التغيير)</span>
                    </label>
                    <input type="password" id="edit_password" name="password" 
                        class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50"
                        placeholder="••••••••">
                </div>
                <div>
                    <label for="edit_role" class="block text-sm font-medium text-gray-300 mb-2">الدور</label>
                    <select id="edit_role" name="role" 
                        class="w-full appearance-none bg-dark/50 border border-white/10 text-white text-right pr-4 pl-8 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 cursor-pointer">
                        <option value="user">مستخدم</option>
                        <option value="admin">مدير</option>
                    </select>
                    <p id="admin_warning" class="text-xs text-yellow-500 mt-2 hidden flex items-center gap-1">
                        <span class="material-icons-round text-sm">warning</span>
                        <span>لا يمكن تغيير دور المدير في النظام</span>
                    </p>
                </div>
            </div>
            <div class="p-6 border-t border-white/5 flex justify-end gap-4">
                <button type="button" onclick="closeEditModal()" 
                    class="px-6 py-2 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                    إلغاء
                </button>
                <button type="submit" name="edit_user" 
                    class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all">
                    حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteUserModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-md border border-white/10 m-4">
        <div class="p-6 border-b border-white/5">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <span class="material-icons-round text-red-500">warning</span>
                تأكيد الحذف
            </h3>
        </div>
        <div class="p-6">
            <p class="text-gray-300 mb-4">
                هل أنت متأكد من حذف المستخدم 
                <span id="delete_username" class="font-bold text-white"></span>؟
            </p>
            <p class="text-sm text-gray-500">
                لا يمكن التراجع عن هذا الإجراء.
            </p>
        </div>
        <div class="p-6 border-t border-white/5 flex justify-end gap-4">
            <button type="button" onclick="closeDeleteModal()" 
                class="px-6 py-2 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                إلغاء
            </button>
            <form action="users.php" method="POST" id="deleteForm">
                <input type="hidden" id="delete_user_id" name="user_id">
                <button type="submit" name="delete_user" 
                    class="bg-red-500/10 hover:bg-red-500/20 text-red-500 px-6 py-2 rounded-xl font-bold transition-all">
                    حذف
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Add User Modal
    const addUserBtn = document.getElementById('addUserBtn');
    const addUserModal = document.getElementById('addUserModal');
    const closeAddModalBtn = document.getElementById('closeAddModalBtn');

    addUserBtn.addEventListener('click', () => {
        addUserModal.classList.remove('hidden');
    });

    function closeAddModal() {
        addUserModal.classList.add('hidden');
    }

    closeAddModalBtn.addEventListener('click', closeAddModal);

    // Edit User Modal
    const editUserModal = document.getElementById('editUserModal');
    const closeEditModalBtn = document.getElementById('closeEditModalBtn');
    const editRoleSelect = document.getElementById('edit_role');
    const adminWarning = document.getElementById('admin_warning');

    function openEditModal(id, username, role, adminCount) {
        document.getElementById('edit_user_id').value = id;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_role').value = role;
        document.getElementById('edit_password').value = '';
        
        // إذا كان آخر admin في النظام، عطّل تغيير الدور
        if (role === 'admin' && adminCount === 1) {
            editRoleSelect.disabled = true;
            editRoleSelect.classList.add('opacity-50', 'cursor-not-allowed');
            adminWarning.classList.remove('hidden');
        } else {
            editRoleSelect.disabled = false;
            editRoleSelect.classList.remove('opacity-50', 'cursor-not-allowed');
            adminWarning.classList.add('hidden');
        }
        
        editUserModal.classList.remove('hidden');
    }

    function closeEditModal() {
        editUserModal.classList.add('hidden');
    }

    closeEditModalBtn.addEventListener('click', closeEditModal);

    // Delete User Modal
    const deleteUserModal = document.getElementById('deleteUserModal');

    function confirmDelete(id, username) {
        document.getElementById('delete_user_id').value = id;
        document.getElementById('delete_username').textContent = username;
        deleteUserModal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        deleteUserModal.classList.add('hidden');
    }

    // Close modals on outside click
    window.addEventListener('click', (event) => {
        if (event.target === addUserModal) {
            closeAddModal();
        }
        if (event.target === editUserModal) {
            closeEditModal();
        }
        if (event.target === deleteUserModal) {
            closeDeleteModal();
        }
    });

    // Close modals on Escape key
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAddModal();
            closeEditModal();
            closeDeleteModal();
        }
    });
</script>

<?php 
$conn->close();
require_once 'src/footer.php'; 
?>