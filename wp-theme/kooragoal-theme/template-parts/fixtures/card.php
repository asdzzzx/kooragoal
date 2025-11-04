<?php
if ( empty( $args['fixture'] ) ) {
    return;
}

$data         = kooragoal_extract_fixture_data( $args['fixture'] );
$home         = $data['teams']['home'];
$away         = $data['teams']['away'];
$status_label = $data['status_label'];
$card_bg      = KOORAGOAL_THEME_URL . '/assets/images/match-card-bg.png';
$match_page   = (int) get_option( 'kooragoal_match_page_id', 0 );
$details_url  = $match_page ? add_query_arg( 'fixture', $data['id'], get_permalink( $match_page ) ) : '#';
?>
<article class="fixture-card" data-fixture-id="<?php echo esc_attr( $data['id'] ); ?>">
    <header class="fixture-card__header">
        <?php if ( ! empty( $data['league']['logo'] ) ) : ?>
            <img src="<?php echo esc_url( $data['league']['logo'] ); ?>" alt="<?php echo esc_attr( $data['league']['name'] ); ?>" class="fixture-card__league-logo" loading="lazy">
        <?php endif; ?>
        <div class="fixture-card__meta">
            <span class="fixture-card__league-name"><?php echo esc_html( $data['league']['name'] ); ?></span>
            <span class="fixture-card__status badge"><?php echo esc_html( $status_label ); ?></span>
        </div>
        <time datetime="<?php echo esc_attr( $data['date'] . 'T' . $data['time'] ); ?>" class="fixture-card__time"><?php echo esc_html( $data['time'] ); ?></time>
    </header>
    <div class="fixture-card__body" style="background-image: url('<?php echo esc_url( $card_bg ); ?>');">
        <div class="fixture-card__team fixture-card__team--home">
            <?php if ( ! empty( $home['logo'] ) ) : ?>
                <img src="<?php echo esc_url( $home['logo'] ); ?>" alt="<?php echo esc_attr( $home['name'] ?? '' ); ?>" class="fixture-card__team-logo" loading="lazy">
            <?php endif; ?>
            <span class="fixture-card__team-name"><?php echo esc_html( $home['name'] ?? '' ); ?></span>
        </div>
        <div class="fixture-card__score">
            <span class="fixture-card__score-value"><?php echo esc_html( $data['score']['home'] ); ?></span>
            <span class="fixture-card__score-separator">-</span>
            <span class="fixture-card__score-value"><?php echo esc_html( $data['score']['away'] ); ?></span>
        </div>
        <div class="fixture-card__team fixture-card__team--away">
            <?php if ( ! empty( $away['logo'] ) ) : ?>
                <img src="<?php echo esc_url( $away['logo'] ); ?>" alt="<?php echo esc_attr( $away['name'] ?? '' ); ?>" class="fixture-card__team-logo" loading="lazy">
            <?php endif; ?>
            <span class="fixture-card__team-name"><?php echo esc_html( $away['name'] ?? '' ); ?></span>
        </div>
    </div>
    <footer class="fixture-card__footer">
        <a class="fixture-card__details<?php echo $match_page ? '' : ' fixture-card__details--disabled'; ?>" href="<?php echo esc_url( $details_url ); ?>"<?php echo $match_page ? '' : ' aria-disabled="true"'; ?>>
            <?php esc_html_e( 'عرض تفاصيل المباراة', 'kooragoal' ); ?>
        </a>
    </footer>
</article>
