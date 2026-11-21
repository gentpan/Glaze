<?php
$days  = $args['days'];
$today = $args['today']->format( 'Y-m-d' );
$first = $args['first'];
$last  = $args['last'];
$start = $first->modify( 'monday this week' );
$end   = $last->modify( 'monday next week' );
?>
<div class="archive-calendar-scroll" role="region" aria-labelledby="archive-activity-title" tabindex="0">
	<div class="archive-calendar">
		<div class="archive-calendar-weekdays" aria-hidden="true">
			<span>Mon</span>
			<span>Wed</span>
			<span>Fri</span>
			<span>Sun</span>
		</div>
		<div class="archive-calendar-weeks">
			<?php for ( $week = $start; $week < $end; $week = $week->modify( '+1 week' ) ) : ?>
				<?php
				$sunday = $week->modify( '+6 days' );
				$month  = $sunday->modify( 'first day of this month' );
				?>
				<div class="archive-calendar-week">
					<div class="archive-calendar-month" aria-hidden="true">
						<?php if ( $month >= $week && $month >= $first && $month <= $last ) : ?>
							<span><?php echo esc_html( $month->format( 'M' ) ); ?></span>
						<?php endif; ?>
					</div>
					<?php for ( $day = $week; $day <= $sunday; $day = $day->modify( '+1 day' ) ) : ?>
						<?php
						$key   = $day->format( 'Y-m-d' );
						$count = $days[ $key ] ?? 0;
						if ( $count ) :
							$label = sprintf(
								/* translators: 1: publication date, 2: localized number of posts. */
								_n( '%1$s · %2$s post', '%1$s · %2$s posts', $count, 'glaze' ),
								wp_date( get_option( 'date_format' ), $day->getTimestamp() ),
								number_format_i18n( $count )
							);
							?>
							<a class="archive-calendar-day" data-level="<?php echo esc_attr( min( 4, $count ) ); ?>" href="#archive-day-<?php echo esc_attr( $key ); ?>" title="<?php echo esc_attr( $label ); ?>" aria-label="<?php echo esc_attr( $label ); ?>"></a>
						<?php else : ?>
							<span class="archive-calendar-day" aria-hidden="true" data-level="<?php echo ( $day < $first || $day > $last ) ? 'outside' : ( $key > $today ? 'future' : '0' ); ?>"></span>
						<?php endif; ?>
					<?php endfor; ?>
				</div>
			<?php endfor; ?>
		</div>
	</div>
</div>
<div class="archive-calendar-legend" aria-label="<?php esc_attr_e( 'Posts per day', 'glaze' ); ?>">
	<span><?php esc_html_e( 'Less', 'glaze' ); ?></span>
	<?php for ( $level = 0; $level <= 4; $level++ ) : ?>
		<span class="archive-calendar-day" data-level="<?php echo esc_attr( $level ); ?>" aria-hidden="true"></span>
	<?php endfor; ?>
	<span><?php esc_html_e( 'More', 'glaze' ); ?></span>
</div>
