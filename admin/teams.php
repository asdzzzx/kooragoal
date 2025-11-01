<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Security\AuthManager;

/** @var AuthManager $auth */
require __DIR__ . '/includes/check_auth.php';

$pageTitle = 'إدارة الفرق';
$activeMenu = 'teams';

/** @var Database $db */
$db = $container->get(Database::class);

$search = trim($_GET['q'] ?? '');
$sql = 'SELECT id, name, country, founded, logo FROM teams';
$params = [];
if ($search !== '') {
    $sql .= ' WHERE name LIKE :search OR country LIKE :search';
    $params['search'] = "%$search%";
}
$sql .= ' ORDER BY name ASC LIMIT 200';
$teams = $db->fetchAll($sql, $params);

include __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';
echo '<div class="row">';
echo renderAdminSidebar($activeMenu);
?>
<div class="col-xl-10 col-lg-9">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">الفرق</h1>
        <a href="/admin/updates.php" class="btn btn-outline-primary">تحديث بيانات الفرق</a>
    </div>

    <form class="card card-body shadow-sm mb-4" method="get">
        <div class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label">بحث عن فريق</label>
                <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="اسم الفريق أو الدولة">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success">بحث</button>
            </div>
        </div>
    </form>

    <div class="row g-3">
        <?php foreach ($teams as $team): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card shadow-sm h-100">
                    <?php if (!empty($team['logo'])): ?>
                        <img src="<?= htmlspecialchars($team['logo']) ?>" class="card-img-top p-3" alt="logo">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title mb-1"><?= htmlspecialchars($team['name']) ?></h5>
                        <p class="text-muted mb-1">الدولة: <?= htmlspecialchars($team['country'] ?? '-') ?></p>
                        <p class="text-muted">تأسس عام: <?= htmlspecialchars($team['founded'] ?? '-') ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$teams): ?>
            <div class="col-12">
                <div class="alert alert-info">لا توجد فرق مطابقة.</div>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php';
