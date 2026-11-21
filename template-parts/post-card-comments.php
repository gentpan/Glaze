<?php
if ( post_password_required() ) {
	return;
}

$count           = (int) get_comments_number();
$avatar_comments = $count ? westnote_glaze_post_card_avatar_comments( get_the_ID() ) : array();
$formatted_count = number_format_i18n( $count );
$badge_count     = $count > 999 ? '999+' : ( $count ? '+' . $formatted_count : $formatted_count );
/* translators: %s: localized number of comments. */
$label = sprintf( _n( '%s comment', '%s comments', $count, 'glaze' ), $formatted_count );
$url   = $count || comments_open() ? get_comments_link() : '';
?>
<?php if ( $url ) : ?>
	<a class="post-card-comment-group t-avatar-group" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
<?php else : ?>
	<span class="post-card-comment-group" aria-label="<?php echo esc_attr( $label ); ?>">
<?php endif; ?>
	<?php foreach ( $avatar_comments as $recent_comment ) : ?>
		<?php $avatar = get_avatar( $recent_comment, 24, '', '', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
		<span class="post-card-comment-avatar t-avatar" aria-hidden="true">
			<?php if ( $avatar ) : ?>
				<?php echo $avatar; ?>
			<?php else : ?>
				<?php echo esc_html( wp_html_excerpt( get_comment_author( $recent_comment ), 1 ) ); ?>
			<?php endif; ?>
		</span>
	<?php endforeach; ?>
	<span class="post-card-comment-count t-avatar" aria-hidden="true" dir="ltr"<?php if ( $count > 999 ) : ?> title="<?php echo esc_attr( $label ); ?>"<?php endif; ?>><?php echo esc_html( $badge_count ); ?></span>
<?php if ( $url ) : ?>
	</a>
<?php else : ?>
	</span>
<?php endif; ?>
