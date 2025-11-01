<?php
if (!function_exists('renderAdminSidebar')) {
    function renderAdminSidebar(string $active = ''): string
    {
        $items = [
            'dashboard' => ['label' => 'لوحة التحكم', 'href' => '/admin/index.php', 'icon' => 'bi-speedometer2'],
            'fixtures' => ['label' => 'المباريات', 'href' => '/admin/fixtures.php', 'icon' => 'bi-calendar-event'],
            'live' => ['label' => 'المباريات الجارية', 'href' => '/admin/live_fixtures.php', 'icon' => 'bi-broadcast-pin'],
            'leagues' => ['label' => 'الدوريات', 'href' => '/admin/leagues.php', 'icon' => 'bi-trophy'],
            'teams' => ['label' => 'الفرق', 'href' => '/admin/teams.php', 'icon' => 'bi-people'],
            'standings' => ['label' => 'الترتيب', 'href' => '/admin/standings.php', 'icon' => 'bi-table'],
            'scorers' => ['label' => 'الهدافين', 'href' => '/admin/scorers.php', 'icon' => 'bi-list-ol'],
            'statistics' => ['label' => 'الإحصائيات', 'href' => '/admin/statistics.php', 'icon' => 'bi-graph-up'],
            'match_details' => ['label' => 'تفاصيل المباريات', 'href' => '/admin/match_details.php', 'icon' => 'bi-card-list'],
            'updates' => ['label' => 'إدارة التحديثات', 'href' => '/admin/updates.php', 'icon' => 'bi-arrow-repeat'],
            'admins' => ['label' => 'المسؤولون', 'href' => '/admin/users.php', 'icon' => 'bi-shield-lock'],
            'logs' => ['label' => 'السجلات', 'href' => '/admin/logs.php', 'icon' => 'bi-journal-text'],
            'settings' => ['label' => 'الإعدادات', 'href' => '/admin/settings.php', 'icon' => 'bi-gear'],
            'profile' => ['label' => 'الملف الشخصي', 'href' => '/admin/profile.php', 'icon' => 'bi-person-circle'],
        ];

        ob_start();
        ?>
        <aside class="col-xl-2 col-lg-3 mb-4">
            <div class="list-group shadow-sm sticky-top">
                <?php foreach ($items as $key => $item): ?>
                    <a class="list-group-item list-group-item-action d-flex align-items-center gap-2 <?php echo $active === $key ? 'active' : ''; ?>"
                       href="<?php echo htmlspecialchars($item['href']); ?>">
                        <?php if (!empty($item['icon'])): ?>
                            <i class="bi <?php echo htmlspecialchars($item['icon']); ?>"></i>
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($item['label']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>
        <?php
        return ob_get_clean();
    }
}
