<?php
$item    = $args['item'];
$icon    = esc_url( $item['icon'], array( 'http', 'https' ) );
$summary = wp_strip_all_tags( $item['summary'], true );
?>
<a class="home-rail-card glaze-panel" href="<?php echo esc_url( $args['url'] ); ?>" rel="external">
	<span class="home-rail-meta">
		<span class="home-rail-avatar" aria-hidden="true">
			<?php if ( $icon ) : ?>
				<img width="20" height="20" src="<?php echo $icon; ?>" alt="" loading="lazy" decoding="async">
			<?php else : ?>
				<?php echo westnote_glaze_icon_markup( 'feeds' ); ?>
			<?php endif; ?>
		</span>
		<span class="home-rail-author"><?php echo esc_html( $item['source'] ); ?></span>
		<?php if ( $item['published'] ) : ?>
			<time datetime="<?php echo esc_attr( wp_date( DATE_W3C, $item['published'] ) ); ?>"><?php echo esc_html( wp_date( 'n/j', $item['published'] ) ); ?></time>
		<?php endif; ?>
	</span>
	<span class="home-rail-headline"><?php echo esc_html( $item['title'] ?: __( 'Untitled', 'glaze' ) ); ?></span>
	<span class="home-rail-excerpt"><?php echo esc_html( $summary ); ?></span>
</a>
