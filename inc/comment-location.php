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

	$flag = westnote_glaze_flag_url( westnote_glaze_location_country_code( $location ) );
	if ( '' === $flag ) {
		return null;
	}

	$location['flag'] = $flag;
	unset( $location['country_code'] );
	return $location;
}

function westnote_glaze_location_country_code( $location ) {
	$code = $location['country_code'] ?? '';
	if ( is_string( $code ) && preg_match( '/\A[a-z]{2}\z/i', $code ) ) {
		return strtolower( $code );
	}

	// Older geolocation transients stored the flag URL rather than a country code.
	$flag = $location['flag'] ?? '';
	return is_string( $flag ) && preg_match( '~\Ahttps://flagcdn\.io/flags/1x1/([a-z]{2})\.svg\z~', $flag, $match ) ? $match[1] : '';
}

function westnote_glaze_normalize_ip( $address ) {
	if ( ! is_string( $address ) || ! filter_var( $address, FILTER_VALIDATE_IP ) ) {
		return '';
	}
	$packed = inet_pton( $address );
	if ( str_starts_with( $packed, str_repeat( "\0", 10 ) . "\xff\xff" ) ) {
		$packed = substr( $packed, -4 );
	}
	return inet_ntop( $packed );
}

function westnote_glaze_is_cloudflare_proxy( $address ) {
	if ( '' === $address ) {
		return false;
	}
	$packed = inet_pton( $address );
	// https://www.cloudflare.com/ips/
	$ranges = 4 === strlen( $packed ) ? array(
		'173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
		'141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
		'197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
		'104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
	) : array(
		'2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
		'2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
	);
	foreach ( $ranges as $range ) {
		list( $network, $prefix ) = explode( '/', $range );
		$network = inet_pton( $network );
		$bytes   = intdiv( (int) $prefix, 8 );
		if ( 0 !== strncmp( $packed, $network, $bytes ) ) {
			continue;
		}
		$bits = (int) $prefix % 8;
		if ( 0 === $bits || ( ord( $packed[ $bytes ] ) >> ( 8 - $bits ) ) === ( ord( $network[ $bytes ] ) >> ( 8 - $bits ) ) ) {
			return true;
		}
	}
	return false;
}

function westnote_glaze_visitor_ip( $server ) {
	$trusted = westnote_glaze_normalize_ip( $server['P1_CLIENT_IP'] ?? '' );
	if ( '' !== $trusted ) {
		return $trusted;
	}

	$peer = westnote_glaze_normalize_ip( $server['REMOTE_ADDR'] ?? '' );
	if ( ! westnote_glaze_is_cloudflare_proxy( $peer ) ) {
		return $peer;
	}

	$visitor = westnote_glaze_normalize_ip( $server['HTTP_CF_CONNECTING_IP'] ?? '' );
	if ( '' !== $visitor && ! str_contains( $visitor, ':' ) && ( ord( inet_pton( $visitor )[0] ) & 0xf0 ) === 0xf0 ) {
		// Cloudflare Pseudo IPv4 overwrite mode preserves the original IPv6 here.
		$ipv6 = westnote_glaze_normalize_ip( $server['HTTP_CF_CONNECTING_IPV6'] ?? '' );
		return str_contains( $ipv6, ':' ) ? $ipv6 : '';
	}
	return $visitor;
}

function westnote_glaze_lookup_region( $address, $resolve ) {
	$address = westnote_glaze_normalize_ip( $address );
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
