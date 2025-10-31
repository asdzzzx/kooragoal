<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Security\AuthManager;
use Kooragoal\Services\Database;

/** @var AuthManager \$auth */
if (!\$auth->check()) {
    header('Location: /admin/login.php');
    exit;
}

\$db = \$container->get(Database::class);
\$logs = \$db->fetchAll('SELECT * FROM logs ORDER BY created_at DESC LIMIT 200');
\$pageTitle = 'سجل النشاطات';
include __DIR__ . '/../includes/header.php';
?>
<h1 class="mb-4">سجل النشاطات</h1>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>النوع</th>
                <th>الرسالة</th>
                <th>التاريخ</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach (\$logs as \$log): ?>
            <tr>
                <td><?= htmlspecialchars(\$log['type']) ?></td>
                <td><?= htmlspecialchars(\$log['message']) ?></td>
                <td><?= htmlspecialchars(\$log['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!\$logs): ?>
            <tr><td colspan="3">لا يوجد نشاط.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php';
