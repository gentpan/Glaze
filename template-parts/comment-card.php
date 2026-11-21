<?php
$rail_comment = $args['item'];
$author       = get_comment_author( $rail_comment );
$title        = get_the_title( (int) $rail_comment->comment_post_ID );
$avatar       = get_avatar( $rail_comment, 20, '', '', array( 'loading' => 'lazy', 'decoding' => 'async' ) );
$comment_text = westnote_glaze_render_comment_images( get_comment_text( $rail_comment ) );
$excerpt      = trim( wp_html_excerpt( strip_shortcodes( $comment_text ), 180, '…' ) );
?>
<a class="home-rail-card home-rail-card--comment glaze-panel" href="<?php echo esc_url( get_comment_link( $rail_comment ) ); ?>">
	<span class="home-rail-meta">
		<span class="home-rail-avatar" aria-hidden="true">
			<?php if ( $avatar ) : ?>
				<?php echo $avatar; ?>
			<?php else : ?>
				<?php echo esc_html( wp_html_excerpt( $author, 1 ) ); ?>
			<?php endif; ?>
		</span>
		<span class="home-rail-author"><?php echo esc_html( $author ); ?></span>
		<time datetime="<?php echo esc_attr( get_comment_date( DATE_W3C, $rail_comment ) ); ?>"><?php echo esc_html( get_comment_date( 'n/j', $rail_comment ) ); ?></time>
	</span>
	<span class="home-rail-headline"><?php echo esc_html( $title ?: __( 'Untitled', 'glaze' ) ); ?></span>
	<span class="home-rail-excerpt"><?php echo esc_html( $excerpt ?: __( 'View comment', 'glaze' ) ); ?></span>
</a>
