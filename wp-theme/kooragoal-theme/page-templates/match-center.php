<?php
/**
 * Template Name: Kooragoal Match Center
 */
get_header();

$fixture_id = isset( $_GET['fixture'] ) ? absint( $_GET['fixture'] ) : 0;
$fixture_details = $fixture_id ? kooragoal_get_fixture_details( $fixture_id ) : [];
$fixture_data = [];
if ( ! empty( $fixture_details['response'][0] ?? [] ) ) {
    $fixture_data = $fixture_details['response'][0];
}
?>
<div class="container match-center" data-fixture="<?php echo esc_attr( $fixture_id ); ?>">
    <?php if ( ! $fixture_id || empty( $fixture_data ) ) : ?>
        <p><?php esc_html_e( 'الرجاء اختيار مباراة صحيحة من الجدول.', 'kooragoal' ); ?></p>
    <?php else :
        $fixture = $fixture_data['fixture'] ?? [];
        $teams   = $fixture_data['teams'] ?? [];
        $goals   = $fixture_data['goals'] ?? [];
        $league  = $fixture_data['league'] ?? [];
        ?>
        <header class="match-center__header" style="background-image: url('<?php echo esc_url( KOORAGOAL_THEME_URL . '/assets/images/match-card-bg.png' ); ?>');">
            <div class="match-center__league">
                <?php if ( ! empty( $league['logo'] ) ) : ?>
                    <img src="<?php echo esc_url( $league['logo'] ); ?>" alt="<?php echo esc_attr( $league['name'] ?? '' ); ?>" loading="lazy">
                <?php endif; ?>
                <span><?php echo esc_html( $league['name'] ?? '' ); ?></span>
            </div>
            <div class="match-center__score">
                <div class="match-center__team">
                    <?php if ( ! empty( $teams['home']['logo'] ) ) : ?>
                        <img src="<?php echo esc_url( $teams['home']['logo'] ); ?>" alt="<?php echo esc_attr( $teams['home']['name'] ?? '' ); ?>" loading="lazy">
                    <?php endif; ?>
                    <span><?php echo esc_html( $teams['home']['name'] ?? '' ); ?></span>
                </div>
                <div class="match-center__result">
                    <span><?php echo esc_html( $goals['home'] ?? 0 ); ?></span>
                    <span>-</span>
                    <span><?php echo esc_html( $goals['away'] ?? 0 ); ?></span>
                </div>
                <div class="match-center__team">
                    <?php if ( ! empty( $teams['away']['logo'] ) ) : ?>
                        <img src="<?php echo esc_url( $teams['away']['logo'] ); ?>" alt="<?php echo esc_attr( $teams['away']['name'] ?? '' ); ?>" loading="lazy">
                    <?php endif; ?>
                    <span><?php echo esc_html( $teams['away']['name'] ?? '' ); ?></span>
                </div>
            </div>
            <div class="match-center__meta">
                <span><?php echo esc_html( $fixture['status']['long'] ?? '' ); ?></span>
                <span><?php echo esc_html( wp_date( 'Y-m-d H:i', (int) ( $fixture['timestamp'] ?? time() ), new DateTimeZone( 'UTC' ) ) ); ?></span>
                <?php if ( ! empty( $fixture['venue']['name'] ) ) : ?>
                    <span><?php echo esc_html( $fixture['venue']['name'] ); ?></span>
                <?php endif; ?>
            </div>
        </header>
        <div class="match-center__grid">
            <section class="match-center__section" id="match-statistics">
                <h2 class="section-title"><?php esc_html_e( 'الإحصائيات', 'kooragoal' ); ?></h2>
                <div class="ajax-fragment" data-fragment="statistics">
                    <?php get_template_part( 'template-parts/partials/statistics', null, [ 'statistics' => kooragoal_get_fixture_statistics( $fixture_id ) ] ); ?>
                </div>
            </section>
            <section class="match-center__section" id="match-events">
                <h2 class="section-title"><?php esc_html_e( 'الأحداث', 'kooragoal' ); ?></h2>
                <div class="ajax-fragment" data-fragment="events">
                    <?php get_template_part( 'template-parts/partials/events', null, [ 'events' => kooragoal_get_fixture_events( $fixture_id ) ] ); ?>
                </div>
            </section>
            <section class="match-center__section" id="match-lineups">
                <h2 class="section-title"><?php esc_html_e( 'التشكيلة', 'kooragoal' ); ?></h2>
                <div class="ajax-fragment" data-fragment="lineups">
                    <?php get_template_part( 'template-parts/partials/lineups', null, [ 'lineups' => kooragoal_get_fixture_lineups( $fixture_id ) ] ); ?>
                </div>
            </section>
        </div>
    <?php endif; ?>
</div>
<?php
get_footer();
