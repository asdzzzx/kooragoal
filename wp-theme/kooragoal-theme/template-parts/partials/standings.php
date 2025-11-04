<?php
$data = $args['data'] ?? [];

if ( is_wp_error( $data ) ) {
    echo '<p>' . esc_html( $data->get_error_message() ) . '</p>';
    return;
}

$rows = kooragoal_prepare_standings_rows( $data );
?>
<table class="kooragoal-table standings-table">
    <thead>
        <tr>
            <th><?php esc_html_e( 'المركز', 'kooragoal' ); ?></th>
            <th><?php esc_html_e( 'الفريق', 'kooragoal' ); ?></th>
            <th><?php esc_html_e( 'لعب', 'kooragoal' ); ?></th>
            <th><?php esc_html_e( 'فوز', 'kooragoal' ); ?></th>
            <th><?php esc_html_e( 'تعادل', 'kooragoal' ); ?></th>
            <th><?php esc_html_e( 'خسارة', 'kooragoal' ); ?></th>
            <th><?php esc_html_e( 'النقاط', 'kooragoal' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if ( empty( $rows ) ) : ?>
            <tr>
                <td colspan="7"><?php esc_html_e( 'لا تتوفر بيانات حالياً.', 'kooragoal' ); ?></td>
            </tr>
        <?php else : ?>
            <?php foreach ( $rows as $row ) : ?>
                <tr>
                    <td><?php echo esc_html( $row['rank'] ?? '' ); ?></td>
                    <td class="team-name">
                        <?php if ( ! empty( $row['team']['logo'] ) ) : ?>
                            <img src="<?php echo esc_url( $row['team']['logo'] ); ?>" alt="" loading="lazy">
                        <?php endif; ?>
                        <?php echo esc_html( $row['team']['name'] ?? '' ); ?>
                    </td>
                    <td><?php echo esc_html( $row['all']['played'] ?? '' ); ?></td>
                    <td><?php echo esc_html( $row['all']['win'] ?? '' ); ?></td>
                    <td><?php echo esc_html( $row['all']['draw'] ?? '' ); ?></td>
                    <td><?php echo esc_html( $row['all']['lose'] ?? '' ); ?></td>
                    <td><?php echo esc_html( $row['points'] ?? '' ); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
