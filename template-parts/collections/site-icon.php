<?php
$icon = esc_url( $args['url'], array( 'https' ) );
?>
<span class="collection-site-icon squircle" aria-hidden="true">
	<?php echo westnote_glaze_icon_markup( $args['fallback'] ); ?>
	<?php if ( $icon ) : ?>
		<img class="collection-site-image" src="<?php echo $icon; ?>" alt="" width="26" height="26" loading="lazy" decoding="async">
	<?php endif; ?>
</span>
