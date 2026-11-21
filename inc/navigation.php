<?php
defined( 'ABSPATH' ) || exit;

function westnote_glaze_outbound_url( $url ) {
	if ( ! preg_match( '~^(?:https?:)?//~i', trim( $url ) ) ) {
		return $url;
	}

	$parts     = wp_parse_url( trim( $url ) );
	$site_host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	if ( empty( $parts['host'] ) || ! $site_host || isset( $parts['user'] ) ) {
		return $url;
	}

	$host = strtolower( rtrim( $parts['host'], '.' ) );
	foreach ( array( $site_host, wp_parse_url( site_url( '/' ), PHP_URL_HOST ) ) as $internal_host ) {
		$domain = preg_replace( '/^www\./', '', strtolower( rtrim( (string) $internal_host, '.' ) ) );
		if ( $host === $domain || str_ends_with( $host, '.' . $domain ) ) {
			return $url;
		}
	}

	foreach ( explode( '&', $parts['query'] ?? '' ) as $parameter ) {
		$key = strtolower( urldecode( explode( '=', $parameter, 2 )[0] ) );
		if ( 'from' === $key || str_starts_with( $key, 'from[' ) || in_array( $key, array( 'sig', 'signature', 'x-amz-signature', 'x-goog-signature' ), true ) ) {
			return $url;
		}
	}

	// Keep the original query encoding and order, and insert the source before the fragment.
	$pieces    = explode( '#', trim( $url ), 2 );
	$separator = ! isset( $parts['query'] ) ? '?' : ( str_ends_with( $pieces[0], '?' ) || str_ends_with( $pieces[0], '&' ) ? '' : '&' );
	$pieces[0] .= $separator . 'from=' . rawurlencode( $site_host );
	return implode( '#', $pieces );
}

function westnote_glaze_filter_content_links( $content ) {
	if ( ! is_singular( array( 'post', 'page' ) ) || is_feed() || ! in_the_loop() || ! is_main_query() || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $content;
	}

	$site_host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	if ( ! $site_host ) {
		return $content;
	}
	$site_host = preg_replace( '/^www\./i', '', $site_host );

	$preview_links = is_singular( 'post' );
	$links         = new WP_HTML_Tag_Processor( $content );
	while ( $links->next_tag( 'A' ) ) {
		$href = $links->get_attribute( 'href' );
		if ( ! is_string( $href ) || ! preg_match( '~^(?:https?:)?//~i', trim( $href ) ) ) {
			continue;
		}
		$host = wp_parse_url( trim( $href ), PHP_URL_HOST );
		$preview = $preview_links && $host && 0 !== strcasecmp( preg_replace( '/^www\./i', '', $host ), $site_host );
		if ( $preview ) {
			$links->add_class( 'glaze-external-link' );
		}
		if ( null !== $links->get_attribute( 'download' ) ) {
			continue;
		}
		$destination = westnote_glaze_outbound_url( $href );
		if ( $destination !== $href ) {
			$links->set_attribute( 'href', $destination );
			if ( $preview ) {
				$links->set_attribute( 'data-glaze-preview-url', $href );
			}
		}
	}
	return $links->get_updated_html();
}
add_filter( 'the_content', 'westnote_glaze_filter_content_links', 20 );

function westnote_glaze_pagination_args() {
	$arrow = '<span class="glaze-pagination-arrow" aria-hidden="true"></span>';
	return array(
		'mid_size'  => 1,
		'prev_text' => $arrow . '<span class="screen-reader-text">' . esc_html__( 'Previous page' ) . '</span>',
		'next_text' => $arrow . '<span class="screen-reader-text">' . esc_html__( 'Next page' ) . '</span>',
	);
}

function westnote_glaze_default_menu_icon( $url ) {
	$host = wp_parse_url( $url, PHP_URL_HOST );
	if ( $host ? 0 !== strcasecmp( $host, (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) ) : ! str_starts_with( $url, '/' ) ) {
		return '';
	}

	$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
	$home = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( $path === $home ) {
		return 'home';
	}

	$slug = basename( $path );
	$aliases = array(
		'archive'       => 'archives',
		'gallery'       => 'album',
		'notes'         => 'talks',
		'rss'           => 'feeds',
		'feed'          => 'feeds',
		'subscribe'     => 'feeds',
		'subscriptions' => 'feeds',
	);
	return westnote_glaze_icon_name( $aliases[ $slug ] ?? $slug, array( 'pages' ) );
}

function westnote_glaze_primary_menu_content( $title, $icon ) {
	$label = trim( wp_strip_all_tags( $title ) );
	if ( '友链' === $label ) {
		$label = '友情链接';
	}

	$display = esc_html( $label );
	if ( preg_match( '/\A(\p{Han}{2})(\p{Han}{2})\z/u', $label, $lines ) ) {
		$display = esc_html( $lines[1] ) . '<br>' . esc_html( $lines[2] );
	}

	return $icon . '<span class="screen-reader-text">' . esc_html( $label ) . '</span>'
		. '<span class="site-nav-label" aria-hidden="true">' . $display . '</span>';
}

function westnote_glaze_primary_icon( $title, $item, $args ) {
	if ( 'primary' !== ( $args->theme_location ?? '' ) ) {
		return $title;
	}

	$custom = westnote_glaze_sanitize_svg_icon( get_post_meta( $item->ID, '_glaze_menu_svg', true ) );
	if ( '' !== $custom ) {
		$icon = '<span class="site-nav-icon site-nav-custom-icon" aria-hidden="true">' . $custom . '</span>';
	} else {
		$name = get_post_meta( $item->ID, '_glaze_menu_icon', true );
		if ( '' === $name && 'page' === $item->object ) {
			$name = get_post_meta( $item->object_id, '_glaze_page_icon', true );
		}
		if ( '' === $name && 'category' === $item->object ) {
			$name = get_term_meta( $item->object_id, '_glaze_category_icon', true );
		}
		$icon = westnote_glaze_icon_markup( $name ?: westnote_glaze_default_menu_icon( $item->url ), 'site-nav-icon' );
	}

	return westnote_glaze_primary_menu_content( $title, $icon );
}
add_filter( 'nav_menu_item_title', 'westnote_glaze_primary_icon', 10, 3 );

function westnote_glaze_primary_navigation() {
	$home = home_url( '/' );
	$home_label = __( 'Home', 'glaze' );
	$pages = get_pages(
		array(
			'sort_column' => 'menu_order,post_title',
			'parent'      => 0,
			'exclude'     => (string) get_option( 'page_on_front' ),
		)
	);
	?>
	<ul class="site-menu">
		<li><a href="<?php echo esc_url( $home ); ?>" <?php if ( is_front_page() ) : ?>aria-current="page"<?php endif; ?>>
			<?php echo westnote_glaze_primary_menu_content( $home_label, westnote_glaze_icon_markup( 'home', 'site-nav-icon' ) ); ?>
		</a></li>
		<?php foreach ( $pages ?: array() as $page ) : ?>
			<?php
			$url   = get_permalink( $page );
			$title = get_the_title( $page );
			$name  = get_post_meta( $page->ID, '_glaze_page_icon', true ) ?: westnote_glaze_default_menu_icon( $url );
			$icon  = westnote_glaze_icon_markup( $name, 'site-nav-icon' );
			?>
			<li><a href="<?php echo esc_url( $url ); ?>" <?php if ( is_page( $page->ID ) ) : ?>aria-current="page"<?php endif; ?>>
				<?php echo westnote_glaze_primary_menu_content( $title, $icon ); ?>
			</a></li>
		<?php endforeach; ?>
	</ul>
	<?php
}

function westnote_glaze_menu_icon_fields( $item_id ) {
	$groups = array( 'pages', 'categories', 'social' );
	$selected = westnote_glaze_icon_name( get_post_meta( $item_id, '_glaze_menu_icon', true ), $groups );
	$svg = get_post_meta( $item_id, '_glaze_menu_svg', true );
	?>
	<div class="description description-wide">
		<?php westnote_glaze_icon_picker( "glaze_menu_icon[{$item_id}]", $selected, $groups, __( 'Use the default icon', 'glaze' ) ); ?>
		<p>
			<label for="glaze-menu-svg-<?php echo esc_attr( $item_id ); ?>"><?php esc_html_e( 'Custom SVG icon for the right-side menu', 'glaze' ); ?></label>
			<textarea id="glaze-menu-svg-<?php echo esc_attr( $item_id ); ?>" class="widefat code" rows="3" name="glaze_menu_svg[<?php echo esc_attr( $item_id ); ?>]"><?php echo esc_textarea( $svg ); ?></textarea>
			<span class="description"><?php esc_html_e( 'Paste a static SVG such as a Font Awesome icon. A custom icon takes precedence over the preset.', 'glaze' ); ?></span>
		</p>
	</div>
	<?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'westnote_glaze_menu_icon_fields' );

function westnote_glaze_save_menu_icon( $menu_id, $item_id ) {
	if ( ! current_user_can( 'edit_theme_options' ) || ! isset( $_POST['update-nav-menu-nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['update-nav-menu-nonce'] ) ), 'update-nav_menu' ) ) {
		return;
	}
	if ( isset( $_POST['glaze_menu_icon'][ $item_id ] ) ) {
		$name = westnote_glaze_icon_name( wp_unslash( $_POST['glaze_menu_icon'][ $item_id ] ), array( 'pages', 'categories', 'social' ) );
		update_post_meta( $item_id, '_glaze_menu_icon', $name );
	}
	if ( isset( $_POST['glaze_menu_svg'][ $item_id ] ) ) {
		$svg = westnote_glaze_sanitize_svg_icon( wp_unslash( $_POST['glaze_menu_svg'][ $item_id ] ) );
		update_post_meta( $item_id, '_glaze_menu_svg', $svg );
	}
}
add_action( 'wp_update_nav_menu_item', 'westnote_glaze_save_menu_icon', 10, 2 );
