<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Security\AuthManager;

/** @var AuthManager $auth */
require __DIR__ . '/includes/check_auth.php';

$pageTitle = 'جداول الترتيب';
$activeMenu = 'standings';

/** @var Database $db */
$db = $container->get(Database::class);

$leagueId = (int) ($_GET['league'] ?? 0);
$season = (int) ($_GET['season'] ?? date('Y'));

$leagues = $db->fetchAll('SELECT DISTINCT league_id AS id, season FROM standings ORDER BY season DESC');
$standings = [];
if ($leagueId > 0) {
    $standings = $db->fetchAll(
        'SELECT s.*, t.name AS team_name FROM standings s JOIN teams t ON t.id = s.team_id
         WHERE s.league_id = :league AND s.season = :season ORDER BY s.rank_position ASC',
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
        <h1 class="h3 mb-0">جداول الترتيب</h1>
        <a href="/admin/updates.php" class="btn btn-outline-primary">تحديث الترتيب</a>
    </div>

    <form class="card card-body shadow-sm mb-4" method="get">
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">معرف الدوري</label>
                <input type="number" name="league" class="form-control" value="<?= $leagueId ?: '' ?>" placeholder="مثال: 39">
            </div>
            <div class="col-md-4">
                <label class="form-label">الموسم</label>
                <input type="number" name="season" class="form-control" value="<?= $season ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">عرض</button>
            </div>
        </div>
        <?php if ($leagues): ?>
            <small class="text-muted d-block mt-2">دوريات متوفرة: <?= implode(', ', array_map(fn($row) => $row['id'] . ':' . $row['season'], $leagues)) ?></small>
        <?php endif; ?>
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>الترتيب</th>
                        <th>الفريق</th>
                        <th>النقاط</th>
                        <th>فارق الأهداف</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($standings as $row): ?>
                        <tr>
                            <td><?= (int) $row['rank_position'] ?></td>
                            <td><?= htmlspecialchars($row['team_name']) ?></td>
                            <td><?= (int) $row['points'] ?></td>
                            <td><?= (int) $row['goals_diff'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$standings): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">لم يتم العثور على بيانات لهذا الدوري.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php';
