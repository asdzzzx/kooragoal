<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/fixtures-functions.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Updaters\UpdateManager;

/** @var Kooragoal\Services\Container $container */
$db = $container->get(Database::class);

$requestedDate = $_GET['date'] ?? date('Y-m-d');
$selectedDate = \DateTimeImmutable::createFromFormat('Y-m-d', $requestedDate) ?: new \DateTimeImmutable('today');
$pageTitle = 'مباريات ' . $selectedDate->format('Y-m-d');

$fixtures = kooragoalFetchFixturesByDate($db, $selectedDate);

$isToday = $selectedDate->format('Y-m-d') === (new \DateTimeImmutable('today'))->format('Y-m-d');
if ($isToday) {
    $lastDaily = $db->fetch('SELECT last_run FROM system_updates WHERE task = :task', ['task' => 'fixtures_daily']);
    $needsRefresh = !$fixtures || !$lastDaily || (time() - strtotime($lastDaily['last_run'])) > 86400;

    if ($needsRefresh) {
        $updateManager = new UpdateManager(
            $db,
            $container->get(Kooragoal\Services\ApiClient::class),
            $container->get(Kooragoal\Services\Logger::class)
        );
        $updateManager->updateDailyFixtures(new \DateTimeImmutable('now'));
        $fixtures = kooragoalFetchFixturesByDate($db, $selectedDate);
    }
}

include __DIR__ . '/includes/site-header.php';
?>
<div id="fixturesSwipeArea">
    <h1 class="mb-4">مباريات اليوم</h1>
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary" id="prevDay" aria-label="اليوم السابق">&#x2190;</button>
                    <input type="date" class="form-control" id="fixturesDate" value="<?= htmlspecialchars($selectedDate->format('Y-m-d')) ?>">
                    <button class="btn btn-outline-secondary" id="nextDay" aria-label="اليوم التالي">&#x2192;</button>
                </div>
                <div class="ms-md-auto text-md-end">
                    <span class="fw-semibold" id="selectedDateLabel"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3" id="fixturesContainer">
        <?php kooragoalRenderFixturesCards($fixtures); ?>
    </div>
</div>

<script>
$(function () {
    const REFRESH_INTERVAL = 15000;
    const $fixturesContainer = $('#fixturesContainer');
    const $fixturesDate = $('#fixturesDate');
    const $selectedDateLabel = $('#selectedDateLabel');
    const $swipeArea = $('#fixturesSwipeArea');
    let refreshTimer = null;
    let touchStartX = null;

    const formatOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const formatter = new Intl.DateTimeFormat('ar-EG', formatOptions);

    function updateLabel() {
        const dateValue = $fixturesDate.val();
        if (!dateValue) {
            $selectedDateLabel.text('');
            return;
        }

        const date = new Date(dateValue + 'T00:00:00');
        $selectedDateLabel.text(formatter.format(date));
    }

    function fetchFixtures() {
        const dateValue = $fixturesDate.val();
        $.get('/partials/fixtures-list.php', { date: dateValue })
            .done(function (html) {
                $fixturesContainer.html(html);
            })
            .fail(function () {
                $fixturesContainer.html('<div class="col-12"><div class="alert alert-danger">تعذر تحديث المباريات.</div></div>');
            });
    }

    function scheduleRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
        }
        refreshTimer = setInterval(fetchFixtures, REFRESH_INTERVAL);
    }

    function changeDay(delta) {
        const currentValue = $fixturesDate.val();
        const currentDate = currentValue ? new Date(currentValue + 'T00:00:00') : new Date();
        currentDate.setDate(currentDate.getDate() + delta);
        const nextValue = currentDate.toISOString().slice(0, 10);
        $fixturesDate.val(nextValue).trigger('change');
    }

    $fixturesDate.on('change', function () {
        updateLabel();
        fetchFixtures();
        scheduleRefresh();
    });

    $('#prevDay').on('click', function () {
        changeDay(-1);
    });

    $('#nextDay').on('click', function () {
        changeDay(1);
    });

    $swipeArea.on('touchstart', function (event) {
        const touches = event.originalEvent.touches;
        if (touches && touches.length === 1) {
            touchStartX = touches[0].clientX;
        }
    });

    $swipeArea.on('touchend', function (event) {
        if (touchStartX === null) {
            return;
        }

        const changedTouches = event.originalEvent.changedTouches;
        if (!changedTouches || !changedTouches.length) {
            touchStartX = null;
            return;
        }

        const touchEndX = changedTouches[0].clientX;
        const deltaX = touchEndX - touchStartX;
        touchStartX = null;

        if (Math.abs(deltaX) < 60) {
            return;
        }

        if (deltaX < 0) {
            changeDay(1);
        } else {
            changeDay(-1);
        }
    });

    updateLabel();
    fetchFixtures();
    scheduleRefresh();
});
</script>
<?php include __DIR__ . '/includes/site-footer.php';
