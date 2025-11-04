<?php
$data    = $args['lineups'] ?? [];

if ( is_wp_error( $data ) ) {
    echo '<p>' . esc_html( $data->get_error_message() ) . '</p>';
    return;
}

$lineups = $data['response'] ?? [];
$pitch   = KOORAGOAL_THEME_URL . '/assets/images/lineup-pitch.png';
?>
<div class="match-lineups">
    <?php if ( empty( $lineups ) ) : ?>
        <p><?php esc_html_e( 'لم يتم الإعلان عن التشكيلة بعد.', 'kooragoal' ); ?></p>
    <?php else : ?>
        <?php foreach ( $lineups as $lineup ) :
            $team    = $lineup['team'] ?? [];
            $coach   = $lineup['coach']['name'] ?? '';
            $formation = $lineup['formation'] ?? '';
            $players = $lineup['startXI'] ?? [];
        ?>
            <section class="lineup">
                <header class="lineup__header">
                    <div class="lineup__team">
                        <?php if ( ! empty( $team['logo'] ) ) : ?>
                            <img src="<?php echo esc_url( $team['logo'] ); ?>" alt="<?php echo esc_attr( $team['name'] ?? '' ); ?>" loading="lazy">
                        <?php endif; ?>
                        <div>
                            <h3><?php echo esc_html( $team['name'] ?? '' ); ?></h3>
                            <?php if ( $formation ) : ?>
                                <span class="lineup__formation"><?php echo esc_html( $formation ); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ( $coach ) : ?>
                        <div class="lineup__coach"><?php printf( esc_html__( 'المدرب: %s', 'kooragoal' ), esc_html( $coach ) ); ?></div>
                    <?php endif; ?>
                </header>
                <div class="lineup__pitch" style="background-image: url('<?php echo esc_url( $pitch ); ?>');">
                    <?php if ( empty( $players ) ) : ?>
                        <p><?php esc_html_e( 'لا تتوفر بيانات اللاعبين.', 'kooragoal' ); ?></p>
                    <?php else : ?>
                        <div class="lineup__players">
                            <?php foreach ( $players as $slot ) :
                                $player = $slot['player'] ?? [];
                                $number = $player['number'] ?? '';
                                $name   = $player['name'] ?? '';
                                $photo  = $player['photo'] ?? '';
                            ?>
                                <div class="lineup__player" data-number="<?php echo esc_attr( $number ); ?>">
                                    <?php if ( $photo ) : ?>
                                        <img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
                                    <?php endif; ?>
                                    <span class="lineup__player-number"><?php echo esc_html( $number ); ?></span>
                                    <span class="lineup__player-name"><?php echo esc_html( $name ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
