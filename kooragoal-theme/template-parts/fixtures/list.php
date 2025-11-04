<?php
$fixtures = $args['fixtures'] ?? [];
$target_date = $args['date'] ?? wp_date( 'Y-m-d' );
?>
<div class="fixtures-list" data-current-date="<?php echo esc_attr( $target_date ); ?>">
    <?php if ( empty( $fixtures ) ) : ?>
        <p class="fixtures-list__empty"><?php esc_html_e( 'لا توجد مباريات مجدولة لهذا اليوم.', 'kooragoal' ); ?></p>
    <?php else : ?>
        <div class="fixtures-grid">
            <?php foreach ( $fixtures as $fixture ) : ?>
                <?php get_template_part( 'template-parts/fixtures/card', null, [ 'fixture' => $fixture ] ); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
