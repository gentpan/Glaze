<?php
$index = 0;
foreach ( $args['items'] as $url => $item ) :
	?>
	<li class="home-rail-item" data-glaze-rail-card<?php if ( 'links' === $args['type'] ) : ?> data-glaze-rail-link-id="<?php echo esc_attr( $item->link_id ); ?>"<?php endif; ?>>
		<?php
		get_template_part(
			'template-parts/' . $args['template'],
			null,
			array( 'item' => $item, 'url' => $url, 'index' => $index, 'collection_url' => $args['url'] ?? '' )
		);
		++$index;
		?>
		<button type="button" class="home-rail-close squircle" data-glaze-rail-close aria-label="<?php esc_attr_e( 'Dismiss card', 'glaze' ); ?>" hidden>
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
		</button>
	</li>
<?php endforeach; ?>
