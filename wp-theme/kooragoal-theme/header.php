<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header">
    <div class="container site-header__inner">
        <div class="site-branding">
            <?php if ( has_custom_logo() ) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-title"><?php bloginfo( 'name' ); ?></a>
                <p class="site-description"><?php bloginfo( 'description' ); ?></p>
            <?php endif; ?>
        </div>
        <nav class="primary-navigation" aria-label="<?php esc_attr_e( 'القائمة الرئيسية', 'kooragoal' ); ?>">
            <?php
            wp_nav_menu(
                [
                    'theme_location' => 'primary',
                    'menu_class'     => 'primary-menu',
                    'fallback_cb'    => '__return_false',
                ]
            );
            ?>
        </nav>
    </div>
</header>
<main class="site-main">
