<?php
require_once __DIR__ . '/includes/bootstrap.php';

use Kooragoal\Services\Database;

\$db = \$container->get(Database::class);
\$fixtureId = (int) (\$_GET['fixture'] ?? 0);
\$events = \$db->fetchAll('SELECT e.*, t.name as team_name FROM events e JOIN teams t ON t.id = e.team_id WHERE fixture_id = :id ORDER BY time_elapsed ASC', ['id' => \$fixtureId]);
?>
<ul class="list-group list-group-flush">
<?php foreach (\$events as \$event): ?>
    <li class="list-group-item d-flex justify-content-between">
        <span>
            <strong><?= htmlspecialchars(\$event['time_elapsed']) ?>'</strong>
            <?= htmlspecialchars(\$event['type']) ?> - <?= htmlspecialchars(\$event['detail']) ?>
            <small class="text-muted"><?= htmlspecialchars(\$event['team_name']) ?></small>
        </span>
        <span><?= htmlspecialchars(\$event['comments'] ?? '') ?></span>
    </li>
<?php endforeach; ?>
<?php if (!\$events): ?>
    <li class="list-group-item">لا توجد أحداث متاحة.</li>
<?php endif; ?>
</ul>
