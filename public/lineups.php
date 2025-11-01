<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;

$db = $container->get(Database::class);
$fixtureId = (int) ($_GET['fixture'] ?? 0);
$lineups = $db->fetchAll('SELECT * FROM lineups WHERE fixture_id = :id', ['id' => $fixtureId]);
?>
<?php foreach ($lineups as $lineup): ?>
    <div class="mb-4">
        <h5>فريق <?= htmlspecialchars($lineup['team_id']) ?> - تشكيل <?= htmlspecialchars($lineup['formation']) ?></h5>
        <strong>التشكيلة الأساسية</strong>
        <pre class="bg-light p-2"><?= htmlspecialchars($lineup['start_xi']) ?></pre>
        <strong>البدلاء</strong>
        <pre class="bg-light p-2"><?= htmlspecialchars($lineup['substitutes']) ?></pre>
    </div>
<?php endforeach; ?>
<?php if (!$lineups): ?>
    <p>لم يتم نشر التشكيلة بعد.</p>
<?php endif; ?>
