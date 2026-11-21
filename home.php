<?php
get_header();
get_template_part( 'template-parts/layout/start' );
?>
<section class="post-feed" aria-labelledby="latest-posts">
	<?php if ( is_front_page() ) : ?>
		<h1 id="latest-posts" class="screen-reader-text"><?php esc_html_e( 'Latest posts', 'glaze' ); ?></h1>
	<?php else : ?>
		<header class="page-heading glaze-panel">
			<h1 id="latest-posts"><?php echo esc_html( get_the_title( get_queried_object_id() ) ?: __( 'Blog', 'glaze' ) ); ?></h1>
		</header>
	<?php endif; ?>
	<?php get_template_part( 'template-parts/loop' ); ?>
</section>
<?php
get_template_part( 'template-parts/layout/end' );
get_footer();
