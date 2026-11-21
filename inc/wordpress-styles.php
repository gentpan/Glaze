<?php
defined( 'ABSPATH' ) || exit;

function westnote_glaze_external_styles_limit( $limit ) {
	return is_admin() || is_customize_preview() ? $limit : 0;
}
add_filter( 'styles_inline_size_limit', 'westnote_glaze_external_styles_limit' );

function westnote_glaze_externalize_wordpress_styles( $html ) {
	if ( is_customize_preview() ) {
		return $html;
	}

	$styles = array(
		'wp-img-auto-sizes-contain-inline-css' => 'image-auto-sizes',
		'wp-emoji-styles-inline-css'           => 'emoji',
		'global-styles-inline-css'             => 'global-styles',
	);
	$processor = new class( $html ) extends WP_HTML_Tag_Processor {
		public function replace_style( $link ) {
			if ( $this->set_bookmark( 'style' ) ) {
				$span = $this->bookmarks['style'];
				$this->lexical_updates[] = new WP_HTML_Text_Replacement( $span->start, $span->length, $link );
				$this->release_bookmark( 'style' );
			}
		}
	};

	while ( $processor->next_tag( array( 'tag_closers' => 'visit' ) ) ) {
		if ( 'HEAD' === $processor->get_tag() && $processor->is_tag_closer() ) {
			break;
		}
		if ( 'STYLE' !== $processor->get_tag() ) {
			continue;
		}

		$id = $processor->get_attribute( 'id' );
		if ( ! is_string( $id ) || ! isset( $styles[ $id ] ) || null !== $processor->get_attribute( 'media' ) ) {
			continue;
		}
		$file = 'assets/css/wordpress/' . $styles[ $id ] . '.css';
		$path = get_parent_theme_file_path( $file );
		$css  = trim( $processor->get_modifiable_text() );
		// Keep WordPress output whenever a core update or customization changes the rules.
		if ( '' === $css || ! is_readable( $path ) || $css . "\n" !== file_get_contents( $path ) ) {
			continue;
		}

		$url = add_query_arg( 'ver', substr( hash( 'sha256', $css ), 0, 12 ), get_parent_theme_file_uri( $file ) );
		$processor->replace_style( sprintf( '<link rel="stylesheet" id="%s" href="%s" />', esc_attr( $id ), esc_url( $url ) ) );
	}
	return $processor->get_updated_html();
}
add_filter( 'wp_template_enhancement_output_buffer', 'westnote_glaze_externalize_wordpress_styles', PHP_INT_MAX );
