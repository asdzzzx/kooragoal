<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Security\AuthManager;
use Kooragoal\Services\Scheduler;
use Kooragoal\Services\Security\CsrfTokenManager;

/** @var AuthManager \$auth */
/** @var Scheduler \$scheduler */
/** @var CsrfTokenManager \$csrf */

if (!\$auth->check()) {
    header('Location: /admin/login.php');
    exit;
}

\$pageTitle = 'لوحة التحكم';
\$db = \$container->get(Kooragoal\Services\Database::class);

\$counts = [
    'leagues' => \$db->fetch('SELECT COUNT(*) as total FROM leagues')['total'] ?? 0,
    'teams' => \$db->fetch('SELECT COUNT(*) as total FROM teams')['total'] ?? 0,
    'fixtures_today' => \$db->fetch('SELECT COUNT(*) as total FROM fixtures WHERE DATE(FROM_UNIXTIME(timestamp)) = CURDATE()')['total'] ?? 0,
];

\$updates = \$db->fetchAll('SELECT * FROM system_updates ORDER BY last_run DESC LIMIT 10');

include __DIR__ . '/../includes/header.php';
?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-bg-primary">
            <div class="card-body">
                <h5 class="card-title">عدد الدوريات</h5>
                <p class="display-5 mb-0"><?= (int) \$counts['leagues'] ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-success">
            <div class="card-body">
                <h5 class="card-title">عدد الفرق</h5>
                <p class="display-5 mb-0"><?= (int) \$counts['teams'] ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-warning">
            <div class="card-body">
                <h5 class="card-title">مباريات اليوم</h5>
                <p class="display-5 mb-0"><?= (int) \$counts['fixtures_today'] ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>التحديثات الأخيرة</span>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#manualUpdateModal">تشغيل تحديث يدوي</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>المهمة</th>
                        <th>الحالة</th>
                        <th>آخر تشغيل</th>
                        <th>الرسالة</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (\$updates as \$update): ?>
                    <tr>
                        <td><?= htmlspecialchars(\$update['task']) ?></td>
                        <td>
                            <span class="badge bg-<?= \$update['status'] === 'success' ? 'success' : 'danger' ?>">
                                <?= \$update['status'] === 'success' ? 'ناجح' : 'فشل' ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars(\$update['last_run']) ?></td>
                        <td><?= htmlspecialchars(\$update['message'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="manualUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="manualUpdateForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf->generateToken()) ?>">
            <div class="modal-header">
                <h5 class="modal-title">تشغيل تحديث يدوي</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">اختر نوع التحديث</label>
                    <select class="form-select" name="task" required>
                        <option value="fixtures_daily">مباريات اليوم</option>
                        <option value="fixtures_live">المباريات الجارية</option>
                        <option value="fixture_details">الإحصائيات والأحداث</option>
                        <option value="fixture_lineups">التشكيلات</option>
                        <option value="standings_scorers">الترتيب والهدافين</option>
                        <option value="teams_players">الفرق واللاعبين</option>
                    </select>
                </div>
                <div class="mb-3" id="taskContext" style="display: none;">
                    <label class="form-label">المعرف</label>
                    <input type="text" name="context" class="form-control" placeholder="مثال: fixture=123 أو league=39:2024">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="submit" class="btn btn-primary">تشغيل</button>
            </div>
        </form>
    </div>
</div>

<script>
$(function(){
    $('#manualUpdateForm select[name="task"]').on('change', function(){
        const requiresContext = ['fixture_details', 'fixture_lineups', 'standings_scorers', 'teams_players'];
        if(requiresContext.includes($(this).val())){
            $('#taskContext').slideDown();
        } else {
            $('#taskContext').slideUp();
        }
    });

    $('#manualUpdateForm').on('submit', function(e){
        e.preventDefault();
        const form = $(this);
        $.ajax({
            url: '/admin/run-task.php',
            method: 'POST',
            data: form.serialize(),
            success: function(response){
                alert(response.message || 'تم تنفيذ التحديث');
                location.reload();
            },
            error: function(xhr){
                alert(xhr.responseJSON?.message || 'حدث خطأ أثناء التنفيذ');
            }
        });
    });
});
</script>
<?php include __DIR__ . '/../includes/footer.php';
