<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Security\AuthManager;

/** @var AuthManager $auth */
require __DIR__ . '/includes/check_auth.php';

$pageTitle = 'إعدادات النظام';
$activeMenu = 'settings';

$config = require __DIR__ . '/../config/config.php';

include __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';
echo '<div class="row">';
echo renderAdminSidebar($activeMenu);
?>
<div class="col-xl-10 col-lg-9">
    <h1 class="h3 mb-4">إعدادات الاتصال</h1>
    <div class="card shadow-sm mb-4">
        <div class="card-header">قاعدة البيانات</div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">DSN</dt>
                <dd class="col-sm-9"><code><?= htmlspecialchars($config['db']['dsn']) ?></code></dd>
                <dt class="col-sm-3">المستخدم</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($config['db']['user']) ?></dd>
            </dl>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-header">واجهة API</div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">العنوان</dt>
                <dd class="col-sm-9"><code><?= htmlspecialchars($config['api']['base_url']) ?></code></dd>
                <dt class="col-sm-3">المفتاح</dt>
                <dd class="col-sm-9"><code><?= str_repeat('*', max(0, strlen($config['api']['key']) - 4)) . substr($config['api']['key'], -4) ?></code></dd>
            </dl>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header">الأمان</div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">مدة الجلسة: <?= (int) $config['security']['session_lifetime'] ?> ثانية</li>
                <li class="list-group-item">عدد المحاولات قبل القفل: <?= (int) $config['security']['lockout_threshold'] ?></li>
                <li class="list-group-item">مدة القفل: <?= (int) $config['security']['lockout_minutes'] ?> دقيقة</li>
            </ul>
        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php';
