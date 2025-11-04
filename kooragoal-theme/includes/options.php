<?php
/**
 * لوحة إعدادات بسيطة للقالب.
 */

add_action( 'admin_menu', 'kooragoal_register_options_page' );
function kooragoal_register_options_page() {
    add_theme_page(
        __( 'إعدادات Kooragoal', 'kooragoal' ),
        __( 'إعدادات Kooragoal', 'kooragoal' ),
        'manage_options',
        'kooragoal-settings',
        'kooragoal_render_options_page'
    );
}

add_action( 'admin_init', 'kooragoal_register_settings' );
function kooragoal_register_settings() {
    register_setting( 'kooragoal_settings', 'kooragoal_api_base', [
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default'           => 'https://yacine--tv.live/api_complete.php',
    ] );

    register_setting( 'kooragoal_settings', 'kooragoal_default_leagues', [
        'type'              => 'string',
        'sanitize_callback' => 'kooragoal_sanitize_league_list',
        'default'           => '2,39,140,135,78,61',
    ] );

    register_setting( 'kooragoal_settings', 'kooragoal_match_page_id', [
        'type'              => 'integer',
        'sanitize_callback' => 'absint',
        'default'           => 0,
    ] );

    add_settings_section(
        'kooragoal_api_section',
        __( 'إعدادات واجهة البرمجة', 'kooragoal' ),
        '__return_false',
        'kooragoal-settings'
    );

    add_settings_field(
        'kooragoal_api_base',
        __( 'رابط ملف الـ API', 'kooragoal' ),
        'kooragoal_render_api_base_field',
        'kooragoal-settings',
        'kooragoal_api_section'
    );

    add_settings_field(
        'kooragoal_default_leagues',
        __( 'البطولات الافتراضية', 'kooragoal' ),
        'kooragoal_render_default_leagues_field',
        'kooragoal-settings',
        'kooragoal_api_section'
    );

    add_settings_field(
        'kooragoal_match_page_id',
        __( 'صفحة تفاصيل المباراة', 'kooragoal' ),
        'kooragoal_render_match_page_field',
        'kooragoal-settings',
        'kooragoal_api_section'
    );
}

function kooragoal_sanitize_league_list( string $value ): string {
    $ids = array_filter( array_map( 'absint', explode( ',', $value ) ) );
    return implode( ',', $ids );
}

function kooragoal_render_api_base_field() {
    $value = esc_url( kooragoal_get_api_base_url() );
    echo '<input type="url" name="kooragoal_api_base" value="' . esc_attr( $value ) . '" class="regular-text ltr">';
    echo '<p class="description">' . esc_html__( 'أدخل الرابط الكامل لملف api_complete.php أو ما يعادله.', 'kooragoal' ) . '</p>';
}

function kooragoal_render_default_leagues_field() {
    $value = esc_attr( get_option( 'kooragoal_default_leagues', '2,39,140,135,78,61' ) );
    echo '<input type="text" name="kooragoal_default_leagues" value="' . $value . '" class="regular-text ltr">';
    echo '<p class="description">' . esc_html__( 'أدخل أرقام الدوريات مفصولة بفاصلة. سيتم عرضها في الصفحة الرئيسية.', 'kooragoal' ) . '</p>';
}

function kooragoal_render_match_page_field() {
    $page_id = (int) get_option( 'kooragoal_match_page_id', 0 );
    wp_dropdown_pages(
        [
            'name'              => 'kooragoal_match_page_id',
            'selected'          => $page_id,
            'show_option_none'  => __( 'اختر الصفحة', 'kooragoal' ),
            'option_none_value' => 0,
        ]
    );
    echo '<p class="description">' . esc_html__( 'حدد صفحة تستخدم قالب تفاصيل المباراة (Match Center). سيتم تمرير معرف المباراة عبر ?fixture=ID.', 'kooragoal' ) . '</p>';
}

function kooragoal_render_options_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'إعدادات Kooragoal', 'kooragoal' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'kooragoal_settings' );
            do_settings_sections( 'kooragoal-settings' );
            submit_button();
            ?>
        </form>
        <p><?php esc_html_e( 'أضف الصور التالية في مجلد القالب assets/images:', 'kooragoal' ); ?></p>
        <ul>
            <li><code>lineup-pitch.png</code></li>
            <li><code>match-card-bg.png</code></li>
            <li><code>logo.svg</code></li>
            <li><code>icon-yellow-card.svg</code>, <code>icon-red-card.svg</code>, <code>icon-goal.svg</code>, <code>icon-substitution.svg</code></li>
        </ul>
    </div>
    <?php
}
