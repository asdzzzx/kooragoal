<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Security\AuthManager;

/** @var AuthManager $auth */
require __DIR__ . '/includes/check_auth.php';

$pageTitle = 'إحصائيات المباريات';
$activeMenu = 'statistics';

/** @var Database $db */
$db = $container->get(Database::class);

$fixtureId = (int) ($_GET['fixture'] ?? 0);
$statistics = [];
if ($fixtureId > 0) {
    $statistics = $db->fetchAll(
        'SELECT * FROM statistics WHERE fixture_id = :fixture ORDER BY team_id',
        ['fixture' => $fixtureId]
    );
}

include __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';
echo '<div class="row">';
echo renderAdminSidebar($activeMenu);
?>
<div class="col-xl-10 col-lg-9">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">إحصائيات المباريات</h1>
        <a href="/admin/updates.php" class="btn btn-outline-primary">تحديث الإحصائيات</a>
    </div>

    <form class="card card-body shadow-sm mb-4" method="get">
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">معرف المباراة</label>
                <input type="number" name="fixture" class="form-control" value="<?= $fixtureId ?: '' ?>" placeholder="مثال: 123456">
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
                        <th>الفريق</th>
                        <th>النوع</th>
                        <th>القيمة</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($statistics as $stat): ?>
                        <tr>
                            <td><?= (int) $stat['team_id'] ?></td>
                            <td><?= htmlspecialchars($stat['type']) ?></td>
                            <td><pre class="mb-0 small bg-light p-2"><?= htmlspecialchars($stat['value']) ?></pre></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$statistics): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">لا توجد إحصائيات لهذه المباراة.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php';
