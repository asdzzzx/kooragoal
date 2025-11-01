<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Security\AuthManager;
use Kooragoal\Services\Security\CsrfTokenManager;

/** @var AuthManager $auth */
require __DIR__ . '/includes/check_auth.php';

$pageTitle = 'إدارة المستخدمين';
$activeMenu = 'admins';

/** @var CsrfTokenManager $csrf */
$csrf = $container->get(CsrfTokenManager::class);
/** @var Database $db */
$db = $container->get(Database::class);

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($csrf->validateToken($_POST['csrf_token'] ?? '')) {
        if (isset($_POST['create'])) {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            if ($username && strlen($password) >= 8) {
                $db->execute('INSERT INTO admins (username, password, created_at) VALUES (:username, :password, NOW())', [
                    'username' => $username,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                ]);
            } else {
                $error = 'يرجى إدخال بيانات صحيحة (كلمة المرور لا تقل عن 8 أحرف).';
            }
        }

        if (!$error && isset($_POST['delete'])) {
            $db->execute('DELETE FROM admins WHERE id = :id', ['id' => (int) $_POST['id']]);
        }

        if (!$error) {
            $csrf->generateToken();
            header('Location: /admin/users.php');
            exit;
        }
    } else {
        $error = 'رمز الحماية غير صالح، حاول مرة أخرى.';
    }
}

$token = $csrf->getToken();
$admins = $db->fetchAll('SELECT id, username, created_at FROM admins ORDER BY created_at DESC');

include __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';
echo '<div class="row">';
echo renderAdminSidebar($activeMenu);
?>
<div class="col-xl-10 col-lg-9">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">إدارة المستخدمين</h1>
        <a href="/admin/logs.php" class="btn btn-outline-secondary btn-sm">عرض السجل</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">مستخدمون حاليون</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($admins as $admin): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <?= htmlspecialchars($admin['username']) ?>
                                    <small class="text-muted d-block">انضم في <?= htmlspecialchars($admin['created_at']) ?></small>
                                </span>
                                <form method="post" onsubmit="return confirm('هل أنت متأكد من حذف المستخدم؟');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $admin['id'] ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-outline-danger">حذف</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                        <?php if (!$admins): ?>
                            <li class="list-group-item text-center text-muted">لا يوجد مستخدمون مسجلون.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">إضافة مستخدم جديد</div>
                <div class="card-body">
                    <form method="post" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
                        <div class="mb-3">
                            <label class="form-label">اسم المستخدم</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">كلمة المرور</label>
                            <input type="password" name="password" class="form-control" minlength="8" required>
                        </div>
                        <button type="submit" name="create" class="btn btn-primary">إضافة المستخدم</button>
                    </form>
                    <p class="text-muted small mt-3">* يتم تخزين كلمات المرور باستخدام <code>password_hash</code>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php';
