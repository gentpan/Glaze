<?php
$link = $args['item'];
$url  = $args['url'];
$host = (string) wp_parse_url( $url, PHP_URL_HOST );
$icon = esc_url( $link->link_image, array( 'http', 'https' ) );
?>
<a class="home-rail-card home-rail-card--link glaze-panel" href="<?php echo esc_url( westnote_glaze_outbound_url( $url ) ); ?>" rel="external">
	<span class="home-rail-meta">
		<span class="home-rail-avatar" aria-hidden="true">
			<?php if ( $icon ) : ?>
				<img width="20" height="20" src="<?php echo $icon; ?>" alt="" loading="lazy" decoding="async">
			<?php else : ?>
				<?php echo westnote_glaze_icon_markup( 'links' ); ?>
			<?php endif; ?>
		</span>
		<span class="home-rail-headline home-rail-link-domain"><?php echo esc_html( $host ); ?></span>
		<span class="home-rail-link-arrow" aria-hidden="true">↗</span>
	</span>
	<span class="home-rail-headline home-rail-link-title"><?php echo esc_html( $link->link_name ?: $host ); ?></span>
	<span class="home-rail-excerpt"><?php echo esc_html( $link->link_description ); ?></span>
</a>
