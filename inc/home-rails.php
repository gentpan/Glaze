<?php
defined( 'ABSPATH' ) || exit;

function westnote_glaze_home_rail_defaults() {
	return array( 'left' => 'subscriptions', 'right' => 'comments' );
}

function westnote_glaze_home_rail_types() {
	return array(
		'subscriptions' => __( 'Subscription updates', 'glaze' ),
		'comments'      => __( 'Recent comments', 'glaze' ),
		'talks'         => __( 'Latest short notes', 'glaze' ),
		'links'         => __( 'Random blogroll', 'glaze' ),
		'none'          => __( 'Hide', 'glaze' ),
	);
}

function westnote_glaze_sanitize_home_rail( $value, $default ) {
	return is_string( $value ) && isset( westnote_glaze_home_rail_types()[ $value ] ) ? $value : $default;
}

function westnote_glaze_home_rail_type( $side ) {
	$default = westnote_glaze_home_rail_defaults()[ $side ];
	return westnote_glaze_sanitize_home_rail( get_theme_mod( "westnote_glaze_home_rail_{$side}", $default ), $default );
}

function westnote_glaze_random_blogroll( $limit, $previous = array() ) {
	$links = array();
	foreach ( get_bookmarks( array( 'hide_invisible' => true ) ) as $link ) {
		$url = esc_url_raw( $link->link_url, array( 'http', 'https' ) );
		if ( wp_parse_url( $url, PHP_URL_HOST ) ) {
			$links[ $url ] = $link;
		}
	}
	$urls = array_keys( $links );
	shuffle( $urls );
	$fresh  = array();
	$repeat = array();
	foreach ( $urls as $url ) {
		$link = $links[ $url ];
		if ( in_array( (int) $link->link_id, $previous, true ) ) {
			$repeat[ $url ] = $link;
		} else {
			$fresh[ $url ] = $link;
		}
	}
	$sample = array_slice( $fresh + $repeat, 0, $limit, true );
	// A small blogroll can still change order when there are no unseen links.
	if ( count( $sample ) > 1 && $previous === array_map( 'intval', wp_list_pluck( array_values( $sample ), 'link_id' ) ) ) {
		$sample = array_slice( $sample, 1, null, true ) + array_slice( $sample, 0, 1, true );
	}
	return $sample;
}

function westnote_glaze_home_rail_content( $type, $previous = array() ) {
	$limit = 5;
	switch ( $type ) {
		case 'subscriptions':
			return array( 'template' => 'subscription-card', 'items' => array_slice( westnote_glaze_subscription_updates(), 0, $limit, true ) );
		case 'comments':
			return array( 'template' => 'comment-card', 'items' => westnote_glaze_recent_home_comments( $limit ) );
		case 'talks':
			$notes = westnote_glaze_notes_query( $limit );
			return array(
				'template' => 'home-note-card',
				'items'    => $notes->posts,
				'url'      => $notes->posts ? westnote_glaze_collection_url( 'talks' ) : '',
			);
		case 'links':
			return array(
				'template' => 'home-link-card',
				'items'    => westnote_glaze_random_blogroll( $limit, $previous ),
			);
	}
	return array( 'items' => array() );
}

function westnote_glaze_register_blogroll_refresh() {
	register_rest_route(
		'glaze/v1',
		'/blogroll',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'permission_callback' => '__return_true',
			'args'                => array(
				'exclude' => array(
					'type'    => 'string',
					'default' => '',
					'pattern' => '^(?:[0-9]{1,20}(?:,[0-9]{1,20}){0,4})?$',
				),
			),
			'callback'            => static function ( $request ) {
				$rail = westnote_glaze_home_rail_content( 'links', wp_parse_id_list( $request['exclude'] ) );
				ob_start();
				get_template_part( 'template-parts/home-rail-items', null, $rail + array( 'type' => 'links' ) );
				$response = rest_ensure_response( array( 'html' => ob_get_clean() ) );
				$response->header( 'Cache-Control', 'no-store, max-age=0' );
				return $response;
			},
		)
	);
}
add_action( 'rest_api_init', 'westnote_glaze_register_blogroll_refresh' );
