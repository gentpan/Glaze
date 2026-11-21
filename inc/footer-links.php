<?php
defined( 'ABSPATH' ) || exit;

function westnote_glaze_footer_icon_presets() {
	return array(
		'rss'       => 'RSS',
		'wordpress' => 'WordPress',
		'github'    => 'GitHub',
		'x-twitter' => 'X',
		'facebook'  => 'Facebook',
		'youtube'   => 'YouTube',
		'bluesky'   => 'Bluesky',
		'mastodon'  => 'Mastodon',
	);
}

function westnote_glaze_sanitize_footer_icon_preset( $value ) {
	return is_string( $value ) && isset( westnote_glaze_footer_icon_presets()[ $value ] ) ? $value : '';
}

function westnote_glaze_font_awesome_icon( $name, $class = '' ) {
	static $icons = array();
	if ( ! isset( westnote_glaze_footer_icon_presets()[ $name ] ) && ! in_array( $name, array( 'user', 'envelope', 'link', 'check', 'comment', 'comments', 'xmark' ), true ) ) {
		return '';
	}
	if ( ! isset( $icons[ $name ] ) ) {
		$path = get_parent_theme_file_path( 'assets/images/font-awesome/' . $name . '.svg' );
		$icons[ $name ] = is_readable( $path ) ? trim( (string) file_get_contents( $path ) ) : '';
	}
	$attributes = 'width="17" height="17" fill="currentColor" aria-hidden="true" focusable="false"';
	if ( '' !== $class ) {
		$attributes .= ' class="' . esc_attr( $class ) . '"';
	}
	return str_replace( '<svg ', '<svg ' . $attributes . ' ', $icons[ $name ] );
}

function westnote_glaze_sanitize_svg_icon( $value ) {
	if ( ! is_string( $value ) ) {
		return '';
	}

	$value = trim( $value );
	if ( strlen( $value ) > 12000 || strpbrk( $value, '&' . chr( 92 ) ) || ! preg_match( '/\A<svg\b.*<\/svg>\z/is', $value ) || preg_match( '/url\s*\(/i', $value ) ) {
		return '';
	}

	$paint = array_fill_keys( array( 'fill', 'fill-rule', 'fill-opacity', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-opacity', 'opacity', 'transform' ), true );
	$shapes = array(
		'svg'      => array( 'viewbox', 'width', 'height', 'xmlns' ),
		'g'        => array(),
		'path'     => array( 'd', 'clip-rule' ),
		'circle'   => array( 'cx', 'cy', 'r' ),
		'ellipse'  => array( 'cx', 'cy', 'rx', 'ry' ),
		'rect'     => array( 'x', 'y', 'rx', 'ry', 'width', 'height' ),
		'line'     => array( 'x1', 'y1', 'x2', 'y2' ),
		'polyline' => array( 'points' ),
		'polygon'  => array( 'points' ),
	);
	$allowed = array();
	foreach ( $shapes as $tag => $attributes ) {
		$allowed[ $tag ] = $paint + array_fill_keys( $attributes, true );
	}

	return wp_kses( $value, $allowed );
}

function westnote_glaze_footer_link_url( $value ) {
	return is_string( $value ) ? esc_url_raw( $value, array( 'https', 'http', 'mailto', 'tel' ) ) : '';
}
