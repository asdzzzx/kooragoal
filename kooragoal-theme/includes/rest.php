<?php
/**
 * نقاط REST المخصصة للتحديثات اللحظية.
 */

add_action( 'rest_api_init', 'kooragoal_register_rest_routes' );
function kooragoal_register_rest_routes() {
    register_rest_route(
        'kooragoal/v1',
        '/fixtures',
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'kooragoal_rest_get_fixtures',
            'permission_callback' => '__return_true',
            'args'                => [
                'date'    => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'leagues' => [
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]
    );

    register_rest_route(
        'kooragoal/v1',
        '/standings/(?P<league_id>\d+)',
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'kooragoal_rest_get_standings',
            'permission_callback' => '__return_true',
        ]
    );

    register_rest_route(
        'kooragoal/v1',
        '/scorers/(?P<league_id>\d+)',
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'kooragoal_rest_get_scorers',
            'permission_callback' => '__return_true',
        ]
    );

    register_rest_route(
        'kooragoal/v1',
        '/match/(?P<fixture_id>\d+)/events',
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'kooragoal_rest_get_events',
            'permission_callback' => '__return_true',
        ]
    );

    register_rest_route(
        'kooragoal/v1',
        '/match/(?P<fixture_id>\d+)/lineups',
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'kooragoal_rest_get_lineups',
            'permission_callback' => '__return_true',
        ]
    );

    register_rest_route(
        'kooragoal/v1',
        '/match/(?P<fixture_id>\d+)/statistics',
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'kooragoal_rest_get_statistics',
            'permission_callback' => '__return_true',
        ]
    );
}

function kooragoal_rest_get_fixtures( WP_REST_Request $request ) {
    $date_param   = $request->get_param( 'date' );
    $league_param = $request->get_param( 'leagues' );

    $date = wp_strip_all_tags( $date_param );
    if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
        $date = gmdate( 'Y-m-d' );
    }
    $leagues = $league_param ? array_filter( array_map( 'absint', explode( ',', $league_param ) ) ) : kooragoal_get_default_league_ids();

    $fixtures = kooragoal_get_fixtures( $leagues, $date );
    $payload  = array_map( 'kooragoal_extract_fixture_data', $fixtures );

    return rest_ensure_response( [
        'fixtures' => $payload,
    ] );
}

function kooragoal_rest_get_standings( WP_REST_Request $request ) {
    $league_id = (int) $request['league_id'];
    $data      = kooragoal_get_standings( $league_id );

    if ( is_wp_error( $data ) ) {
        return $data;
    }

    return rest_ensure_response( [
        'rows' => kooragoal_prepare_standings_rows( $data ),
    ] );
}

function kooragoal_rest_get_scorers( WP_REST_Request $request ) {
    $league_id = (int) $request['league_id'];
    $data      = kooragoal_get_top_scorers( $league_id );

    if ( is_wp_error( $data ) ) {
        return $data;
    }

    return rest_ensure_response( [
        'rows' => kooragoal_prepare_scorers_rows( $data ),
    ] );
}

function kooragoal_rest_get_events( WP_REST_Request $request ) {
    $fixture_id = (int) $request['fixture_id'];
    $data       = kooragoal_get_fixture_events( $fixture_id );

    if ( is_wp_error( $data ) ) {
        return $data;
    }

    return rest_ensure_response( $data );
}

function kooragoal_rest_get_lineups( WP_REST_Request $request ) {
    $fixture_id = (int) $request['fixture_id'];
    $data       = kooragoal_get_fixture_lineups( $fixture_id );

    if ( is_wp_error( $data ) ) {
        return $data;
    }

    return rest_ensure_response( $data );
}

function kooragoal_rest_get_statistics( WP_REST_Request $request ) {
    $fixture_id = (int) $request['fixture_id'];
    $data       = kooragoal_get_fixture_statistics( $fixture_id );

    if ( is_wp_error( $data ) ) {
        return $data;
    }

    return rest_ensure_response( $data );
}
