<?php
$archive_posts = westnote_glaze_archive_posts();
$categories    = array();
$days          = array();
$words         = 0;
$views         = 0;
foreach ( $archive_posts as $archive_post ) {
	$days[ substr( $archive_post->post_date, 0, 10 ) ] = true;
	$views += westnote_glaze_archive_post_views( $archive_post->ID );
	foreach ( get_the_category( $archive_post->ID ) as $category ) {
		$categories[ $category->term_id ] = true;
	}

	$text   = strip_shortcodes( $archive_post->post_content );
	$text   = preg_replace( '~<(?:/(?:p|div|li|h[1-6]|pre|tr)|br)\b[^>]*>~i', ' ', $text );
	$text   = html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$text   = preg_replace( '/\p{Han}/u', ' ', $text, -1, $characters );
	$words += $characters + ( $text ? preg_match_all( '/[\p{L}\p{N}]+/u', $text ) : 0 );
}

$post_count     = count( $archive_posts );
$category_count = count( $categories );
$day_count      = count( $days );
$stats          = array(
	array( $post_count, _n( '%s post', '%s posts', $post_count, 'glaze' ) ),
	array( $category_count, _n( '%s category', '%s categories', $category_count, 'glaze' ) ),
	array( $words, _n( '%s word', '%s words', $words, 'glaze' ) ),
	array( $views, _nx( '%s view', '%s views', $views, 'archive total', 'glaze' ) ),
	array( $day_count, _n( '%s publishing day', '%s publishing days', $day_count, 'glaze' ) ),
);
?>
<ul class="archive-stats" role="list">
	<?php foreach ( $stats as list( $value, $label ) ) : ?>
		<li><?php printf( esc_html( $label ), '<strong>' . esc_html( number_format_i18n( $value ) ) . '</strong>' ); ?></li>
	<?php endforeach; ?>
</ul>
