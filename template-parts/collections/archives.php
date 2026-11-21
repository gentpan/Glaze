<?php
$today        = current_datetime()->setTime( 0, 0 );
$current_year = (int) $today->format( 'Y' );
$years        = array( $current_year => array() );
$entries      = westnote_glaze_archive_posts();
foreach ( $entries as $entry ) {
	$years[ (int) substr( $entry->post_date, 0, 4 ) ][] = $entry;
}
krsort( $years, SORT_NUMERIC );

$requested    = $_GET['archive_year'] ?? '';
$year         = is_string( $requested ) && isset( $years[ $requested ] ) ? (int) $requested : '';
$first        = $year ? $today->setDate( $year, 1, 1 ) : $today->modify( '-364 days' );
$last         = $year ? $today->setDate( $year, 12, 31 ) : $today;
$first_day    = $first->format( 'Y-m-d' );
$last_day     = $last->format( 'Y-m-d' );
$recent_label = __( 'Last 365 days', 'glaze' );
$url          = get_permalink( get_queried_object_id() );
if ( empty( $args['fragment'] ) ) {
	$script = 'assets/js/archive.min.js';
	$path   = get_parent_theme_file_path( $script );
	if ( is_file( $path ) ) {
		wp_enqueue_script( 'westnote-glaze-archive', get_parent_theme_file_uri( $script ), array(), (string) filemtime( $path ), true );
	}
}
$days   = array();
$months = array();
$total  = 0;
foreach ( $year ? $years[ $year ] : $entries as $entry ) {
	$day = substr( $entry->post_date, 0, 10 );
	if ( $day < $first_day || $day > $last_day ) {
		continue;
	}
	$days[ $day ] = ( $days[ $day ] ?? 0 ) + 1;
	$months[ substr( $day, 0, 7 ) ][] = $entry;
	++$total;
}
?>
<div class="archive-view" data-glaze-archive data-archive-year="<?php echo esc_attr( $year ); ?>" data-archive-error="<?php esc_attr_e( 'Could not load this period. Try again.', 'glaze' ); ?>">
	<section class="archive-activity" aria-labelledby="archive-activity-title">
		<div class="archive-activity-heading">
			<h2 id="archive-activity-title"><?php esc_html_e( 'Publishing activity', 'glaze' ); ?></h2>
			<details class="archive-years">
				<summary><span class="screen-reader-text"><?php esc_html_e( 'Archive period', 'glaze' ); ?> </span><?php echo esc_html( $year ?: $recent_label ); ?></summary>
				<nav aria-label="<?php esc_attr_e( 'Archive period', 'glaze' ); ?>">
					<a href="<?php echo esc_url( $url ); ?>" data-archive-year-link=""<?php if ( ! $year ) : ?> aria-current="true"<?php endif; ?>><?php echo esc_html( $recent_label ); ?></a>
					<?php foreach ( array_keys( $years ) as $option ) : ?>
						<a href="<?php echo esc_url( add_query_arg( 'archive_year', $option, $url ) ); ?>" data-archive-year-link="<?php echo esc_attr( $option ); ?>"<?php if ( $option === $year ) : ?> aria-current="true"<?php endif; ?>><?php echo esc_html( $option ); ?></a>
					<?php endforeach; ?>
				</nav>
			</details>
			<span class="archive-year-total"><?php
				/* translators: %s: localized number of posts. */
				echo esc_html( sprintf( _n( '%s post', '%s posts', $total, 'glaze' ), number_format_i18n( $total ) ) );
			?></span>
		</div>
		<div class="archive-calendar-frame" data-archive-calendar-frame>
			<?php get_template_part( 'template-parts/collections/archive-calendar', null, compact( 'first', 'last', 'days', 'today' ) ); ?>
			<span class="archive-calendar-loading" role="status">
				<span aria-hidden="true"></span><?php esc_html_e( 'Loading…', 'glaze' ); ?>
			</span>
		</div>
	</section>

	<section class="archive-index" aria-labelledby="archive-index-title">
		<div class="archive-section-heading">
			<h2 id="archive-index-title"><?php
				/* translators: %s: year, such as 2026. */
				echo esc_html( $year ? sprintf( __( 'Posts in %s', 'glaze' ), $year ) : __( 'Recent posts', 'glaze' ) );
			?></h2>
		</div>
		<?php if ( ! $months ) : ?>
			<p class="archive-empty"><?php esc_html_e( 'No posts were published in this period.', 'glaze' ); ?></p>
		<?php endif; ?>
		<?php foreach ( $months as $month_entries ) : ?>
			<?php $month_total = count( $month_entries ); ?>
			<div class="archive-month">
				<div class="archive-section-heading">
					<h3><?php echo esc_html( get_the_date( _x( 'F', 'archive month heading date format', 'glaze' ), $month_entries[0] ) ); ?></h3>
					<span><?php echo esc_html( sprintf( _n( '%s post', '%s posts', $month_total, 'glaze' ), number_format_i18n( $month_total ) ) ); ?></span>
				</div>
				<ul class="archive-posts" role="list">
					<?php
					$previous_day = '';
					foreach ( $month_entries as $entry ) :
						$date       = get_the_date( 'Y-m-d', $entry );
						$categories = get_the_category( $entry->ID );
						$icon       = $categories ? westnote_glaze_icon_markup( get_term_meta( $categories[0]->term_id, '_glaze_category_icon', true ) ) : '';
						$comments   = (int) get_comments_number( $entry );
						$views      = westnote_glaze_archive_post_views( $entry->ID );
						?>
						<li<?php if ( $previous_day !== $date ) : ?> id="archive-day-<?php echo esc_attr( $date ); ?>" tabindex="-1"<?php endif; ?>>
							<a class="archive-post squircle" href="<?php echo esc_url( get_permalink( $entry ) ); ?>">
								<time datetime="<?php echo esc_attr( $date ); ?>" title="<?php echo esc_attr( get_the_date( '', $entry ) ); ?>"><?php echo esc_html( get_the_date( 'm/d', $entry ) ); ?></time>
								<span class="archive-post-title">
									<?php echo $icon ?: westnote_glaze_icon_markup( 'reading' ); ?>
									<span><?php echo esc_html( get_the_title( $entry ) ?: __( 'Untitled', 'glaze' ) ); ?></span>
								</span>
								<span class="archive-post-metrics">
									<span>
										<?php echo westnote_glaze_icon_markup( 'talks' ); ?>
										<span aria-hidden="true"><?php echo esc_html( number_format_i18n( $comments ) ); ?></span>
										<span class="screen-reader-text"><?php echo esc_html( sprintf( _n( '%s comment', '%s comments', $comments, 'glaze' ), number_format_i18n( $comments ) ) ); ?></span>
									</span>
									<span>
										<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M2.5 12S6 6 12 6s9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="2.4"/></svg>
										<span aria-hidden="true"><?php echo esc_html( number_format_i18n( $views ) ); ?></span>
										<span class="screen-reader-text"><?php echo esc_html( sprintf( _n( '%s view', '%s views', $views, 'glaze' ), number_format_i18n( $views ) ) ); ?></span>
									</span>
								</span>
							</a>
						</li>
						<?php $previous_day = $date; ?>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>
	</section>
</div>
<?php if ( ! empty( $args['fragment'] ) ) { return; } ?>

<div class="archive-taxonomies">
	<?php
	$taxonomies = array(
		'category' => array( __( 'Categories', 'glaze' ), 'categories' ),
		'post_tag' => array( __( 'Tags', 'glaze' ), 'tags' ),
	);
	foreach ( $taxonomies as $taxonomy => list( $label, $icon ) ) :
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'orderby' => 'count', 'order' => 'DESC' ) );
		if ( ! $terms || is_wp_error( $terms ) ) {
			continue;
		}
		?>
		<details class="archive-taxonomy squircle">
			<summary><?php echo westnote_glaze_icon_markup( $icon ); ?><?php echo esc_html( $label ); ?></summary>
			<ul role="list">
				<?php foreach ( $terms as $term ) : ?>
					<?php $term_url = get_term_link( $term ); ?>
					<?php if ( ! is_wp_error( $term_url ) ) : ?>
						<li><a class="squircle" href="<?php echo esc_url( $term_url ); ?>"><?php echo esc_html( $term->name ); ?> <sup><?php echo esc_html( number_format_i18n( $term->count ) ); ?></sup></a></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</details>
	<?php endforeach; ?>
</div>
