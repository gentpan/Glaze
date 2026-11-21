<?php $shortcut_post_id = get_queried_object_id(); ?>
<nav class="site-jump-navigation squircle" aria-label="<?php esc_attr_e( 'Article shortcuts', 'glaze' ); ?>">
	<a class="squircle" href="#top" data-glaze-tooltip aria-label="<?php esc_attr_e( 'Back to top', 'glaze' ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V7m-4 4 4-4 4 4M5 4h14"/></svg>
	</a>
	<?php if ( ! post_password_required( $shortcut_post_id ) && ( comments_open( $shortcut_post_id ) || get_comments_number( $shortcut_post_id ) ) ) : ?>
	<a class="squircle" href="#comments" data-glaze-tooltip aria-label="<?php esc_attr_e( 'Go to post comments', 'glaze' ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11.6a7.5 7.5 0 0 1-7.9 7.1 9 9 0 0 1-2.8-.5L5 20l.9-3.6a6.5 6.5 0 0 1-1.8-4.8A7.5 7.5 0 0 1 12 4.5a7.5 7.5 0 0 1 8 7.1Z"/><path d="M8.5 11.5h7"/></svg>
	</a>
	<?php endif; ?>
	<a class="squircle" href="#site-bottom" data-glaze-tooltip aria-label="<?php esc_attr_e( 'Go to bottom', 'glaze' ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v12m-4-4 4 4 4-4M5 20h14"/></svg>
	</a>
</nav>
