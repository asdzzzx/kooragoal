<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Security\AuthManager;
use Kooragoal\Services\Security\CsrfTokenManager;

/** @var AuthManager $auth */
require __DIR__ . '/includes/check_auth.php';

$pageTitle = 'إدارة التحديثات';
$activeMenu = 'updates';

/** @var Database $db */
$db = $container->get(Database::class);
/** @var CsrfTokenManager $csrf */
$csrf = $container->get(CsrfTokenManager::class);
$token = $csrf->getToken();

$updates = $db->fetchAll('SELECT * FROM system_updates ORDER BY last_run DESC');

include __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';
echo '<div class="row">';
echo renderAdminSidebar($activeMenu);
?>
<div class="col-xl-10 col-lg-9">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">إدارة التحديثات</h1>
        <button class="btn btn-outline-secondary" id="refreshStatus">تحديث الحالة</button>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header">تشغيل سريع</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><button class="btn btn-outline-primary w-100 task-trigger" data-task="fixtures_daily">مباريات اليوم</button></div>
                <div class="col-md-4"><button class="btn btn-outline-primary w-100 task-trigger" data-task="fixtures_live">المباريات الجارية</button></div>
                <div class="col-md-4"><button class="btn btn-outline-primary w-100 task-trigger" data-task="fixture_details" data-ask="معرف المباراة (مثال: 123456)">إحصائيات مباراة</button></div>
                <div class="col-md-4"><button class="btn btn-outline-primary w-100 task-trigger" data-task="fixture_lineups" data-ask="معرف المباراة (مثال: 123456)">تشكيلات مباراة</button></div>
                <div class="col-md-4"><button class="btn btn-outline-primary w-100 task-trigger" data-task="standings_scorers" data-ask="leagueId:season (مثال: 39:2024)">الترتيب والهدافين</button></div>
                <div class="col-md-4"><button class="btn btn-outline-primary w-100 task-trigger" data-task="teams_players" data-ask="leagueId:season (مثال: 39:2024)">الفرق واللاعبون</button></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">سجل المهام</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0" id="updatesTable">
                    <thead class="table-light">
                    <tr>
                        <th>المهمة</th>
                        <th>الحالة</th>
                        <th>آخر تشغيل</th>
                        <th>الرسالة</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($updates as $update): ?>
                        <tr>
                            <td><?= htmlspecialchars($update['task']) ?></td>
                            <td><span class="badge bg-<?= $update['status'] === 'success' ? 'success' : 'danger' ?>"><?= $update['status'] === 'success' ? 'ناجح' : 'فشل' ?></span></td>
                            <td><?= htmlspecialchars($update['last_run']) ?></td>
                            <td><?= htmlspecialchars($update['message'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$updates): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">لم يتم تنفيذ أي تحديثات بعد.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<script>
(function($){
    function runTask(task, context){
        const payload = { task: task, csrf_token: '<?= htmlspecialchars($token) ?>' };
        if(context){ payload.context = context; }
        return $.post('/admin/ajax/manual_update.php', payload);
    }

    $('.task-trigger').on('click', function(){
        const task = $(this).data('task');
        const ask = $(this).data('ask');
        let context = '';
        if(ask){
            context = prompt(ask);
            if(!context){ return; }
        }
        $(this).prop('disabled', true);
        runTask(task, context)
            .done(function(resp){ alert(resp.message || 'تم التنفيذ'); $('#refreshStatus').trigger('click'); })
            .fail(function(xhr){ alert(xhr.responseJSON?.message || 'حدث خطأ'); })
            .always(() => $(this).prop('disabled', false));
    });

    $('#refreshStatus').on('click', function(){
        const btn = $(this).prop('disabled', true);
        $.get('/admin/ajax/check_updates.php', function(resp){
            const tbody = $('#updatesTable tbody').empty();
            if(resp.updates && resp.updates.length){
                resp.updates.forEach(function(update){
                    tbody.append(
                        '<tr>' +
                        '<td>' + (update.task || '-') + '</td>' +
                        '<td><span class="badge bg-' + (update.status === 'success' ? 'success' : 'danger') + '">' + (update.status === 'success' ? 'ناجح' : 'فشل') + '</span></td>' +
                        '<td>' + (update.last_run || '-') + '</td>' +
                        '<td>' + (update.message || '-') + '</td>' +
                        '</tr>'
                    );
                });
            } else {
                tbody.append('<tr><td colspan="4" class="text-center text-muted py-4">لا توجد بيانات.</td></tr>');
            }
        }).always(function(){ btn.prop('disabled', false); });
    });
})(jQuery);
</script>
<?php include __DIR__ . '/includes/footer.php';
