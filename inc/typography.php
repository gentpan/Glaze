<?php
defined( 'ABSPATH' ) || exit;

function westnote_glaze_google_fonts() {
	return array(
		'chinese' => array(
			'noto-sans-sc'  => array( 'Noto Sans SC', 'wght@100..900' ),
			'noto-serif-sc' => array( 'Noto Serif SC', 'wght@200..900' ),
		),
		'latin'   => array(
			'noto-sans'  => array( 'Noto Sans', 'ital,wght@0,100..900;1,100..900' ),
			'agrave-pro' => array( 'Agrave Pro', 'wght@100..700' ),
		),
	);
}

function westnote_glaze_font_settings() {
	return array(
		'site_chinese'  => array( 'chinese', 'system', __( 'Site Chinese font', 'glaze' ) ),
		'site_latin'    => array( 'latin', 'auto', __( 'Site Latin font', 'glaze' ) ),
		'entry_chinese' => array( 'chinese', 'inherit', __( 'Content Chinese font', 'glaze' ) ),
		'entry_latin'   => array( 'latin', 'inherit', __( 'Content Latin font', 'glaze' ) ),
	);
}

function westnote_glaze_font_choices( $script, $default ) {
	$choices = 'inherit' === $default ? array( 'inherit' => __( 'Follow site setting', 'glaze' ) ) : array();
	if ( 'chinese' === $script ) {
		return $choices + array(
			'system'        => __( 'System font', 'glaze' ),
			'noto-sans-sc'  => __( 'Noto Sans SC (Source Han Sans)', 'glaze' ),
			'noto-serif-sc' => __( 'Noto Serif SC (Source Han Serif)', 'glaze' ),
		);
	}
	$choices['auto'] = __( 'Follow Chinese font', 'glaze' );
	foreach ( westnote_glaze_google_fonts()['latin'] as $id => list( $family ) ) {
		$choices[ $id ] = $family;
	}
	return $choices;
}

function westnote_glaze_sanitize_font( $value, $script, $default ) {
	if ( ! is_string( $value ) ) {
		return $default;
	}
	$fallback = 'chinese' === $script ? 'system' : 'auto';
	return $value === $default || $value === $fallback || isset( westnote_glaze_google_fonts()[ $script ][ $value ] ) ? $value : $default;
}

function westnote_glaze_get_font( $setting, $script, $default ) {
	return westnote_glaze_sanitize_font( get_theme_mod( "westnote_glaze_font_{$setting}", $default ), $script, $default );
}

function westnote_glaze_active_fonts() {
	$fonts = array();
	foreach ( westnote_glaze_font_settings() as $setting => list( $script, $default ) ) {
		$fonts[ $setting ] = westnote_glaze_get_font( $setting, $script, $default );
	}

	$active = array( 'site-latin' => $fonts['site_latin'], 'site-chinese' => $fonts['site_chinese'] );
	if ( is_singular() ) {
		$active['entry-latin']   = 'inherit' === $fonts['entry_latin'] ? $fonts['site_latin'] : $fonts['entry_latin'];
		$active['entry-chinese'] = 'inherit' === $fonts['entry_chinese'] ? $fonts['site_chinese'] : $fonts['entry_chinese'];
	}
	return $active;
}

function westnote_glaze_font_classes( $classes ) {
	$catalog = westnote_glaze_google_fonts();
	$fonts   = westnote_glaze_active_fonts();
	$custom  = array_intersect( $fonts, array_keys( $catalog['latin'] + $catalog['chinese'] ) );
	foreach ( $custom as $slot => $font ) {
		$classes[] = 'glaze-font-' . $slot . '-' . $font;
	}
	if ( $custom && isset( $fonts['entry-latin'] ) ) {
		$classes[] = 'glaze-entry-font';
	}
	return $classes;
}
add_filter( 'body_class', 'westnote_glaze_font_classes' );

function westnote_glaze_enqueue_fonts() {
	$catalog  = westnote_glaze_google_fonts();
	$families = $catalog['latin'] + $catalog['chinese'];
	$requests = array();
	foreach ( array_unique( westnote_glaze_active_fonts() ) as $id ) {
		if ( isset( $families[ $id ] ) ) {
			list( $family, $axes ) = $families[ $id ];
			$requests[]           = 'family=' . str_replace( ' ', '+', $family ) . ':' . $axes;
		}
	}
	if ( $requests ) {
		wp_enqueue_style( 'westnote-glaze-google-fonts', 'https://fonts.googleapis.com/css2?' . implode( '&', $requests ) . '&display=swap', array(), null );
	}
}
add_action( 'wp_enqueue_scripts', 'westnote_glaze_enqueue_fonts', 20 );
