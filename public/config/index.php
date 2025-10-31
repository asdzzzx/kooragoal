<?php

require_once __DIR__ . '/../admin/bootstrap.php';

use App\Support\Config;

ensure_admin();

function mask_value(string $value): string
{
    $length = strlen($value);

    if ($length <= 8) {
        return str_repeat('*', $length);
    }

    return substr($value, 0, 4) . str_repeat('*', $length - 8) . substr($value, -4);
}

$configView = [
    'app' => [
        'name' => Config::get('app.name'),
        'url' => Config::get('app.url'),
        'timezone' => Config::get('app.timezone'),
        'domain_whitelist' => Config::get('app.domain_whitelist'),
    ],
    'database' => [
        'driver' => Config::get('database.driver'),
        'host' => Config::get('database.host'),
        'port' => Config::get('database.port'),
        'name' => Config::get('database.name'),
        'username' => Config::get('database.username'),
    ],
    'api' => [
        'base_url' => Config::get('api.base_url'),
        'key' => mask_value(Config::get('api.key')),
        'host' => Config::get('api.host'),
        'rate_limit' => Config::get('api.rate_limit'),
    ],
    'security' => [
        'api_rate_limit' => Config::get('security.api_rate_limit'),
        'admin_rate_limit' => Config::get('security.admin_rate_limit'),
        'backup_path' => Config::get('security.backup.path'),
    ],
    'leagues_total' => count(Config::get('leagues', [])),
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إعدادات النظام</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="navbar">
        <div>لوحة تحكم كورة جول</div>
        <div>
            <a href="../admin/dashboard.php">الرئيسية</a>
            <a href="../admin/leagues.php">الدوريات</a>
            <a href="../admin/settings.php">الإعدادات</a>
            <a href="../admin/logout.php">تسجيل الخروج</a>
        </div>
    </div>
    <div class="container">
        <div class="card">
            <h2>معلومات التطبيق</h2>
            <p><strong>الاسم:</strong> <?php echo htmlspecialchars($configView['app']['name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>الرابط:</strong> <code><?php echo htmlspecialchars($configView['app']['url'], ENT_QUOTES, 'UTF-8'); ?></code></p>
            <p><strong>المنطقة الزمنية:</strong> <?php echo htmlspecialchars($configView['app']['timezone'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>النطاقات المسموح بها:</strong></p>
            <ul>
                <?php foreach ($configView['app']['domain_whitelist'] as $domain): ?>
                    <li><?php echo htmlspecialchars($domain, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="card">
            <h2>قاعدة البيانات</h2>
            <p><strong>نوع قاعدة البيانات:</strong> <?php echo htmlspecialchars($configView['database']['driver'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>المضيف:</strong> <?php echo htmlspecialchars($configView['database']['host'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>المنفذ:</strong> <?php echo htmlspecialchars((string) $configView['database']['port'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>اسم القاعدة:</strong> <?php echo htmlspecialchars($configView['database']['name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>اسم المستخدم:</strong> <?php echo htmlspecialchars($configView['database']['username'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <div class="card">
            <h2>واجهة API</h2>
            <p><strong>الرابط الأساسي:</strong> <code><?php echo htmlspecialchars($configView['api']['base_url'], ENT_QUOTES, 'UTF-8'); ?></code></p>
            <p><strong>المفتاح:</strong> <?php echo htmlspecialchars($configView['api']['key'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>المضيف:</strong> <?php echo htmlspecialchars($configView['api']['host'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>الحد الأقصى للطلبات في الدقيقة:</strong> <?php echo htmlspecialchars((string) $configView['api']['rate_limit'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <div class="card">
            <h2>الحماية والنسخ الاحتياطي</h2>
            <p><strong>حد API:</strong> <?php echo htmlspecialchars($configView['security']['api_rate_limit']['max_requests'], ENT_QUOTES, 'UTF-8'); ?> / <?php echo htmlspecialchars($configView['security']['api_rate_limit']['per_minutes'], ENT_QUOTES, 'UTF-8'); ?> دقيقة</p>
            <p><strong>حد لوحة التحكم:</strong> <?php echo htmlspecialchars($configView['security']['admin_rate_limit']['max_requests'], ENT_QUOTES, 'UTF-8'); ?> / <?php echo htmlspecialchars($configView['security']['admin_rate_limit']['per_minutes'], ENT_QUOTES, 'UTF-8'); ?> دقيقة</p>
            <p><strong>مسار النسخ الاحتياطي:</strong> <code><?php echo htmlspecialchars($configView['security']['backup_path'], ENT_QUOTES, 'UTF-8'); ?></code></p>
        </div>

        <div class="card">
            <h2>الدوريات المدعومة</h2>
            <p>عدد الدوريات في النظام: <strong><?php echo htmlspecialchars((string) $configView['leagues_total'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
        </div>
    </div>
</body>
</html>
