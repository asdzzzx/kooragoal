<?php
$data = $args['events'] ?? [];

if ( is_wp_error( $data ) ) {
    echo '<p>' . esc_html( $data->get_error_message() ) . '</p>';
    return;
}

$events = $data['response'] ?? [];
?>
<div class="match-events">
    <?php if ( empty( $events ) ) : ?>
        <p><?php esc_html_e( 'لا توجد أحداث متاحة لهذه المباراة.', 'kooragoal' ); ?></p>
    <?php else : ?>
        <ul class="match-events__list">
            <?php foreach ( $events as $event ) :
                $player = $event['player'] ?? [];
                $assist = $event['assist'] ?? [];
                $team   = $event['team'] ?? [];
                $time   = $event['time']['elapsed'] ?? '';
                $type   = $event['type'] ?? '';
                $detail = $event['detail'] ?? '';
                $icon   = kooragoal_format_event_icon( $type, $detail );
            ?>
                <li class="match-events__item">
                    <div class="match-events__minute"><?php echo esc_html( $time ); ?>'</div>
                    <div class="match-events__icon"><?php echo $icon; ?></div>
                    <div class="match-events__content">
                        <div class="match-events__player">
                            <?php if ( ! empty( $player['photo'] ) ) : ?>
                                <img src="<?php echo esc_url( $player['photo'] ); ?>" alt="<?php echo esc_attr( $player['name'] ?? '' ); ?>" loading="lazy">
                            <?php endif; ?>
                            <span><?php echo esc_html( $player['name'] ?? '' ); ?></span>
                        </div>
                        <?php if ( ! empty( $assist['name'] ) ) : ?>
                            <div class="match-events__assist"><?php printf( /* translators: %s player name */ esc_html__( 'بمساعدة %s', 'kooragoal' ), esc_html( $assist['name'] ) ); ?></div>
                        <?php endif; ?>
                        <div class="match-events__team"><?php echo esc_html( $team['name'] ?? '' ); ?></div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
