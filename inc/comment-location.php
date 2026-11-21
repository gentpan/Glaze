<?php
defined( 'ABSPATH' ) || exit;

function westnote_glaze_comment_location( $comment, $resolve = false ) {
	if ( ! $comment || '1' !== (string) $comment->comment_approved ) {
		return null;
	}
	$post = get_post( $comment->comment_post_ID );
	if ( ! $post || $post->post_password || ! is_post_publicly_viewable( $post ) ) {
		return null;
	}

	$location = westnote_glaze_lookup_region( $comment->comment_author_IP, $resolve );
	if ( ! $location ) {
		return $location;
	}

	$code = $location['country_code'] ?? '';
	// Existing geolocation transients stored the flag URL instead of the country code.
	$legacy_flag = $location['flag'] ?? '';
	if ( '' === $code && is_string( $legacy_flag ) && preg_match( '~\Ahttps://flagcdn\.io/flags/1x1/([a-z]{2})\.svg\z~', $legacy_flag, $match ) ) {
		$code = $match[1];
	}
	$flag = westnote_glaze_flag_url( $code );
	if ( '' === $flag ) {
		return null;
	}

	$location['flag'] = $flag;
	unset( $location['country_code'] );
	return $location;
}

function westnote_glaze_lookup_region( $address, $resolve ) {
	if ( ! filter_var( $address, FILTER_VALIDATE_IP ) ) {
		return null;
	}
	$packed = inet_pton( $address );
	if ( str_starts_with( $packed, str_repeat( "\0", 10 ) . "\xff\xff" ) ) {
		$packed = substr( $packed, -4 );
	}
	$address = inet_ntop( $packed );
	if ( ! filter_var( $address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
		return null;
	}

	$key     = 'glaze_comment_geo_' . hash_hmac( 'sha256', $address, wp_salt( 'auth' ) );
	$cached  = get_transient( $key );
	if ( false !== $cached || ! $resolve ) {
		return $cached;
	}

	$response = wp_safe_remote_get(
		'https://api.cnip.io/geoip/' . rawurlencode( $address ),
		array( 'timeout' => 3, 'redirection' => 0, 'limit_response_size' => 8192 )
	);
	$location = array();
	if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		$code = is_string( $data['country_code'] ?? null ) ? strtolower( $data['country_code'] ) : '';
		if ( preg_match( '/\A[a-z]{2}\z/', $code ) ) {
			$country  = is_string( $data['country'] ?? null ) ? sanitize_text_field( $data['country'] ) : strtoupper( $code );
			$province = is_string( $data['province'] ?? null ) ? sanitize_text_field( $data['province'] ) : '';
			$city     = is_string( $data['city'] ?? null ) ? sanitize_text_field( $data['city'] ) : '';
			$place    = 'cn' === $code ? implode( ' · ', array_unique( array_filter( array( $province, $city ) ) ) ) : $city;
			$location = array(
				'country_code' => $code,
				'country'      => $country ?: strtoupper( $code ),
				'place'        => $place,
			);
		}
	}
	set_transient( $key, $location, $location ? MONTH_IN_SECONDS : HOUR_IN_SECONDS );
	return $location;
}

function westnote_glaze_comment_location_label( $location ) {
	return implode( ' · ', array_unique( array_filter( array( $location['country'], $location['place'] ) ) ) );
}

function westnote_glaze_register_comment_location() {
	register_rest_route(
		'glaze/v1',
		'/comment-location/(?P<id>[1-9][0-9]*)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'permission_callback' => '__return_true',
			'callback'            => static function ( $request ) {
				$comment  = get_comment( (int) $request['id'] );
				$location = westnote_glaze_comment_location( $comment, true );
				if ( $location ) {
					$location['label'] = westnote_glaze_comment_location_label( $location );
				}
				return rest_ensure_response( $location ?: null );
			},
		)
	);
}
add_action( 'rest_api_init', 'westnote_glaze_register_comment_location' );
