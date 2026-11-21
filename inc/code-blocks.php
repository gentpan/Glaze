<?php
defined( 'ABSPATH' ) || exit;

function westnote_glaze_enqueue_code_highlight( $content ) {
	if ( is_singular() && in_the_loop() && is_main_query() && false !== stripos( $content, '<pre' ) ) {
		$script = 'assets/js/code-highlight.min.js';
		$path   = get_parent_theme_file_path( $script );
		if ( is_file( $path ) ) {
			$handle = 'westnote-glaze-code-highlight';
			wp_enqueue_script( $handle, get_parent_theme_file_uri( $script ), array(), (string) filemtime( $path ), true );
			wp_localize_script(
				$handle,
				'glazeCodeLabels',
				array(
					'copy'     => __( 'Copy code', 'glaze' ),
					'copied'   => __( 'Code copied', 'glaze' ),
					'error'    => __( 'Could not copy the code.', 'glaze' ),
					'collapse' => __( 'Collapse code', 'glaze' ),
					'expand'   => __( 'Expand code', 'glaze' ),
				)
			);
		}
	}
	return $content;
}
add_filter( 'the_content', 'westnote_glaze_enqueue_code_highlight', 30 );
