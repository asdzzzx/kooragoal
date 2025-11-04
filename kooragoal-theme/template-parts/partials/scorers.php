<?php
$data = $args['data'] ?? [];

if ( is_wp_error( $data ) ) {
    echo '<p>' . esc_html( $data->get_error_message() ) . '</p>';
    return;
}

$rows = kooragoal_prepare_scorers_rows( $data );
?>
<table class="kooragoal-table scorers-table">
    <thead>
        <tr>
            <th><?php esc_html_e( 'المركز', 'kooragoal' ); ?></th>
            <th><?php esc_html_e( 'اللاعب', 'kooragoal' ); ?></th>
            <th><?php esc_html_e( 'الفريق', 'kooragoal' ); ?></th>
            <th><?php esc_html_e( 'الأهداف', 'kooragoal' ); ?></th>
            <th><?php esc_html_e( 'التمريرات الحاسمة', 'kooragoal' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if ( empty( $rows ) ) : ?>
            <tr>
                <td colspan="5"><?php esc_html_e( 'لا تتوفر بيانات حالياً.', 'kooragoal' ); ?></td>
            </tr>
        <?php else : ?>
            <?php foreach ( $rows as $index => $row ) :
                $player = $row['player'] ?? [];
                $statistics = $row['statistics'][0] ?? [];
                $team = $statistics['team'] ?? [];
                $goals = $statistics['goals']['total'] ?? 0;
                $assists = $statistics['goals']['assists'] ?? 0;
            ?>
                <tr>
                    <td><?php echo esc_html( $index + 1 ); ?></td>
                    <td class="player-name">
                        <?php if ( ! empty( $player['photo'] ) ) : ?>
                            <img src="<?php echo esc_url( $player['photo'] ); ?>" alt="<?php echo esc_attr( $player['name'] ?? '' ); ?>" loading="lazy">
                        <?php endif; ?>
                        <?php echo esc_html( $player['name'] ?? '' ); ?>
                    </td>
                    <td><?php echo esc_html( $team['name'] ?? '' ); ?></td>
                    <td><?php echo esc_html( $goals ); ?></td>
                    <td><?php echo esc_html( $assists ); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
