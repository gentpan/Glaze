<?php
$presets = westnote_glaze_footer_icon_presets();
for ( $slot = 1; $slot <= 6; $slot++ ) :
	$url = westnote_glaze_footer_link_url( get_theme_mod( "westnote_glaze_footer_url_{$slot}", '' ) );
	if ( '' === $url ) {
		continue;
	}
	$preset = westnote_glaze_sanitize_footer_icon_preset( get_theme_mod( "westnote_glaze_footer_preset_{$slot}", '' ) );
	$icon   = '' !== $preset ? westnote_glaze_font_awesome_icon( $preset ) : westnote_glaze_sanitize_svg_icon( get_theme_mod( "westnote_glaze_footer_icon_{$slot}", '' ) );
	if ( '' === $icon ) {
		continue;
	}
	?>
	<a class="footer-control footer-custom-link squircle" href="<?php echo esc_url( $url, array( 'https', 'http', 'mailto', 'tel' ) ); ?>" aria-label="<?php echo esc_attr( $presets[ $preset ] ?? $url ); ?>">
		<span aria-hidden="true"><?php echo $icon; // Bundled icon or sanitized custom SVG. ?></span>
	</a>
<?php endfor; ?>
