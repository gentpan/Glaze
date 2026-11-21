<?php
$categories = get_the_category();
if ( ! $categories ) {
	return;
}
?>
<span class="post-meta-categories">
	<?php foreach ( $categories as $category ) : ?>
		<?php
		$link = get_category_link( $category->term_id );
		if ( ! $link ) {
			continue;
		}
		$icon = westnote_glaze_icon_markup( get_term_meta( $category->term_id, '_glaze_category_icon', true ) );
		?>
		<a href="<?php echo esc_url( $link ); ?>" rel="category tag">
			<?php echo $icon ?: westnote_glaze_icon_markup( $args['fallback_icon'] ?? '' ); ?>
			<?php echo esc_html( $category->name ); ?>
		</a>
	<?php endforeach; ?>
</span>
