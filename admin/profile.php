<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Security\AuthManager;
use Kooragoal\Services\Security\CsrfTokenManager;

/** @var AuthManager $auth */
require __DIR__ . '/includes/check_auth.php';

$pageTitle = 'الملف الشخصي';
$activeMenu = 'profile';

/** @var Database $db */
$db = $container->get(Database::class);
/** @var CsrfTokenManager $csrf */
$csrf = $container->get(CsrfTokenManager::class);
$token = $csrf->getToken();

$error = null;
$success = null;
$admin = $db->fetch('SELECT * FROM admins WHERE id = :id', ['id' => $_SESSION['admin_id']]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $csrf->validateToken($_POST['csrf_token'] ?? '')) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirmation'] ?? '';
    if ($password && $password === $confirm && strlen($password) >= 8) {
        $db->execute('UPDATE admins SET password = :password WHERE id = :id', [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'id' => $admin['id'],
        ]);
        $success = 'تم تحديث كلمة المرور بنجاح.';
    } else {
        $error = 'يرجى التأكد من مطابقة كلمة المرور والحد الأدنى للطول (8 أحرف).';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = 'رمز التحقق غير صالح.';
}

include __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';
echo '<div class="row">';
echo renderAdminSidebar($activeMenu);
?>
<div class="col-xl-10 col-lg-9">
    <h1 class="h3 mb-4">الملف الشخصي</h1>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title">معلومات الحساب</h5>
            <p class="mb-1"><strong>اسم المستخدم:</strong> <?= htmlspecialchars($admin['username']) ?></p>
            <p class="text-muted">تم الإنشاء في <?= htmlspecialchars($admin['created_at']) ?></p>

            <hr>
            <h5 class="card-title">تغيير كلمة المرور</h5>
            <form method="post" class="row g-3" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
                <div class="col-md-6">
                    <label class="form-label">كلمة المرور الجديدة</label>
                    <input type="password" name="password" class="form-control" minlength="8" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php';
