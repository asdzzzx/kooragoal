<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;

$db = $container->get(Database::class);
$fixtureId = (int) ($_GET['fixture'] ?? 0);
$stats = $db->fetchAll('SELECT * FROM statistics WHERE fixture_id = :id', ['id' => $fixtureId]);

$grouped = [];
foreach ($stats as $row) {
    $grouped[$row['type']][] = $row;
}
?>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>الإحصائية</th>
                <th>الفريق</th>
                <th>القيمة</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($grouped as $type => $entries): ?>
            <?php foreach ($entries as $entry): ?>
                <tr>
                    <td><?= htmlspecialchars($type) ?></td>
                    <td><?= htmlspecialchars($entry['team_id']) ?></td>
                    <td><?= htmlspecialchars($entry['value']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <?php if (!$stats): ?>
            <tr><td colspan="3">لا توجد بيانات إحصائية متاحة.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
