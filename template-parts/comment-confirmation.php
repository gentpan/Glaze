<?php
$state = $args['state'];
?>
<div class="glaze-comment-confirmation" data-glaze-comment-confirmation="<?php echo esc_attr( $state ); ?>" aria-hidden="true" hidden>
	<div class="glaze-comment-flight">
		<img class="glaze-comment-plane" width="144" height="96" src="<?php echo esc_url( get_parent_theme_file_uri( 'assets/images/comment-flight/glow.svg' ) ); ?>" alt="" decoding="async">
		<?php if ( 'pending' === $state ) : ?>
			<span class="glaze-comment-gate" aria-hidden="true"></span>
			<span class="glaze-comment-outcome glaze-comment-clock" data-state="out">
				<svg width="52" height="52" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<circle cx="24" cy="24" r="17"/>
					<path d="M24 14v11l7 4"/>
				</svg>
			</span>
		<?php elseif ( 'failed' === $state ) : ?>
			<span class="glaze-comment-gate" aria-hidden="true"></span>
			<span class="glaze-comment-outcome glaze-comment-exclamation" data-state="out">
				<svg width="52" height="52" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" aria-hidden="true">
					<circle cx="24" cy="24" r="17"/>
					<path d="M24 15v10m0 7h.01"/>
				</svg>
			</span>
		<?php else : ?>
			<span class="glaze-comment-outcome t-success-check" data-state="out" aria-hidden="true">
				<svg width="48" height="48" viewBox="0 0 48 48" fill="none">
					<path pathLength="1" d="M17.25 25.25l4.5 4.5 9-9.5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</span>
		<?php endif; ?>
	</div>
	<p><?php echo esc_html( $args['message'] ); ?></p>
</div>
