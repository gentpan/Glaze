<?php
$note      = $args['item'];
$author    = get_the_author_meta( 'display_name', $note->post_author );
$thumbnail = esc_url( westnote_glaze_note_thumbnail( $note ), array( 'http', 'https' ) );
$content   = strip_shortcodes( $note->post_content );
$content   = preg_replace( '~</(?:p|div|li)>|<br\b[^>]*>~i', ' ', $content );
$summary   = trim( html_entity_decode( wp_strip_all_tags( $content, true ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
$summary   = trim( preg_replace( '/\s+/u', ' ', $summary ) ?? $summary );
if ( '' === $summary ) {
	$summary = get_the_title( $note ) ?: ( $thumbnail ? __( 'Photo note', 'glaze' ) : __( 'Short notes', 'glaze' ) );
}

$url = $args['collection_url'];
if ( $url ) {
	$page = intdiv( $args['index'], westnote_glaze_notes_per_page() ) + 1;
	$url  = ( $page > 1 ? add_query_arg( 'paged', $page, $url ) : $url ) . '#note-' . $note->ID;
} elseif ( is_post_type_viewable( $note->post_type ) ) {
	$url = get_permalink( $note );
}
$tag = $url ? 'a' : 'div';
?>
<<?php echo $tag; ?> class="home-rail-card home-rail-card--note glaze-panel"<?php if ( $url ) : ?> href="<?php echo esc_url( $url ); ?>"<?php endif; ?>>
	<span class="home-rail-meta">
		<span class="home-rail-avatar" aria-hidden="true"><?php echo westnote_glaze_icon_markup( 'talks' ); ?></span>
		<span class="home-rail-author"><?php echo esc_html( $author ); ?></span>
		<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $note ) ); ?>"><?php echo esc_html( get_the_date( 'n/j', $note ) ); ?></time>
	</span>
	<span class="home-rail-note-body">
		<span class="home-rail-note-text"><?php echo esc_html( $summary ); ?></span>
		<?php if ( $thumbnail ) : ?>
			<img class="home-rail-note-image squircle" width="44" height="44" src="<?php echo $thumbnail; ?>" alt="" loading="lazy" decoding="async">
		<?php endif; ?>
	</span>
</<?php echo $tag; ?>>
