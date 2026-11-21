<?php
$is_post    = 'post' === get_post_type();
$show_cover = has_post_thumbnail() && ! post_password_required();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $show_cover ? 'post-card post-card-has-image glaze-panel' : 'post-card glaze-panel' ); ?>>
	<div class="<?php echo $show_cover ? 'post-card-cover' : 'post-card-copy'; ?>">
		<?php if ( $show_cover ) : ?>
			<a class="post-card-image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
				<?php the_post_thumbnail( 'large' ); ?>
			</a>
		<?php endif; ?>
		<div class="post-card-heading">
			<h2 class="post-card-title">
				<?php if ( $is_post ) : ?><span class="post-card-title-mark" aria-hidden="true">#</span><?php endif; ?>
				<a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ?: __( 'Untitled', 'glaze' ) ); ?></a>
			</h2>
			<?php if ( $is_post ) : ?>
				<?php get_template_part( 'template-parts/post-categories', null, array( 'fallback_icon' => 'categories' ) ); ?>
			<?php endif; ?>
		</div>
	<?php if ( $show_cover ) : ?>
	</div>
	<div class="post-card-copy">
	<?php endif; ?>
		<div class="post-excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt(), true ) ); ?></div>
		<?php if ( $is_post ) : ?>
			<div class="post-card-footer">
				<?php get_template_part( 'template-parts/post-meta', null, array( 'card' => true ) ); ?>
				<?php get_template_part( 'template-parts/post-card-comments' ); ?>
			</div>
		<?php endif; ?>
	</div>
</article>
