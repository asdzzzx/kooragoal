<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Security\AuthManager;
use Kooragoal\Services\Security\CsrfTokenManager;
use Kooragoal\Services\Database;

/** @var AuthManager \$auth */
if (!\$auth->check()) {
    header('Location: /admin/login.php');
    exit;
}

/** @var CsrfTokenManager \$csrf */
\$csrf = \$container->get(CsrfTokenManager::class);
\$db = \$container->get(Database::class);

if (\$_SERVER['REQUEST_METHOD'] === 'POST') {
    if (\$csrf->validateToken(\$_POST['csrf_token'] ?? '')) {
        if (isset(\$_POST['create'])) {
            \$db->execute('INSERT INTO admins (username, password, created_at) VALUES (:username, :password, NOW())', [
                'username' => trim(\$_POST['username']),
                'password' => password_hash(\$_POST['password'], PASSWORD_DEFAULT),
            ]);
        }

        if (isset(\$_POST['delete'])) {
            \$db->execute('DELETE FROM admins WHERE id = :id', ['id' => (int) \$_POST['id']]);
        }

        \$csrf->generateToken();
        header('Location: /admin/users.php');
        exit;
    }

    \$error = 'رمز الحماية غير صالح، حاول مرة أخرى.';
}

\$token = \$csrf->getToken();
\$admins = \$db->fetchAll('SELECT id, username, created_at FROM admins ORDER BY created_at DESC');
\$pageTitle = 'إدارة المستخدمين';
include __DIR__ . '/../includes/header.php';
?>
<h1 class="mb-4">إدارة المستخدمين</h1>
<?php if (!empty(\$error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars(\$error) ?></div>
<?php endif; ?>
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">مستخدمون حاليون</div>
            <div class="card-body">
                <ul class="list-group">
                    <?php foreach (\$admins as \$admin): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <?= htmlspecialchars(\$admin['username']) ?>
                                <small class="text-muted d-block"><?= htmlspecialchars(\$admin['created_at']) ?></small>
                            </span>
                            <form method="post" onsubmit="return confirm('هل أنت متأكد؟');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\$token) ?>">
                                <input type="hidden" name="id" value="<?= (int) \$admin['id'] ?>">
                                <button type="submit" name="delete" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                    <?php if (!\$admins): ?>
                        <li class="list-group-item">لا يوجد مستخدمون مسجلون.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">إضافة مستخدم جديد</div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\$token) ?>">
                    <div class="mb-3">
                        <label class="form-label">اسم المستخدم</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">كلمة المرور</label>
                        <input type="password" name="password" class="form-control" minlength="8" required>
                    </div>
                    <button type="submit" name="create" class="btn btn-primary">إضافة</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php';
