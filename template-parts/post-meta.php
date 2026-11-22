<?php
$is_card      = ! empty( $args['card'] );
$is_hero      = ! empty( $args['hero'] );
$is_home_card = $is_card && is_front_page();
$date         = get_the_date();
$date_tooltip = $is_home_card ? $date . ' ' . get_the_time() : '';
if ( $is_card ) {
	$timestamp = get_post_timestamp();
	$now       = time();
	$elapsed   = false !== $timestamp ? $now - $timestamp : -1;
	if ( 0 <= $elapsed && ( $is_home_card || $elapsed <= 30 * DAY_IN_SECONDS ) ) {
		if ( $elapsed < MINUTE_IN_SECONDS ) {
			$date = __( 'Just now', 'glaze' );
		} elseif ( ! $is_home_card ) {
			/* translators: %s: Human-readable time difference. */
			$date = sprintf( __( '%s ago', 'glaze' ), human_time_diff( $timestamp, $now ) );
		} else {
			if ( $elapsed >= YEAR_IN_SECONDS ) {
				$count = intdiv( $elapsed, YEAR_IN_SECONDS );
				/* translators: %s: Number of years. */
				$format = _n( '%s year ago', '%s years ago', $count, 'glaze' );
			} elseif ( $elapsed >= MONTH_IN_SECONDS ) {
				$count = intdiv( $elapsed, MONTH_IN_SECONDS );
				/* translators: %s: Number of months. */
				$format = _n( '%s month ago', '%s months ago', $count, 'glaze' );
			} elseif ( $elapsed >= DAY_IN_SECONDS ) {
				$count = intdiv( $elapsed, DAY_IN_SECONDS );
				/* translators: %s: Number of days. */
				$format = _n( '%s day ago', '%s days ago', $count, 'glaze' );
			} elseif ( $elapsed >= HOUR_IN_SECONDS ) {
				$count = intdiv( $elapsed, HOUR_IN_SECONDS );
				/* translators: %s: Number of hours. */
				$format = _n( '%s hour ago', '%s hours ago', $count, 'glaze' );
			} else {
				$count = intdiv( $elapsed, MINUTE_IN_SECONDS );
				/* translators: %s: Number of minutes. */
				$format = _n( '%s minute ago', '%s minutes ago', $count, 'glaze' );
			}
			$date = sprintf( $format, number_format_i18n( $count ) );
		}
	}
}
?>
<div class="post-meta">
	<time<?php if ( $is_card ) : ?> class="post-card-date"<?php elseif ( $is_hero ) : ?> class="entry-hero-date"<?php endif; ?>
		datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"<?php if ( $date_tooltip ) : ?> tabindex="0" data-glaze-tooltip-single-line data-glaze-tooltip="<?php echo esc_attr( $date_tooltip ); ?>"<?php endif; ?>>
		<?php if ( $is_card || $is_hero ) : ?><?php echo westnote_glaze_icon_markup( 'now' ); ?><?php endif; ?>
		<?php echo esc_html( $date ); ?>
	</time>
	<?php if ( ! $is_hero && is_singular( 'post' ) ) : ?>
		<span><?php the_author_posts_link(); ?></span>
	<?php endif; ?>
	<?php if ( ! $is_card && ! $is_hero ) : ?>
		<?php get_template_part( 'template-parts/post-categories' ); ?>
	<?php endif; ?>
	<?php if ( $is_card ) : ?>
		<?php
		$views = westnote_glaze_archive_post_views( get_the_ID() );
		$formatted_views = number_format_i18n( $views );
		/* translators: %s: localized number of post views. */
		$views_label = sprintf( _n( '%s view', '%s views', $views, 'glaze' ), $formatted_views );
		?>
		<span class="post-card-views" data-glaze-post-views="<?php echo esc_attr( get_the_ID() ); ?>" data-glaze-view-count="<?php echo esc_attr( $views ); ?>">
			<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M2.5 12S6 6 12 6s9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="2.4"/></svg>
			<span aria-hidden="true"><?php echo esc_html( $formatted_views ); ?></span>
			<span class="screen-reader-text"><?php echo esc_html( $views_label ); ?></span>
		</span>
	<?php endif; ?>
</div>
