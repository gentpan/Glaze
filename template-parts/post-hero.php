<?php
$cover_id = 0;
if ( ! post_password_required() ) {
	$cover_id = (int) get_post_thumbnail_id();
	if ( ! $cover_id ) {
		$featured_posts = get_posts(
			array( 'fields' => 'ids', 'posts_per_page' => 18, 'meta_key' => '_thumbnail_id', 'has_password' => false )
		);
		if ( $featured_posts ) {
			$cover_id = (int) get_post_thumbnail_id( $featured_posts[ get_the_ID() % count( $featured_posts ) ] );
		}
	}
}
?>
<header class="entry-hero glaze-panel">
	<?php if ( $cover_id ) : ?>
		<?php echo wp_get_attachment_image( $cover_id, 'large', false, array( 'class' => 'entry-hero-image', 'alt' => '', 'loading' => 'eager' ) ); ?>
	<?php endif; ?>
	<div class="entry-hero-copy">
		<h1 class="entry-title"><?php echo esc_html( get_the_title() ?: __( 'Untitled', 'glaze' ) ); ?></h1>
		<?php get_template_part( 'template-parts/post-meta', null, array( 'hero' => true ) ); ?>
	</div>
</header>
