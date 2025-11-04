<?php
$data = $args['statistics'] ?? [];

if ( is_wp_error( $data ) ) {
    echo '<p>' . esc_html( $data->get_error_message() ) . '</p>';
    return;
}

$statistics = $data['response'] ?? [];
?>
<div class="match-statistics">
    <?php if ( empty( $statistics ) ) : ?>
        <p><?php esc_html_e( 'لا توجد إحصائيات متاحة حالياً.', 'kooragoal' ); ?></p>
    <?php else :
        $home = $statistics[0] ?? [];
        $away = $statistics[1] ?? [];
        $metrics = [];
        foreach ( $home['statistics'] ?? [] as $item ) {
            if ( empty( $item['type'] ) ) {
                continue;
            }
            $metrics[ $item['type'] ] = [
                'home' => $item['value'] ?? 0,
                'away' => null,
            ];
        }
        foreach ( $away['statistics'] ?? [] as $item ) {
            if ( empty( $item['type'] ) ) {
                continue;
            }
            if ( ! isset( $metrics[ $item['type'] ] ) ) {
                $metrics[ $item['type'] ] = [ 'home' => null, 'away' => $item['value'] ?? 0 ];
            } else {
                $metrics[ $item['type'] ]['away'] = $item['value'] ?? 0;
            }
        }
    ?>
        <table class="kooragoal-table statistics-table">
            <thead>
                <tr>
                    <th><?php echo esc_html( $home['team']['name'] ?? __( 'الفريق المضيف', 'kooragoal' ) ); ?></th>
                    <th><?php esc_html_e( 'الإحصائية', 'kooragoal' ); ?></th>
                    <th><?php echo esc_html( $away['team']['name'] ?? __( 'الفريق الضيف', 'kooragoal' ) ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $metrics as $label => $values ) : ?>
                    <tr>
                        <td><?php echo esc_html( $values['home'] ?? '0' ); ?></td>
                        <td><?php echo esc_html( $label ); ?></td>
                        <td><?php echo esc_html( $values['away'] ?? '0' ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
