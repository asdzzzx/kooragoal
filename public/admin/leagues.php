<?php

require_once __DIR__ . '/bootstrap.php';

use App\Support\Config;
use App\Support\Database;

ensure_admin();

$pdo = Database::getConnection();
$leagues = $pdo->query('SELECT api_id, name, country, season, type, last_update FROM leagues ORDER BY country, name')->fetchAll();
$configured = Config::get('leagues');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الدوريات</title>
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
            <h2>الدوريات المخزنة (<?php echo count($leagues); ?>)</h2>
            <p>تأكد من مطابقة رقم الدوري (API ID) مع <a href="https://www.api-football.com/" target="_blank" rel="noopener">API-FOOTBALL</a>.</p>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>الاسم</th>
                        <th>الدولة</th>
                        <th>الموسم</th>
                        <th>النوع</th>
                        <th>آخر تحديث</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leagues as $league): ?>
                        <tr>
                            <td><?php echo $league['api_id']; ?></td>
                            <td><?php echo htmlspecialchars($league['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($league['country'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo $league['season']; ?></td>
                            <td><?php echo htmlspecialchars($league['type'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo $league['last_update']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card">
            <h2>الدوريات في ملف الإعدادات (<?php echo count($configured); ?>)</h2>
            <p>القيم التي تحتوي على رقم (ID) فارغ لن يتم سحبها حتى يتم تحديثها بالقيمة الصحيحة.</p>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>الاسم</th>
                        <th>الدولة</th>
                        <th>الموسم</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($configured as $league): ?>
                        <tr>
                            <td><?php echo $league['api_id']; ?></td>
                            <td><?php echo htmlspecialchars($league['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($league['country'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo $league['season']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
