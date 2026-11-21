<?php
defined( 'ABSPATH' ) || exit;

function westnote_glaze_default_pagination_motto() {
	return '永远相信美好的事情即将发生';
}

function westnote_glaze_get_pagination_motto() {
	$default = westnote_glaze_default_pagination_motto();
	$motto   = get_theme_mod( 'westnote_glaze_pagination_motto' );
	if ( is_array( $motto ) && is_string( $motto['text'] ?? null ) ) {
		return array( 'text' => $motto['text'], 'svg' => (string) ( $motto['svg'] ?? '' ) );
	}
	return array( 'text' => $default, 'svg' => '' );
}

function westnote_glaze_text_glyphs( $glyph_file, $characters ) {
	$file = get_parent_theme_file_path( $glyph_file );
	if ( ! is_readable( $file ) || ! function_exists( 'gzdecode' ) ) {
		return null;
	}
	$stream = fopen( $file, 'rb' );
	if ( false === $stream ) {
		return null;
	}
	try {
		$header = fread( $stream, 4 );
		if ( ! is_string( $header ) || 4 !== strlen( $header ) ) {
			return null;
		}
		$header_size = unpack( 'N', $header )[1];
		$data = json_decode( fread( $stream, $header_size ), true );
		if ( ! is_array( $data ) ) {
			return null;
		}
		list( $metrics, $index ) = $data;
		$pages  = array_unique( array_map( static fn( $character ) => bin2hex( substr( $character, 0, 2 ) ), $characters ) );
		$glyphs = array();
		foreach ( $pages as $page ) {
			if ( ! isset( $index[ $page ] ) ) {
				continue;
			}
			list( $offset, $size ) = $index[ $page ];
			fseek( $stream, 4 + $header_size + $offset );
			$chunk = gzdecode( fread( $stream, $size ) );
			$glyphs += false === $chunk ? array() : (array) json_decode( $chunk, true );
		}
		return array( $metrics, $glyphs );
	} finally {
		fclose( $stream );
	}
}

function westnote_glaze_generate_text_svg( $text, $glyph_file, $font_size, $line_limit = 0 ) {
	$characters = preg_split( '//u', $text, -1, PREG_SPLIT_NO_EMPTY );
	if ( ! $characters ) {
		return '';
	}
	$data = westnote_glaze_text_glyphs( $glyph_file, $characters );
	if ( ! $data ) {
		return '';
	}
	list( list( $em, $ascent, $descent ), $glyphs ) = $data;
	$lines   = array();
	$line    = '';
	$x       = 0;
	$width   = 0;
	$advance = $ascent + $descent;
	foreach ( $characters as $character ) {
		$glyph = $glyphs[ $character ] ?? null;
		$step  = (int) ( $glyph[0] ?? $em );
		if ( $line_limit && $x && $x + $step > $line_limit * $em ) {
			$lines[] = array( $x, $line );
			$width   = max( $width, $x );
			$line    = '';
			$x       = 0;
		}
		if ( ! $glyph ) {
			$line .= sprintf( '<text transform="translate(%d 0) scale(1 -1)" font-family="system-ui" font-size="%d">%s</text>', $x, $em, esc_html( $character ) );
		} elseif ( '' !== $glyph[1] ) {
			$line .= sprintf( '<path transform="translate(%d 0)" d="%s"/>', $x, esc_attr( $glyph[1] ) );
		}
		$x += $step;
	}
	$lines[] = array( $x, $line );
	$width   = max( 1, $width, $x );
	$height  = count( $lines ) * $advance;
	$svg     = sprintf(
		'<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d" fill="currentColor" role="img" aria-label="%s">',
		max( 1, round( $width * $font_size / $em ) ),
		max( 1, round( $height * $font_size / $em ) ),
		$width,
		$height,
		esc_attr( $text )
	);
	foreach ( $lines as $index => list( $line_width, $paths ) ) {
		$svg .= sprintf( '<g transform="translate(%d %d) scale(1 -1)">%s</g>', $width - $line_width, $ascent + $index * $advance, $paths );
	}
	return $svg . '</svg>';
}

function westnote_glaze_text_svg_markup( $svg, $text ) {
	$svg = wp_kses(
		(string) $svg,
		array(
			'svg'  => array( 'xmlns' => true, 'width' => true, 'height' => true, 'viewbox' => true, 'fill' => true, 'role' => true, 'aria-label' => true ),
			'g'    => array( 'transform' => true ),
			'path' => array( 'transform' => true, 'd' => true ),
			'text' => array( 'transform' => true, 'font-family' => true, 'font-size' => true ),
		)
	);
	return '' === $svg ? esc_html( $text ) : $svg;
}

function westnote_glaze_pagination_motto_markup( $motto ) {
	$svg = $motto['svg'];
	if ( '' === $svg && westnote_glaze_default_pagination_motto() === $motto['text'] ) {
		$file = get_parent_theme_file_path( 'inc/motto-default.svg' );
		$svg  = is_readable( $file ) ? file_get_contents( $file ) : '';
	}
	return westnote_glaze_text_svg_markup( $svg, $motto['text'] );
}

function westnote_glaze_refresh_site_title_svg() {
	$title = get_option( 'blogname' );
	$svg   = westnote_glaze_generate_text_svg( $title, 'inc/site-title-glyphs.bin', 16 );
	set_theme_mod( 'westnote_glaze_site_title_svg', array( 'text' => $title, 'svg' => $svg ) );
}
add_action( 'update_option_blogname', 'westnote_glaze_refresh_site_title_svg' );
add_action( 'after_switch_theme', 'westnote_glaze_refresh_site_title_svg' );

function westnote_glaze_site_title_markup() {
	$title = get_option( 'blogname' );
	$saved = get_theme_mod( 'westnote_glaze_site_title_svg' );
	if ( is_array( $saved ) && $title === ( $saved['text'] ?? null ) ) {
		return westnote_glaze_text_svg_markup( $saved['svg'] ?? '', $title );
	}
	if ( '西风' === $title ) {
		$file = get_parent_theme_file_path( 'inc/site-title-default.svg' );
		if ( is_readable( $file ) ) {
			return westnote_glaze_text_svg_markup( file_get_contents( $file ), $title );
		}
	}
	return esc_html( $title );
}
