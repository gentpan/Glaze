<?php
$types = westnote_glaze_home_rail_types();
$content = array();
$rails = array();
foreach ( array_keys( westnote_glaze_home_rail_defaults() ) as $side ) {
	$type = westnote_glaze_home_rail_type( $side );
	$content[ $type ] ??= westnote_glaze_home_rail_content( $type );
	if ( $content[ $type ]['items'] ) {
		$rails[ $side ] = $content[ $type ] + array( 'type' => $type, 'title' => $types[ $type ] );
	}
}
if ( ! $rails ) {
	return;
}
?>
<div class="home-rails">
	<?php foreach ( $rails as $side => $rail ) : ?>
		<?php
		$list_id    = 'home-rail-list-' . $side;
		$title_id   = 'home-rail-' . $side;
		$show_title = ! in_array( $rail['type'], array( 'links', 'comments', 'subscriptions' ), true );
		?>
		<aside class="home-rail home-rail--<?php echo esc_attr( $side ); ?>" data-glaze-home-rail="<?php echo esc_attr( $side ); ?>"
			<?php if ( $show_title ) : ?>aria-labelledby="<?php echo esc_attr( $title_id ); ?>"<?php else : ?>aria-label="<?php echo esc_attr( $rail['title'] ); ?>"<?php endif; ?>>
			<?php if ( 'links' === $rail['type'] ) : ?>
				<div class="home-rail-actions" data-glaze-blogroll-actions hidden>
					<span class="home-rail-status screen-reader-text" role="status" data-glaze-blogroll-status
						data-error="<?php esc_attr_e( 'Could not refresh links. Try again.', 'glaze' ); ?>"
						data-success="<?php esc_attr_e( 'Links refreshed.', 'glaze' ); ?>"
						data-empty="<?php esc_attr_e( 'No links to show.', 'glaze' ); ?>"></span>
					<button type="button" class="home-rail-refresh squircle" data-glaze-blogroll-refresh="<?php echo esc_url( rest_url( 'glaze/v1/blogroll' ) ); ?>" data-glaze-tooltip aria-label="<?php esc_attr_e( 'Shuffle links', 'glaze' ); ?>" aria-controls="<?php echo esc_attr( $list_id ); ?>">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 7v5h-5M4 17v-5h5M6 8a7 7 0 0 1 11.7-2.2L20 8M4 16l2.3 2.2A7 7 0 0 0 18 16"/></svg>
					</button>
				</div>
			<?php elseif ( $show_title ) : ?>
				<h2 class="home-rail-title" id="<?php echo esc_attr( $title_id ); ?>">
					<?php if ( ! empty( $rail['url'] ) ) : ?>
						<a href="<?php echo esc_url( $rail['url'] ); ?>"><?php echo esc_html( $rail['title'] ); ?> <span aria-hidden="true">›</span></a>
					<?php else : ?>
						<?php echo esc_html( $rail['title'] ); ?>
					<?php endif; ?>
				</h2>
			<?php endif; ?>
			<ol class="home-rail-list" id="<?php echo esc_attr( $list_id ); ?>">
				<?php get_template_part( 'template-parts/home-rail-items', null, $rail ); ?>
			</ol>
		</aside>
	<?php endforeach; ?>
</div>
