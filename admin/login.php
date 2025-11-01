<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Security\AuthManager;
use Kooragoal\Services\Security\CsrfTokenManager;

/** @var AuthManager $auth */
/** @var CsrfTokenManager $csrf */
$csrf = $container->get(CsrfTokenManager::class);

if ($auth->check()) {
    header('Location: /admin/index.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$csrf->validateToken($_POST['csrf_token'] ?? '')) {
        $error = 'رمز التحقق غير صالح، يرجى إعادة المحاولة.';
    } else {
        try {
            if ($auth->attempt(trim($_POST['username'] ?? ''), $_POST['password'] ?? '')) {
                header('Location: /admin/index.php');
                exit;
            }
            $error = 'بيانات الدخول غير صحيحة.';
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }
    }
}

$pageTitle = 'تسجيل الدخول - Kooragoal';
include __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 text-center mb-4">تسجيل الدخول</h1>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf->generateToken()) ?>">
                    <div class="mb-3">
                        <label class="form-label">اسم المستخدم</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">كلمة المرور</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">تسجيل الدخول</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php';
