<?php
$items = westnote_glaze_subscription_updates();

if ( ! $items ) {
	return;
}

$page_size = 20;
$last_page = (int) ceil( count( $items ) / $page_size );
$raw_page  = $_GET['feed_page'] ?? '';
$feed_page = min( $last_page, max( 1, is_string( $raw_page ) ? absint( $raw_page ) : 1 ) );
?>
<div class="subscription-list">
	<?php foreach ( array_slice( $items, ( $feed_page - 1 ) * $page_size, $page_size, true ) as $url => $item ) : ?>
		<?php $title_id = wp_unique_id( 'subscription-title-' ); ?>
		<article class="subscription-item">
			<a class="subscription-link squircle" href="<?php echo esc_url( $url ); ?>" rel="external" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
				<?php get_template_part( 'template-parts/collections/site-icon', null, array( 'url' => $item['icon'], 'fallback' => 'feeds' ) ); ?>
				<div class="subscription-copy">
					<div class="subscription-meta">
						<span><?php echo esc_html( $item['source'] ); ?></span>
						<?php if ( $item['published'] ) : ?>
							<time datetime="<?php echo esc_attr( wp_date( DATE_W3C, $item['published'] ) ); ?>"><?php echo esc_html( wp_date( get_option( 'date_format' ), $item['published'] ) ); ?></time>
						<?php endif; ?>
					</div>
					<h2 id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html( $item['title'] ?: __( 'Untitled', 'glaze' ) ); ?></h2>
					<?php if ( $item['summary'] ) : ?>
						<p><?php echo esc_html( $item['summary'] ); ?></p>
					<?php endif; ?>
				</div>
				<span class="collection-external-arrow" aria-hidden="true">↗</span>
			</a>
		</article>
	<?php endforeach; ?>
</div>
<?php
$pagination = paginate_links(
	westnote_glaze_pagination_args() + array(
		'base'    => str_replace( '999999999', '%#%', add_query_arg( 'feed_page', 999999999, get_permalink() ) ),
		'format'  => '',
		'current' => $feed_page,
		'total'   => $last_page,
	)
);
if ( $pagination ) :
	?>
	<nav class="navigation pagination collection-pagination" aria-label="<?php esc_attr_e( 'Feed pagination', 'glaze' ); ?>">
		<div class="nav-links"><?php echo wp_kses_post( $pagination ); ?></div>
	</nav>
	<?php
endif;
