<?php
get_header();

$today_gmt = gmdate( 'Y-m-d' );
$requested_date = isset( $_GET['date'] ) ? sanitize_text_field( wp_unslash( $_GET['date'] ) ) : $today_gmt;
if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $requested_date ) ) {
    $requested_date = $today_gmt;
}

$league_ids = kooragoal_get_default_league_ids();
$fixtures   = kooragoal_get_fixtures( $league_ids, $requested_date );
$primary_league = $league_ids[0] ?? 0;
$standings_data = $primary_league ? kooragoal_get_standings( $primary_league ) : [];
$scorers_data   = $primary_league ? kooragoal_get_top_scorers( $primary_league ) : [];
?>
<section class="hero">
    <div class="container">
        <h1 class="hero__title"><?php esc_html_e( 'جدول المباريات الحية', 'kooragoal' ); ?></h1>
        <p class="hero__subtitle"><?php esc_html_e( 'تحديث تلقائي كل 15 ثانية بدون إعادة تحميل الصفحة.', 'kooragoal' ); ?></p>
        <div class="hero__calendar" data-current-date="<?php echo esc_attr( $requested_date ); ?>" data-leagues="<?php echo esc_attr( implode( ',', $league_ids ) ); ?>">
            <button type="button" class="calendar-nav calendar-nav--prev" aria-label="<?php esc_attr_e( 'اليوم السابق', 'kooragoal' ); ?>" data-direction="prev">&larr;</button>
            <div class="calendar-display">
                <input type="date" value="<?php echo esc_attr( $requested_date ); ?>" class="calendar-display__input">
                <span class="calendar-display__label"><?php echo esc_html( $requested_date ); ?></span>
            </div>
            <button type="button" class="calendar-nav calendar-nav--next" aria-label="<?php esc_attr_e( 'اليوم التالي', 'kooragoal' ); ?>" data-direction="next">&rarr;</button>
        </div>
    </div>
</section>
<section class="fixtures" id="fixtures" data-refresh="fixtures">
    <div class="container">
        <h2 class="section-title"><?php esc_html_e( 'مباريات اليوم', 'kooragoal' ); ?></h2>
        <div class="fixtures__swipe-hint"><?php esc_html_e( 'اسحب لليسار أو اليمين لتغيير اليوم على الهواتف.', 'kooragoal' ); ?></div>
        <div class="fixtures__wrapper" data-swipe-container>
            <?php get_template_part( 'template-parts/fixtures/list', null, [
                'fixtures' => $fixtures,
                'date'     => $requested_date,
            ] ); ?>
        </div>
    </div>
</section>
<section class="league-highlights" data-refresh="league-summary" data-league="<?php echo esc_attr( $primary_league ); ?>">
    <div class="container league-highlights__grid">
        <div class="league-highlights__standings">
            <h2 class="section-title"><?php esc_html_e( 'الترتيب', 'kooragoal' ); ?></h2>
            <div class="ajax-fragment" data-fragment="standings">
                <?php get_template_part( 'template-parts/partials/standings', null, [ 'data' => $standings_data ] ); ?>
            </div>
        </div>
        <div class="league-highlights__scorers">
            <h2 class="section-title"><?php esc_html_e( 'قائمة الهدافين', 'kooragoal' ); ?></h2>
            <div class="ajax-fragment" data-fragment="scorers">
                <?php get_template_part( 'template-parts/partials/scorers', null, [ 'data' => $scorers_data ] ); ?>
            </div>
        </div>
    </div>
</section>
<?php
get_footer();
