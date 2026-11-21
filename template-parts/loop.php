<?php if ( have_posts() ) : ?>
	<div class="post-list">
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<?php get_template_part( 'template-parts/content' ); ?>
		<?php endwhile; ?>
	</div>
	<?php $motto = is_home() ? westnote_glaze_get_pagination_motto() : array( 'text' => '' ); ?>
	<?php if ( '' !== $motto['text'] ) : ?>
		<div class="post-feed-bottom">
			<?php the_posts_pagination( westnote_glaze_pagination_args() ); ?>
			<div class="post-feed-motto"><?php echo westnote_glaze_pagination_motto_markup( $motto ); ?></div>
		</div>
	<?php else : ?>
		<?php the_posts_pagination( westnote_glaze_pagination_args() ); ?>
	<?php endif; ?>
<?php else : ?>
	<div class="empty-state glaze-panel">
		<p>
			<?php
			if ( is_search() ) {
				esc_html_e( 'No results. Try a different search term.', 'glaze' );
			} else {
				esc_html_e( 'No posts found.', 'glaze' );
			}
			?>
		</p>
	</div>
<?php endif; ?>
