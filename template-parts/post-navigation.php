<?php
$arrow    = '<span class="post-nav-arrow" aria-hidden="true"></span>';
$previous = get_previous_post_link( '%link', $arrow . '<span class="screen-reader-text">' . esc_html__( 'Previous post', 'glaze' ) . ' </span><span class="post-nav-title">%title</span>' );
$next     = get_next_post_link( '%link', '<span class="screen-reader-text">' . esc_html__( 'Next post', 'glaze' ) . ' </span><span class="post-nav-title">%title</span>' . $arrow );
?>
<nav class="navigation post-navigation" aria-label="<?php esc_attr_e( 'Posts' ); ?>">
	<h2 class="screen-reader-text"><?php esc_html_e( 'Post navigation' ); ?></h2>
	<div class="nav-links">
		<div class="nav-previous">
			<?php if ( $previous ) : ?>
				<?php echo $previous; ?>
			<?php else : ?>
				<span class="post-nav-placeholder squircle">
					<?php echo $arrow; ?>
					<span class="post-nav-title"><?php esc_html_e( 'No older article', 'glaze' ); ?></span>
				</span>
			<?php endif; ?>
		</div>
		<div class="nav-next">
			<?php if ( $next ) : ?>
				<?php echo $next; ?>
			<?php else : ?>
				<span class="post-nav-placeholder squircle">
					<span class="post-nav-title"><?php esc_html_e( 'No newer article', 'glaze' ); ?></span>
					<?php echo $arrow; ?>
				</span>
			<?php endif; ?>
		</div>
	</div>
</nav>
