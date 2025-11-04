</main>
<footer class="site-footer">
    <div class="container site-footer__inner">
        <div class="footer-widgets">
            <?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
                <?php dynamic_sidebar( 'sidebar-1' ); ?>
            <?php endif; ?>
        </div>
        <nav class="footer-navigation" aria-label="<?php esc_attr_e( 'القائمة السفلية', 'kooragoal' ); ?>">
            <?php
            wp_nav_menu(
                [
                    'theme_location' => 'footer',
                    'menu_class'     => 'footer-menu',
                    'fallback_cb'    => '__return_false',
                ]
            );
            ?>
        </nav>
        <p class="site-footer__copyright">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
