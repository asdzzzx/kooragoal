<?php

require_once __DIR__ . '/bootstrap.php';

use App\Support\Config;
use App\Support\Database;

ensure_admin();

$apiKey = Config::get('api.key');
$maskedKey = substr($apiKey, 0, 4) . str_repeat('*', max(strlen($apiKey) - 8, 0)) . substr($apiKey, -4);

$pdo = Database::getConnection();
$backups = $pdo->query('SELECT file_path, created_at FROM backups ORDER BY created_at DESC LIMIT 20')->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الإعدادات</title>
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
            <h2>مفتاح API المستخدم</h2>
            <p>المفتاح: <strong><?php echo htmlspecialchars($maskedKey, ENT_QUOTES, 'UTF-8'); ?></strong></p>
            <p>الرابط: <code><?php echo htmlspecialchars(Config::get('api.base_url'), ENT_QUOTES, 'UTF-8'); ?></code></p>
        </div>
        <div class="card">
            <h2>الجدولة</h2>
            <ul>
                <li>المباريات اليومية: <strong>يوميًا 00:05</strong> - cron/get_daily_fixtures.php</li>
                <li>المباريات الجارية: <strong>كل 25 ثانية</strong> - cron/get_live_matches.php</li>
                <li>الإحصائيات والأحداث: <strong>كل 50 ثانية</strong> - cron/get_events_stats.php</li>
                <li>التشكيل: <strong>كل 5 دقائق</strong> - cron/get_lineups.php</li>
                <li>الترتيب والهدافين: <strong>كل ساعتين</strong> - cron/get_standings.php</li>
                <li>الفرق واللاعبين: <strong>شهريًا</strong> - cron/get_players_teams.php</li>
                <li>النسخة الاحتياطية: <strong>يوميًا 04:00</strong> - cron/create_backup.php</li>
            </ul>
        </div>
        <div class="card">
            <h2>آخر النسخ الاحتياطية</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>المسار</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td><?php echo $backup['created_at']; ?></td>
                            <td><code><?php echo htmlspecialchars($backup['file_path'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
