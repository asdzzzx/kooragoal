<?php
/**
 * دوال مساعدة للقوالب.
 */

if ( ! function_exists( 'kooragoal_extract_fixture_data' ) ) {
    function kooragoal_extract_fixture_data( array $fixture ): array {
        $fixture_data = $fixture['fixture'] ?? [];
        $league       = $fixture['league'] ?? [];
        $teams        = $fixture['teams'] ?? [];
        $goals        = $fixture['goals'] ?? [];

        $timestamp = isset( $fixture_data['timestamp'] ) ? (int) $fixture_data['timestamp'] : 0;
        $date      = $timestamp ? wp_date( 'Y-m-d', $timestamp, new DateTimeZone( 'UTC' ) ) : '';
        $time      = $timestamp ? wp_date( 'H:i', $timestamp, new DateTimeZone( 'UTC' ) ) : '';

        $status      = $fixture_data['status'] ?? [];

        return [
            'id'          => (int) ( $fixture_data['id'] ?? 0 ),
            'status'      => $status,
            'status_label'=> kooragoal_format_status_label( $status ),
            'league'      => [
                'name' => $league['name'] ?? '',
                'logo' => $league['logo'] ?? '',
            ],
            'date'        => $date,
            'time'        => $time,
            'timestamp'   => $timestamp,
            'venue'       => $fixture_data['venue'] ?? [],
            'teams'       => [
                'home' => $teams['home'] ?? [],
                'away' => $teams['away'] ?? [],
            ],
            'score'       => [
                'home' => (int) ( $goals['home'] ?? 0 ),
                'away' => (int) ( $goals['away'] ?? 0 ),
            ],
        ];
    }
}

if ( ! function_exists( 'kooragoal_format_status_label' ) ) {
    function kooragoal_format_status_label( array $status ): string {
        if ( empty( $status['short'] ) ) {
            return __( 'قريباً', 'kooragoal' );
        }

        $short = $status['short'];
        $long  = $status['long'] ?? $short;

        if ( in_array( $short, [ 'FT', 'AET', 'PEN' ], true ) ) {
            return __( 'منتهية', 'kooragoal' );
        }

        if ( in_array( $short, [ 'NS', 'TBD' ], true ) ) {
            return __( 'لم تبدأ', 'kooragoal' );
        }

        return $long;
    }
}

if ( ! function_exists( 'kooragoal_format_event_icon' ) ) {
    function kooragoal_format_event_icon( string $type, string $detail ): string {
        $icons = [
            'Goal'        => 'icon-goal.svg',
            'CardYellow'  => 'icon-yellow-card.svg',
            'CardRed'     => 'icon-red-card.svg',
            'Substitution'=> 'icon-substitution.svg',
        ];

        $key = 'Goal';
        if ( 'Card' === $type ) {
            $key = ( 'Yellow Card' === $detail ) ? 'CardYellow' : 'CardRed';
        } elseif ( 'Substitution' === $type ) {
            $key = 'Substitution';
        }

        $filename = $icons[ $key ] ?? 'icon-goal.svg';

        $path = KOORAGOAL_THEME_URL . '/assets/images/' . $filename;
        return '<img src="' . esc_url( $path ) . '" alt="" class="event-icon" loading="lazy">';
    }
}

if ( ! function_exists( 'kooragoal_prepare_standings_rows' ) ) {
    function kooragoal_prepare_standings_rows( $data ): array {
        if ( empty( $data['response'][0]['league']['standings'][0] ?? [] ) ) {
            return [];
        }

        return $data['response'][0]['league']['standings'][0];
    }
}

if ( ! function_exists( 'kooragoal_prepare_scorers_rows' ) ) {
    function kooragoal_prepare_scorers_rows( $data ): array {
        if ( empty( $data['response'] ?? [] ) ) {
            return [];
        }

        return $data['response'];
    }
}
