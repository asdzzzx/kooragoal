<?php

require_once __DIR__ . '/bootstrap.php';

ensure_admin();

$logFile = storage_path('logs/app.log');
$logs = file_exists($logFile) ? file_get_contents($logFile) : 'لا توجد سجلات بعد.';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>سجلات النظام</title>
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
        <div class="card">
            <h2>السجلات الكاملة</h2>
            <pre class="log-output"><?php echo htmlspecialchars($logs, ENT_QUOTES, 'UTF-8'); ?></pre>
        </div>
    </div>
</body>
</html>
