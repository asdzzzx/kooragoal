<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Security\AuthManager;

/** @var AuthManager $auth */
require __DIR__ . '/includes/check_auth.php';

$pageTitle = 'إدارة الدوريات';
$activeMenu = 'leagues';

/** @var Database $db */
$db = $container->get(Database::class);
$search = trim($_GET['q'] ?? '');

$sql = 'SELECT * FROM leagues';
$params = [];
if ($search !== '') {
    $sql .= ' WHERE name LIKE :search OR country LIKE :search';
    $params['search'] = "%$search%";
}
$sql .= ' ORDER BY country, name';
$leagues = $db->fetchAll($sql, $params);

include __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';
echo '<div class="row">';
echo renderAdminSidebar($activeMenu);
?>
<div class="col-xl-10 col-lg-9">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">إدارة الدوريات</h1>
        <a href="/admin/updates.php" class="btn btn-outline-primary">تحديث الترتيب والهدافين</a>
    </div>

    <form class="card card-body shadow-sm mb-4" method="get">
        <div class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label">بحث</label>
                <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="اسم الدوري أو الدولة">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success">بحث</button>
            </div>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الدوري</th>
                        <th>الدولة</th>
                        <th>الموسم</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($leagues as $league): ?>
                        <tr>
                            <td><?= (int) $league['id'] ?></td>
                            <td><?= htmlspecialchars($league['name']) ?></td>
                            <td><?= htmlspecialchars($league['country']) ?></td>
                            <td><?= htmlspecialchars($league['season']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$leagues): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">لا توجد دوريات مطابقة.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php';
