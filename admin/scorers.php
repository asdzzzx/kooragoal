<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Security\AuthManager;

/** @var AuthManager $auth */
require __DIR__ . '/includes/check_auth.php';

$pageTitle = 'قائمة الهدافين';
$activeMenu = 'scorers';

/** @var Database $db */
$db = $container->get(Database::class);

$leagueId = (int) ($_GET['league'] ?? 0);
$season = (int) ($_GET['season'] ?? date('Y'));
$scorers = [];
if ($leagueId > 0) {
    $scorers = $db->fetchAll(
        'SELECT s.*, p.name AS player_name, t.name AS team_name
         FROM scorers s
         LEFT JOIN players p ON p.id = s.player_id
         LEFT JOIN teams t ON t.id = s.team_id
         WHERE s.league_id = :league AND s.season = :season
         ORDER BY s.goals DESC LIMIT 100',
        ['league' => $leagueId, 'season' => $season]
    );
}

include __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';
echo '<div class="row">';
echo renderAdminSidebar($activeMenu);
?>
<div class="col-xl-10 col-lg-9">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">قائمة الهدافين</h1>
        <a href="/admin/updates.php" class="btn btn-outline-primary">تحديث الهدافين</a>
    </div>

    <form class="card card-body shadow-sm mb-4" method="get">
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">معرف الدوري</label>
                <input type="number" name="league" class="form-control" value="<?= $leagueId ?: '' ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">الموسم</label>
                <input type="number" name="season" class="form-control" value="<?= $season ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">عرض</button>
            </div>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>اللاعب</th>
                        <th>الفريق</th>
                        <th>عدد الأهداف</th>
                        <th>التمريرات الحاسمة</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($scorers as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['player_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['team_name'] ?? '-') ?></td>
                            <td><?= (int) $row['goals'] ?></td>
                            <td><?= (int) $row['assists'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$scorers): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">لا توجد بيانات متاحة.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php';
