<?php
defined( 'ABSPATH' ) || exit;

function westnote_glaze_subscription_sources() {
	return array_values(
		array_filter(
			get_bookmarks( array( 'hide_invisible' => true ) ),
			static fn( $source ) => '' !== trim( (string) $source->link_rss )
		)
	);
}

function westnote_glaze_subscription_updates() {
	$updates = array();
	$now     = time();
	foreach ( westnote_glaze_subscription_sources() as $source ) {
		$hash = hash( 'sha256', trim( (string) $source->link_rss ) );
		foreach ( array( 'glaze_feed_source_', 'p1_feed_source_', 'feng_feed_source_' ) as $prefix ) {
			$snapshot = get_option( $prefix . (int) $source->link_id, array() );
			if ( ! is_array( $snapshot ) || ( $snapshot['hash'] ?? '' ) !== $hash || ! is_array( $snapshot['items'] ?? null ) ) {
				continue;
			}
			foreach ( $snapshot['items'] as $item ) {
				if ( ! is_array( $item ) || ! is_string( $item['url'] ?? null ) || ! is_string( $item['title'] ?? null ) ) {
					continue;
				}
				$url       = esc_url_raw( $item['url'], array( 'http', 'https' ) );
				$published = max( 0, (int) ( $item['published'] ?? 0 ) );
				if ( ! wp_parse_url( $url, PHP_URL_HOST ) || $published > $now || ( isset( $updates[ $url ] ) && $published <= $updates[ $url ]['published'] ) ) {
					continue;
				}
				$updates[ $url ] = array(
					'title'     => $item['title'],
					'source'    => $source->link_name,
					'icon'      => $source->link_image,
					'published' => $published,
					'summary'   => is_string( $item['summary'] ?? null ) ? $item['summary'] : '',
				);
			}
			break;
		}
	}
	uasort( $updates, static fn( $left, $right ) => $right['published'] <=> $left['published'] );
	return $updates;
}

function westnote_glaze_schedule_subscriptions() {
	if ( ! wp_next_scheduled( 'westnote_glaze_refresh_subscriptions' ) ) {
		wp_schedule_event( time() + MINUTE_IN_SECONDS, 'hourly', 'westnote_glaze_refresh_subscriptions' );
	}
}
add_action( 'init', 'westnote_glaze_schedule_subscriptions' );

function westnote_glaze_queue_subscriptions() {
	foreach ( westnote_glaze_subscription_sources() as $index => $source ) {
		$args = array( (int) $source->link_id );
		if ( ! wp_next_scheduled( 'westnote_glaze_refresh_subscription', $args ) ) {
			wp_schedule_single_event( time() + 1 + $index * 15, 'westnote_glaze_refresh_subscription', $args );
		}
	}
}
add_action( 'westnote_glaze_refresh_subscriptions', 'westnote_glaze_queue_subscriptions' );

function westnote_glaze_refresh_subscription( $link_id ) {
	$source = get_bookmark( $link_id );
	if ( ! $source || 'Y' !== $source->link_visible ) {
		return;
	}
	$url = trim( (string) $source->link_rss );
	if ( '' === $url ) {
		return;
	}

	$response = wp_safe_remote_get(
		$url,
		array(
			'timeout'             => 6,
			'redirection'         => 3,
			'limit_response_size' => 1048577,
		)
	);
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return;
	}
	$xml = wp_remote_retrieve_body( $response );
	if ( '' === $xml || strlen( $xml ) > 1048576 || str_contains( $xml, chr( 0 ) ) || preg_match( '/<!\s*(DOCTYPE|ENTITY)/i', $xml ) ) {
		return;
	}

	require_once ABSPATH . WPINC . '/SimplePie/autoloader.php';
	$feed = new SimplePie\SimplePie();
	$feed->enable_cache( false );
	$feed->set_autodiscovery_level( 0 );
	$feed->set_raw_data( $xml );
	if ( ! $feed->init() || $feed->error() || ! $feed->get_type() ) {
		return;
	}

	$items = array();
	foreach ( $feed->get_items( 0, 100 ) as $item ) {
		$link = esc_url_raw( (string) $item->get_permalink(), array( 'http', 'https' ) );
		if ( ! wp_parse_url( $link, PHP_URL_HOST ) ) {
			continue;
		}
		$title   = trim( wp_html_excerpt( html_entity_decode( (string) $item->get_title(), ENT_QUOTES | ENT_HTML5, 'UTF-8' ), 200, '…' ) );
		$summary = html_entity_decode( (string) $item->get_description(), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$items[] = array(
			'url'       => $link,
			'title'     => $title ?: __( 'Untitled', 'glaze' ),
			'published' => max( 0, (int) $item->get_date( 'U' ) ),
			'summary'   => wp_html_excerpt( $summary, 150, '…' ),
		);
	}
	update_option( 'glaze_feed_source_' . (int) $link_id, array( 'hash' => hash( 'sha256', $url ), 'items' => $items ), false );
}
add_action( 'westnote_glaze_refresh_subscription', 'westnote_glaze_refresh_subscription' );

function westnote_glaze_unschedule_subscriptions() {
	wp_unschedule_hook( 'westnote_glaze_refresh_subscriptions' );
	wp_unschedule_hook( 'westnote_glaze_refresh_subscription' );
}
add_action( 'switch_theme', 'westnote_glaze_unschedule_subscriptions' );
