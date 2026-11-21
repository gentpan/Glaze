<?php
$glaze_comment    = $args['comment'];
$depth            = $args['depth'];
$options          = $args['options'];
$glaze_comment_id = (int) $glaze_comment->comment_ID;
$is_reply         = (bool) $glaze_comment->comment_parent;
$row_classes      = array();
if ( 1 === $depth ) {
	$row_classes[] = 'comment-thread';
}
if ( $args['has_children'] ) {
	$row_classes[] = 'parent';
}
$pending          = '0' === (string) $glaze_comment->comment_approved;
$commenter        = wp_get_current_commenter();
$show_links       = ! $pending || ! empty( $commenter['comment_author'] );
$floor            = $args['floor'] ? number_format_i18n( $args['floor'] ) : '—';
$avatar_class     = $is_reply ? 'comment-avatar-inline' : 'comment-avatar-compact';
$avatar_size      = $is_reply ? 20 : $options['avatar_size'];
$avatar           = $options['avatar_size'] ? get_avatar( $glaze_comment, $avatar_size, '', '', array( 'loading' => 'lazy', 'decoding' => 'async' ) ) : '';
$avatar_badge = '';
if ( $avatar && ( $args['is_admin'] || $args['is_blogroll'] ) ) {
	$badge_class  = $args['is_admin'] ? 'comment-admin-badge' : 'comment-blogroll-badge';
	$badge_label  = $args['is_admin'] ? esc_attr__( 'Comment administrator', 'glaze' ) : esc_attr__( 'Blogroll', 'glaze' );
	$avatar_badge = '<span class="comment-avatar-badge ' . $badge_class . '" role="img" tabindex="0" aria-label="' . $badge_label . '" data-glaze-tooltip="' . $badge_label . '"><svg viewBox="12 13 24 24" fill="none" aria-hidden="true"><path d="M17.25 25.25l4.5 4.5 9-9.5" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
}
$avatar_markup = $avatar ? '<span class="comment-avatar ' . esc_attr( $avatar_class ) . '"><span class="comment-avatar-photo" aria-hidden="true">' . $avatar . '</span>' . $avatar_badge . '</span>' : '';
$date             = get_comment_date( '', $glaze_comment );
$date_time        = $date . ' ' . get_comment_time( 'H:i:s', false, true, $glaze_comment );
$datetime         = get_comment_time( DATE_W3C, false, false, $glaze_comment );
$timestamp        = strtotime( $datetime );
$relative_date    = $date;
if ( false !== $timestamp ) {
	$now     = time();
	$elapsed = $now - $timestamp;
	if ( $elapsed < MINUTE_IN_SECONDS ) {
		$relative_date = __( 'Just now', 'glaze' );
	} else {
		$difference = human_time_diff( $timestamp, $now );
		if ( $elapsed >= WEEK_IN_SECONDS && $elapsed < MONTH_IN_SECONDS ) {
			$days = (int) floor( $elapsed / DAY_IN_SECONDS );
			/* translators: %s: Number of days. */
			$difference = sprintf( _n( '%s day', '%s days', $days ), number_format_i18n( $days ) );
		}
		/* translators: %s: Human-readable time difference. */
		$relative_date = sprintf( __( '%s ago', 'glaze' ), $difference );
	}
}
$devices          = array_filter( array( $args['browser'], $args['system'] ) );
$location         = westnote_glaze_comment_location( $glaze_comment );
$reply            = '';
if ( $show_links ) {
	$reply_icon = westnote_glaze_font_awesome_icon( 'comment' );
	$reply      = get_comment_reply_link(
		array_merge(
			$options,
			array(
				'add_below'  => 'div-comment',
				'depth'      => $depth,
				'reply_text' => $reply_icon,
				'login_text' => $reply_icon . '<span class="screen-reader-text">' . esc_html__( 'Log in to Reply' ) . '</span>',
			)
		),
		$glaze_comment
	);
	if ( $reply ) {
		$reply_tag = new WP_HTML_Tag_Processor( $reply );
		if ( $reply_tag->next_tag( 'A' ) ) {
			/* translators: %s: Comment author name. */
			$reply_hint = sprintf( __( 'Reply to %s and join the conversation', 'glaze' ), get_comment_author( $glaze_comment ) );
			$reply_tag->set_attribute( 'data-glaze-tooltip', $reply_tag->has_class( 'comment-reply-login' ) ? __( 'Log in to Reply' ) : $reply_hint );
			$reply = $reply_tag->get_updated_html();
		}
	}
}
/* translators: %s: Comment floor number. */
$floor_label = $args['floor'] ? sprintf( __( 'Comment #%s', 'glaze' ), $floor ) : __( 'Awaiting moderation', 'glaze' );
?>
<li id="comment-<?php echo esc_attr( $glaze_comment_id ); ?>" <?php comment_class( $row_classes, $glaze_comment ); ?>>
	<article id="div-comment-<?php echo esc_attr( $glaze_comment_id ); ?>" class="comment-body">
		<header class="comment-meta">
			<div class="comment-meta-details" role="group" aria-label="<?php esc_attr_e( 'Comment details', 'glaze' ); ?>" tabindex="0">
				<?php echo $avatar_markup; ?>
				<div class="comment-author vcard" title="<?php echo esc_attr( get_comment_author( $glaze_comment ) ); ?>">
					<?php echo $show_links ? get_comment_author_link( $glaze_comment ) : esc_html( get_comment_author( $glaze_comment ) ); ?>
				</div>
				<?php if ( $location ) : ?>
					<?php $location_label = westnote_glaze_comment_location_label( $location ); ?>
					<span class="comment-location" tabindex="0" data-glaze-tooltip="<?php echo esc_attr( $location_label ); ?>" aria-label="<?php echo esc_attr( $location_label ); ?>">
						<img src="<?php echo esc_url( $location['flag'] ); ?>" width="16" height="16" alt="" loading="lazy" decoding="async" referrerpolicy="no-referrer">
						<span class="comment-location-name"><?php echo esc_html( $location['place'] ?: $location['country'] ); ?></span>
					</span>
				<?php elseif ( false === $location ) : ?>
					<span class="comment-location" data-comment-location-url="<?php echo esc_url( rest_url( 'glaze/v1/comment-location/' . $glaze_comment_id ) ); ?>" hidden></span>
				<?php endif; ?>
				<span class="comment-device-capsule">
					<?php foreach ( $devices as $device ) : ?>
						<span class="comment-device" tabindex="0" data-glaze-tooltip="<?php echo esc_attr( $device['label'] ); ?>">
							<?php if ( 'globe' === $device['icon'] ) : ?>
								<span role="img" aria-label="<?php echo esc_attr( $device['label'] ); ?>">
									<svg class="comment-device-globe" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
										<circle cx="12" cy="12" r="9.5"/>
										<ellipse cx="12" cy="12" rx="4.25" ry="9.5"/>
										<path d="M2.5 12h19"/>
									</svg>
								</span>
							<?php elseif ( 'apple' === $device['icon'] ) : ?>
								<img class="comment-device-apple-light" src="<?php echo esc_url( get_parent_theme_file_uri( 'assets/demo/the-svg/apple/light.svg' ) ); ?>" width="16" height="16" alt="<?php echo esc_attr( $device['label'] ); ?>" loading="lazy" decoding="async">
								<img class="comment-device-apple-dark" src="<?php echo esc_url( get_parent_theme_file_uri( 'assets/demo/the-svg/apple/dark.svg' ) ); ?>" width="16" height="16" alt="<?php echo esc_attr( $device['label'] ); ?>" loading="lazy" decoding="async">
							<?php elseif ( $device['icon'] ) : ?>
								<?php
								$icon_path = 'google-chrome' === $device['icon'] ? 'assets/images/comment-client/chrome.svg' : 'assets/demo/the-svg/' . $device['icon'] . '/default.svg';
								?>
								<img src="<?php echo esc_url( get_parent_theme_file_uri( $icon_path ) ); ?>" width="16" height="16" alt="<?php echo esc_attr( $device['label'] ); ?>" loading="lazy" decoding="async">
							<?php else : ?>
								<span role="img" aria-label="<?php echo esc_attr( $device['label'] ); ?>"><?php echo westnote_glaze_icon_markup( 'about' ); ?></span>
							<?php endif; ?>
						</span>
					<?php endforeach; ?>
				</span>
			</div>
			<?php if ( ! $is_reply ) : ?>
				<span class="comment-floor" title="<?php echo esc_attr( $floor_label ); ?>">
					<?php if ( $args['floor'] ) : ?>
						<span class="comment-floor-watermark" aria-hidden="true"><?php echo esc_html_x( '#', 'Comment floor watermark', 'glaze' ); ?></span>
					<?php endif; ?>
					<?php echo esc_html( $floor ); ?>
				</span>
			<?php endif; ?>
			<div class="comment-meta-actions">
				<a class="comment-metadata" href="<?php echo esc_url( get_comment_link( $glaze_comment, $options ) ); ?>" data-glaze-tooltip="<?php echo esc_attr( $date_time ); ?>" aria-label="<?php echo esc_attr( $date_time ); ?>">
					<time datetime="<?php echo esc_attr( $datetime ); ?>"><?php echo esc_html( $relative_date ); ?></time>
				</a>
				<?php if ( $reply ) : ?>
					<span class="comment-reply-action"><span class="comment-reply-date" aria-hidden="true" data-glaze-tooltip="<?php echo esc_attr( $date_time ); ?>"></span><?php echo $reply; ?></span>
				<?php endif; ?>
			</div>
		</header>

		<?php if ( $pending ) : ?>
			<em class="comment-awaiting-moderation">
				<?php echo esc_html( ! empty( $commenter['comment_author_email'] ) ? __( 'Your comment is awaiting moderation.' ) : __( 'Your comment is awaiting moderation. This is a preview; your comment will be visible after it has been approved.' ) ); ?>
			</em>
		<?php endif; ?>
		<div class="comment-content">
			<?php echo westnote_glaze_render_comment_body( $glaze_comment, $options ); ?>
		</div>
	</article>
