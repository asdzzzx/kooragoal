<?php
/**
 * وظائف التعامل مع واجهة الواجهة البرمجية للمباريات.
 */

if ( ! function_exists( 'kooragoal_get_api_base_url' ) ) {
    function kooragoal_get_api_base_url(): string {
        $base = get_option( 'kooragoal_api_base', 'https://yacine--tv.live/api_complete.php' );
        return untrailingslashit( $base );
    }
}

if ( ! function_exists( 'kooragoal_get_default_league_ids' ) ) {
    function kooragoal_get_default_league_ids(): array {
        $league_ids = get_option( 'kooragoal_default_leagues', '2,39,140,135,78,61' );
        $ids        = array_filter( array_map( 'absint', explode( ',', $league_ids ) ) );

        return $ids ?: [ 2, 39, 140, 135, 78, 61 ];
    }
}

if ( ! function_exists( 'kooragoal_build_api_url' ) ) {
    function kooragoal_build_api_url( string $action, array $query = [] ): string {
        $base = kooragoal_get_api_base_url();
        $args = array_merge( [ 'action' => $action ], $query );
        return add_query_arg( $args, $base );
    }
}

if ( ! function_exists( 'kooragoal_fetch_api' ) ) {
    function kooragoal_fetch_api( string $action, array $query = [], int $cache_ttl = 12 ) {
        $url = kooragoal_build_api_url( $action, $query );
        $key = 'kooragoal_' . md5( $url );

        $cached = get_transient( $key );
        if ( false !== $cached ) {
            return $cached;
        }

        $response = wp_remote_get(
            $url,
            [
                'timeout' => 15,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( null === $data ) {
            return new WP_Error( 'kooragoal_invalid_response', __( 'تعذر قراءة بيانات الواجهة البرمجية.', 'kooragoal' ), [ 'body' => $body ] );
        }

        set_transient( $key, $data, $cache_ttl );

        return $data;
    }
}

if ( ! function_exists( 'kooragoal_get_fixtures' ) ) {
    function kooragoal_get_fixtures( array $league_ids, string $date ): array {
        $fixtures = [];

        foreach ( $league_ids as $league_id ) {
            $response = kooragoal_fetch_api( 'by_league', [ 'league' => (int) $league_id ] );

            if ( is_wp_error( $response ) || empty( $response['response'] ?? null ) ) {
                continue;
            }

            $items = $response['response'];
            foreach ( $items as $item ) {
                $fixture = $item['fixture'] ?? [];
                if ( empty( $fixture['id'] ) ) {
                    continue;
                }

                $timestamp = isset( $fixture['timestamp'] ) ? (int) $fixture['timestamp'] : 0;
                $fixture_date = $timestamp ? wp_date( 'Y-m-d', $timestamp, new DateTimeZone( 'UTC' ) ) : null;

                if ( $fixture_date !== $date ) {
                    continue;
                }

                $fixtures[] = $item;
            }
        }

        usort(
            $fixtures,
            static function ( $a, $b ) {
                $at = (int) ( $a['fixture']['timestamp'] ?? 0 );
                $bt = (int) ( $b['fixture']['timestamp'] ?? 0 );
                return $at <=> $bt;
            }
        );

        return $fixtures;
    }
}

if ( ! function_exists( 'kooragoal_get_standings' ) ) {
    function kooragoal_get_standings( int $league_id ) {
        return kooragoal_fetch_api( 'standings', [ 'league' => $league_id ] );
    }
}

if ( ! function_exists( 'kooragoal_get_top_scorers' ) ) {
    function kooragoal_get_top_scorers( int $league_id ) {
        return kooragoal_fetch_api( 'top_scorers', [ 'league' => $league_id ] );
    }
}

if ( ! function_exists( 'kooragoal_get_fixture_details' ) ) {
    function kooragoal_get_fixture_details( int $fixture_id ) {
        return kooragoal_fetch_api( 'fixture', [ 'fixture' => $fixture_id ] );
    }
}

if ( ! function_exists( 'kooragoal_get_fixture_events' ) ) {
    function kooragoal_get_fixture_events( int $fixture_id ) {
        return kooragoal_fetch_api( 'events', [ 'fixture' => $fixture_id ] );
    }
}

if ( ! function_exists( 'kooragoal_get_fixture_lineups' ) ) {
    function kooragoal_get_fixture_lineups( int $fixture_id ) {
        return kooragoal_fetch_api( 'lineups', [ 'fixture' => $fixture_id ] );
    }
}

if ( ! function_exists( 'kooragoal_get_fixture_statistics' ) ) {
    function kooragoal_get_fixture_statistics( int $fixture_id ) {
        return kooragoal_fetch_api( 'statistics', [ 'fixture' => $fixture_id ] );
    }
}
