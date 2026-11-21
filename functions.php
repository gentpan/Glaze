<?php
defined( 'ABSPATH' ) || exit;

require_once get_parent_theme_file_path( 'inc/appearance.php' );
require_once get_parent_theme_file_path( 'inc/wordpress-styles.php' );
require_once get_parent_theme_file_path( 'inc/typography.php' );
require_once get_parent_theme_file_path( 'inc/footer-links.php' );
require_once get_parent_theme_file_path( 'inc/icon-catalog.php' );
require_once get_parent_theme_file_path( 'inc/icons.php' );
require_once get_parent_theme_file_path( 'inc/navigation.php' );
require_once get_parent_theme_file_path( 'inc/comments.php' );
require_once get_parent_theme_file_path( 'inc/comment-list.php' );
require_once get_parent_theme_file_path( 'inc/comment-location.php' );
require_once get_parent_theme_file_path( 'inc/code-blocks.php' );
require_once get_parent_theme_file_path( 'inc/text-svg.php' );
require_once get_parent_theme_file_path( 'inc/settings.php' );
require_once get_parent_theme_file_path( 'inc/subscriptions.php' );
require_once get_parent_theme_file_path( 'inc/collections.php' );
require_once get_parent_theme_file_path( 'inc/home-rails.php' );

function westnote_glaze_setup() {
	load_theme_textdomain( 'glaze', get_parent_theme_file_path( 'languages' ) );
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	register_nav_menus(
		array(
			'primary' => __( 'Right-side menu', 'glaze' ),
			'footer'  => __( 'Footer Navigation', 'glaze' ),
		)
	);
}
add_action( 'after_setup_theme', 'westnote_glaze_setup' );

function westnote_glaze_gravatar_url( $url ) {
	if ( ! is_string( $url ) ) {
		return $url;
	}

	return preg_replace( '~^https?://(?:[a-z0-9-]+\.)*gravatar\.com(?=[/?#]|$)~i', 'https://gravatar.bluecdn.com', $url );
}
add_filter( 'get_avatar_url', 'westnote_glaze_gravatar_url' );

function westnote_glaze_enqueue_stylesheet( $handle, $file, $dependencies = array(), $media = 'all' ) {
	$stylesheet = 'assets/css/' . $file . '.css';
	$path       = get_parent_theme_file_path( $stylesheet );
	if ( is_file( $path ) ) {
		wp_enqueue_style(
			$handle,
			get_parent_theme_file_uri( $stylesheet ),
			$dependencies,
			(string) filemtime( $path ),
			$media
		);
	}
}

function westnote_glaze_enqueue_assets() {
	$width = westnote_glaze_get_content_width();
	westnote_glaze_enqueue_stylesheet( 'westnote-glaze', 'main' );
	westnote_glaze_enqueue_stylesheet( 'westnote-glaze-wide-dock', 'wide-dock', array( 'westnote-glaze' ), sprintf( 'screen and (min-width: %dpx)', max( 780, $width + 180 ) ) );
	westnote_glaze_enqueue_stylesheet( 'westnote-glaze-wide-rails', 'wide-rails', array( 'westnote-glaze' ), sprintf( 'screen and (min-width: %dpx) and (min-height: 430px)', max( 1200, $width + 600 ) ) );

	if ( is_singular( 'post' ) ) {
		wp_enqueue_style( 'westnote-glaze-cover-font', 'https://static.bluecdn.com/fonts/kuaikanshijieti.css', array(), null );
	}
	wp_enqueue_style( 'westnote-glaze-footer-font', 'https://static.bluecdn.com/fonts/misans.css', array(), null );

	$scripts = array(
		'westnote-glaze-app'  => array( 'assets/js/app.min.js', array(), false ),
		'westnote-glaze-main' => array( 'assets/js/main.min.js', array( 'westnote-glaze-app' ), true ),
	);

	foreach ( $scripts as $handle => list( $file, $dependencies, $in_footer ) ) {
		$path = get_parent_theme_file_path( $file );
		if ( is_file( $path ) ) {
			wp_enqueue_script( $handle, get_parent_theme_file_uri( $file ), $dependencies, (string) filemtime( $path ), $in_footer );
		}
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'westnote_glaze_enqueue_assets' );

function westnote_glaze_register_presence() {
	register_rest_route(
		'glaze/v1',
		'/presence',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'westnote_glaze_record_presence',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'westnote_glaze_register_presence' );

function westnote_glaze_total_pageviews() {
	$views = get_option( 'westnote_glaze_total_pageviews', false );
	return false === $views ? max( 0, (int) get_option( 'p1_total_pageviews' ), (int) get_option( 'feng_total_pageviews' ) ) : max( 0, (int) $views );
}

function westnote_glaze_latest_pageview() {
	global $wpdb;
	$schema = (int) get_option( 'westnote_glaze_pageview_schema' );
	if ( $schema ) {
		$table   = $wpdb->prefix . 'glaze_pageview_events';
		$columns = $schema >= 2 ? 'seen, region, country_code' : 'seen, region';
		$latest  = $wpdb->get_row( "SELECT $columns FROM `{$table}` WHERE region <> '' ORDER BY id DESC LIMIT 1", ARRAY_A );
		if ( $latest ) {
			return $latest;
		}
	}
	foreach ( array( 'p1_latest_visitor_location', 'polar_latest_visitor_location' ) as $key ) {
		$cached = get_transient( $key );
		if ( is_string( $cached['label'] ?? null ) && '' !== $cached['label'] ) {
			return array( 'region' => $cached['label'], 'country_code' => $cached['code'] ?? '' );
		}
	}
	return null;
}

function westnote_glaze_last_visit_label( $visit ) {
	return $visit ? ( $visit['region'] ?: wp_date( 'Y/m/d H:i', (int) $visit['seen'] ) ) : '—';
}

function westnote_glaze_flag_url( $code ) {
	if ( ! is_string( $code ) || ! preg_match( '/\A[a-z]{2}\z/', $code ) ) {
		return '';
	}

	$flag = 'assets/images/flags/1x1/' . $code . '.svg';
	if ( ! is_file( get_parent_theme_file_path( $flag ) ) ) {
		$flag = 'assets/images/flags/1x1/xx.svg';
	}
	return wp_make_link_relative( get_parent_theme_file_uri( $flag ) );
}

function westnote_glaze_last_visit_flag( $visit ) {
	return westnote_glaze_flag_url( $visit['country_code'] ?? '' );
}

function westnote_glaze_record_pageview( $table, $event, $now ) {
	global $wpdb;

	$schema = (int) get_option( 'westnote_glaze_pageview_schema' );
	if ( ! $schema ) {
		$created = $wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$table}` (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				event_id CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
				seen INT UNSIGNED NOT NULL,
				region VARCHAR(255) NOT NULL DEFAULT '',
				country_code CHAR(2) NOT NULL DEFAULT '',
				PRIMARY KEY (id),
				UNIQUE KEY event_id (event_id),
				KEY seen (seen)
			) ENGINE=InnoDB " . $wpdb->get_charset_collate()
		);
		if ( false === $created ) {
			return null;
		}
		add_option( 'westnote_glaze_total_pageviews', westnote_glaze_total_pageviews(), '', false );
		add_option( 'westnote_glaze_pageview_schema', 1, '', false );
	}
	if ( $schema < 2 ) {
		$column = "SHOW COLUMNS FROM `{$table}` LIKE 'country_code'";
		if ( ! $wpdb->get_var( $column ) ) {
			// Another request may have added the column after the first check.
			$show_errors = $wpdb->suppress_errors( true );
			$wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN country_code CHAR(2) NOT NULL DEFAULT ''" );
			$wpdb->suppress_errors( $show_errors );
		}
		if ( $wpdb->get_var( $column ) ) {
			$schema = 2;
			update_option( 'westnote_glaze_pageview_schema', $schema, false );
		}
	}

	if ( false === $wpdb->query( 'START TRANSACTION' ) ) {
		return null;
	}
	$inserted = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO `{$table}` (event_id, seen) VALUES (%s, %d)", $event, $now ) );
	if ( false === $inserted || ( 1 === $inserted && 1 !== $wpdb->query( "UPDATE {$wpdb->options} SET option_value = CAST(option_value AS UNSIGNED) + 1 WHERE option_name = 'westnote_glaze_total_pageviews'" ) ) || false === $wpdb->query( 'COMMIT' ) ) {
		$wpdb->query( 'ROLLBACK' );
		return null;
	}
	if ( 1 !== $inserted ) {
		return false;
	}

	wp_cache_delete( 'westnote_glaze_total_pageviews', 'options' );
	$address  = westnote_glaze_visitor_ip( $_SERVER );
	$location = westnote_glaze_lookup_region( $address, true );
	if ( $location ) {
		$metadata = array( 'region' => wp_html_excerpt( westnote_glaze_comment_location_label( $location ), 240 ) );
		if ( $schema >= 2 ) {
			$metadata['country_code'] = westnote_glaze_location_country_code( $location );
		}
		$wpdb->update( $table, $metadata, array( 'event_id' => $event ) );
	}
	$wpdb->query( $wpdb->prepare( "DELETE FROM `{$table}` WHERE seen < %d LIMIT 200", $now - DAY_IN_SECONDS ) );
	return true;
}

function westnote_glaze_record_presence( WP_REST_Request $request ) {
	global $wpdb;

	$visitor = $request->get_param( 'visitor' );
	if ( ! is_string( $visitor ) || ! preg_match( '/\A[a-f0-9]{32}\z/', $visitor ) ) {
		return new WP_Error( 'glaze_invalid_visitor', '', array( 'status' => 400 ) );
	}
	$event = $request->get_param( 'event' ) ?? '';
	if ( '' !== $event && ( ! is_string( $event ) || ! preg_match( '/\A[a-f0-9]{32}\z/', $event ) ) ) {
		return new WP_Error( 'glaze_invalid_pageview', '', array( 'status' => 400 ) );
	}
	$origin = get_http_origin();
	if ( '' !== $event && ( ( $origin && wp_parse_url( $origin, PHP_URL_HOST ) !== wp_parse_url( home_url( '/' ), PHP_URL_HOST ) ) || 'cross-site' === ( $_SERVER['HTTP_SEC_FETCH_SITE'] ?? '' ) ) ) {
		return new WP_Error( 'glaze_invalid_origin', '', array( 'status' => 403 ) );
	}

	$table = $wpdb->prefix . 'glaze_presence';
	if ( ! get_option( 'westnote_glaze_presence_schema' ) ) {
		$created = $wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$table}` (
				visitor_id CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
				last_seen INT UNSIGNED NOT NULL,
				PRIMARY KEY (visitor_id),
				KEY last_seen (last_seen)
			) " . $wpdb->get_charset_collate()
		);
		if ( false === $created ) {
			return new WP_Error( 'glaze_presence_unavailable', '', array( 'status' => 503 ) );
		}
		update_option( 'westnote_glaze_presence_schema', 1, false );
	}

	$now = time();
	$recorded = $wpdb->query(
		$wpdb->prepare(
			"INSERT INTO `{$table}` (visitor_id, last_seen) VALUES (%s, %d)
			 ON DUPLICATE KEY UPDATE last_seen = GREATEST(last_seen, %d)",
			$visitor,
			$now,
			$now
		)
	);
	if ( false === $recorded ) {
		return new WP_Error( 'glaze_presence_unavailable', '', array( 'status' => 503 ) );
	}

	$cutoff = $now - 5 * MINUTE_IN_SECONDS;
	$wpdb->query( $wpdb->prepare( "DELETE FROM `{$table}` WHERE last_seen < %d LIMIT 200", $cutoff ) );
	$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$table}` WHERE last_seen >= %d", $cutoff ) );
	if ( null === $count ) {
		return new WP_Error( 'glaze_presence_unavailable', '', array( 'status' => 503 ) );
	}

	$events = $wpdb->prefix . 'glaze_pageview_events';
	if ( '' !== $event && null === westnote_glaze_record_pageview( $events, $event, $now ) ) {
		return new WP_Error( 'glaze_pageview_unavailable', '', array( 'status' => 503 ) );
	}
	$latest = westnote_glaze_latest_pageview();
	return new WP_REST_Response(
		array(
			'count'         => (int) $count,
			'views'         => westnote_glaze_total_pageviews(),
			'lastVisit'     => westnote_glaze_last_visit_label( $latest ),
			'lastVisitFlag' => westnote_glaze_last_visit_flag( $latest ),
		),
		200,
		array( 'Cache-Control' => 'no-store' )
	);
}
