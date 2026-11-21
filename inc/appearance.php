<?php
defined( 'ABSPATH' ) || exit;

function westnote_glaze_palettes() {
	return array(
		'green'    => __( 'Lime', 'glaze' ),
		'mint'     => __( 'Mint', 'glaze' ),
		'teal'     => __( 'Celadon', 'glaze' ),
		'blue'     => __( 'Sky blue', 'glaze' ),
		'indigo'   => __( 'Indigo', 'glaze' ),
		'violet'   => __( 'Wisteria', 'glaze' ),
		'pink'     => __( 'Blossom', 'glaze' ),
		'coral'    => __( 'Coral', 'glaze' ),
		'amber'    => __( 'Amber', 'glaze' ),
		'slate'    => __( 'Graphite', 'glaze' ),
		'cinnabar' => __( 'Cinnabar', 'glaze' ),
		'bamboo'   => __( 'Bamboo', 'glaze' ),
		'ochre'    => __( 'Autumn ochre', 'glaze' ),
		'lotus'    => __( 'Lotus mauve', 'glaze' ),
	);
}

function westnote_glaze_schemes() {
	return array(
		'light' => __( 'Light appearance', 'glaze' ),
		'dark'  => __( 'Dark appearance', 'glaze' ),
	);
}

function westnote_glaze_backgrounds() {
	return array(
		'blue-preview' => __( 'Light ripples on Sky blue', 'glaze' ),
		'ripples'      => __( 'Light ripples on all palettes', 'glaze' ),
		'none'         => __( 'No background effect', 'glaze' ),
	);
}

function westnote_glaze_sanitize_background( $value ) {
	return is_string( $value ) && isset( westnote_glaze_backgrounds()[ $value ] ) ? $value : 'blue-preview';
}

function westnote_glaze_get_background() {
	return westnote_glaze_sanitize_background( get_theme_mod( 'westnote_glaze_background', 'blue-preview' ) );
}

function westnote_glaze_corner_modes() {
	return array(
		'square'   => __( 'Square', 'glaze' ),
		'squircle' => __( 'Squircle', 'glaze' ),
		'round'    => __( 'Round', 'glaze' ),
	);
}

function westnote_glaze_sanitize_palette( $value ) {
	return is_string( $value ) && isset( westnote_glaze_palettes()[ $value ] ) ? $value : 'green';
}

function westnote_glaze_sanitize_scheme( $value ) {
	return 'dark' === $value ? 'dark' : 'light';
}

function westnote_glaze_sanitize_corner_mode( $value ) {
	if ( 'apple' === $value ) {
		return 'round';
	}
	return is_string( $value ) && isset( westnote_glaze_corner_modes()[ $value ] ) ? $value : 'squircle';
}

function westnote_glaze_get_palette() {
	return westnote_glaze_sanitize_palette( get_theme_mod( 'westnote_glaze_palette', 'green' ) );
}

function westnote_glaze_get_scheme() {
	return westnote_glaze_sanitize_scheme( get_theme_mod( 'westnote_glaze_scheme', 'light' ) );
}

function westnote_glaze_get_corner_mode() {
	return westnote_glaze_sanitize_corner_mode( get_theme_mod( 'westnote_glaze_corner_mode', 'squircle' ) );
}

function westnote_glaze_content_width( $value ) {
	return is_numeric( $value ) ? max( 360, min( 1600, (int) $value ) ) : 600;
}

function westnote_glaze_get_content_width() {
	return westnote_glaze_content_width( get_theme_mod( 'westnote_glaze_content_width', 600 ) );
}
