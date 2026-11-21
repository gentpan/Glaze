<?php
$links = get_bookmarks( array( 'hide_invisible' => true, 'orderby' => 'name' ) );
if ( ! $links ) {
	return;
}
?>
<ul class="friend-list" role="list">
	<?php foreach ( $links as $link ) : ?>
		<?php $host = wp_parse_url( $link->link_url, PHP_URL_HOST ); ?>
		<li>
			<a class="friend-link" href="<?php echo esc_url( westnote_glaze_outbound_url( $link->link_url ) ); ?>" rel="external">
				<?php get_template_part( 'template-parts/collections/site-icon', null, array( 'url' => $link->link_image, 'fallback' => 'links' ) ); ?>
				<span class="friend-name"><?php echo esc_html( $link->link_name ?: $host ); ?></span>
				<?php if ( $link->link_description ) : ?>
					<span class="friend-description"><?php echo esc_html( $link->link_description ); ?></span>
				<?php endif; ?>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
