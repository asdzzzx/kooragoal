<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Security\AuthManager;

/** @var AuthManager $auth */
if (!$auth->check()) {
    http_response_code(401);
    exit('غير مصرح');
}

/** @var Database $db */
$db = $container->get(Database::class);
$fixtureId = (int) ($_GET['fixture'] ?? 0);
$stats = [];
if ($fixtureId > 0) {
    $stats = $db->fetchAll('SELECT * FROM statistics WHERE fixture_id = :fixture ORDER BY team_id', ['fixture' => $fixtureId]);
}
?>
<div class="table-responsive">
    <table class="table table-sm">
        <thead>
        <tr>
            <th>الفريق</th>
            <th>النوع</th>
            <th>القيمة</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($stats as $row): ?>
            <tr>
                <td><?= (int) $row['team_id'] ?></td>
                <td><?= htmlspecialchars($row['type']) ?></td>
                <td><pre class="mb-0 small bg-light p-2"><?= htmlspecialchars($row['value']) ?></pre></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$stats): ?>
            <tr><td colspan="3" class="text-center text-muted">لا توجد إحصائيات.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
