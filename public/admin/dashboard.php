<?php

require_once __DIR__ . '/bootstrap.php';

use App\Support\Database;
ensure_admin();

$pdo = Database::getConnection();

$totalFixtures = (int)$pdo->query('SELECT COUNT(*) FROM fixtures')->fetchColumn();
$liveFixtures = (int)$pdo->query("SELECT COUNT(*) FROM fixtures WHERE status_short IN ('1H','2H','ET','P','BT','INT','LIVE')")->fetchColumn();
$leagues = $pdo->query('SELECT COUNT(*) FROM leagues')->fetchColumn();
$lastLogs = @array_slice(@file(storage_path('logs/app.log')) ?: [], -10);

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="navbar">
        <div>لوحة تحكم كورة جول</div>
        <div>
            <a href="dashboard.php">الرئيسية</a>
            <a href="leagues.php">الدوريات</a>
            <a href="settings.php">الإعدادات</a>
            <a href="logs.php">السجلات</a>
            <a href="logout.php">تسجيل الخروج</a>
        </div>
    </div>
    <div class="container">
        <div class="grid">
            <div class="card card-highlight">
                <h2>إجمالي المباريات</h2>
                <p class="badge"><?php echo $totalFixtures; ?></p>
            </div>
            <div class="card card-highlight">
                <h2>مباريات مباشرة الآن</h2>
                <p class="badge"><?php echo $liveFixtures; ?></p>
            </div>
            <div class="card card-highlight">
                <h2>الدوريات المتاحة</h2>
                <p class="badge"><?php echo $leagues; ?></p>
            </div>
        </div>

        <div class="card">
            <h2>آخر السجلات</h2>
            <pre class="log-output"><?php echo htmlspecialchars(implode('', $lastLogs), ENT_QUOTES, 'UTF-8'); ?></pre>
        </div>
    </div>
</body>
</html>
