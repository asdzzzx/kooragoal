<?php
/**
 * الوظائف الأساسية لقالب Kooragoal Live Football
 */

define( 'KOORAGOAL_THEME_VERSION', '1.0.0' );

define( 'KOORAGOAL_THEME_PATH', get_template_directory() );
define( 'KOORAGOAL_THEME_URL', get_template_directory_uri() );

require_once KOORAGOAL_THEME_PATH . '/includes/api.php';
require_once KOORAGOAL_THEME_PATH . '/includes/options.php';
require_once KOORAGOAL_THEME_PATH . '/includes/template-tags.php';
require_once KOORAGOAL_THEME_PATH . '/includes/rest.php';

add_action( 'after_setup_theme', 'kooragoal_theme_setup' );
function kooragoal_theme_setup() {
    load_theme_textdomain( 'kooragoal', KOORAGOAL_THEME_PATH . '/languages' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'custom-logo', [ 'height' => 60, 'width' => 200, 'flex-height' => true, 'flex-width' => true ] );

    register_nav_menus(
        [
            'primary' => __( 'القائمة الرئيسية', 'kooragoal' ),
            'footer'  => __( 'القائمة السفلية', 'kooragoal' ),
        ]
    );
}

add_action( 'widgets_init', 'kooragoal_register_sidebars' );
function kooragoal_register_sidebars() {
    register_sidebar(
        [
            'name'          => __( 'الشريط الجانبي', 'kooragoal' ),
            'id'            => 'sidebar-1',
            'description'   => __( 'منطقة الودجات الرئيسية للمقالات والصفحات.', 'kooragoal' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ]
    );
}

add_action( 'wp_enqueue_scripts', 'kooragoal_enqueue_assets' );
function kooragoal_enqueue_assets() {
    wp_enqueue_style( 'kooragoal-theme', KOORAGOAL_THEME_URL . '/assets/css/theme.css', [], KOORAGOAL_THEME_VERSION );

    wp_enqueue_script( 'kooragoal-live-updates', KOORAGOAL_THEME_URL . '/assets/js/live-updates.js', [ 'jquery' ], KOORAGOAL_THEME_VERSION, true );

    $match_page = (int) get_option( 'kooragoal_match_page_id', 0 );
    $match_page_url = $match_page ? get_permalink( $match_page ) : '';

    $settings = [
        'restUrl'           => esc_url_raw( rest_url( 'kooragoal/v1' ) ),
        'nonce'             => wp_create_nonce( 'wp_rest' ),
        'refreshInterval'   => (int) apply_filters( 'kooragoal_refresh_interval', 15000 ),
        'defaultLeagueIds'  => kooragoal_get_default_league_ids(),
        'matchPage'         => $match_page_url,
        'themeUrl'          => KOORAGOAL_THEME_URL,
        'translations'      => [
            'loading'   => __( 'جار التحديث...', 'kooragoal' ),
            'noMatches' => __( 'لا توجد مباريات مجدولة لهذا اليوم.', 'kooragoal' ),
            'details'   => __( 'عرض تفاصيل المباراة', 'kooragoal' ),
            'assist'    => __( 'بمساعدة %s', 'kooragoal' ),
        ],
    ];

    wp_localize_script( 'kooragoal-live-updates', 'kooragoalSettings', $settings );
}

add_filter( 'body_class', 'kooragoal_append_body_classes' );
function kooragoal_append_body_classes( array $classes ): array {
    $classes[] = 'kooragoal-theme';
    return $classes;
}
