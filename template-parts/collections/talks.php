<?php
$manage = westnote_glaze_note_tools_available();
if ( $manage ) {
	get_template_part( 'template-parts/collections/talk-compose' );
}

$page  = westnote_glaze_note_page_number();
$url   = $manage ? westnote_glaze_note_page_link( $page ) : '';
$notes = westnote_glaze_notes_query( westnote_glaze_notes_per_page(), $page, true );
if ( ! $notes->have_posts() ) {
	return;
}
$month = '';
?>
<div class="talk-timeline">
	<?php while ( $notes->have_posts() ) : ?>
		<?php
		$notes->the_post();
		$note_month = get_the_date( 'Y-m' );
		$title      = get_the_title();
		$keywords   = get_the_terms( get_the_ID(), 'feng_talk_tag' );
		if ( $note_month !== $month ) :
			$month = $note_month;
			?>
			<h2 class="talk-month"><?php echo esc_html( get_the_date( _x( 'F Y', 'notes month heading date format', 'glaze' ) ) ); ?></h2>
		<?php endif; ?>
		<article id="note-<?php the_ID(); ?>" class="talk-item">
			<a class="talk-date squircle" href="#note-<?php the_ID(); ?>">
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date( _x( 'M j, H:i', 'note date and time format', 'glaze' ) ) ); ?></time>
			</a>
			<div class="talk-card squircle">
				<?php if ( is_array( $keywords ) && $keywords ) : ?>
					<ul class="talk-tags" role="list" aria-label="<?php esc_attr_e( 'Short note keywords', 'glaze' ); ?>">
						<?php foreach ( $keywords as $keyword ) : ?>
							<li><?php echo esc_html( '#' . ltrim( $keyword->name, '# ' ) ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( $title && trim( wp_strip_all_tags( $post->post_content ) ) !== $title ) : ?>
					<h3><?php echo esc_html( $title ); ?></h3>
				<?php endif; ?>
				<?php the_content(); ?>
				<?php $images = westnote_glaze_note_image_ids( $post ); ?>
				<?php if ( $images ) : ?>
					<div class="talk-images">
						<?php foreach ( $images as $image_id ) : ?>
							<?php $image_url = wp_get_attachment_url( $image_id ); ?>
							<?php if ( $image_url ) : ?>
								<a class="squircle" href="<?php echo esc_url( $image_url ); ?>"><?php echo wp_get_attachment_image( $image_id, 'medium_large' ); ?></a>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php
				$link = esc_url_raw( get_post_meta( get_the_ID(), 'p1_note_link_url', true ), array( 'http', 'https' ) );
				if ( $link ) :
					$label = get_post_meta( get_the_ID(), 'p1_note_link_label', true );
					?>
					<a class="talk-external squircle" href="<?php echo esc_url( $link ); ?>" rel="external">
						<?php echo westnote_glaze_icon_markup( 'links' ); ?>
						<span><?php echo esc_html( $label ?: wp_parse_url( $link, PHP_URL_HOST ) ?: $link ); ?></span>
						<span class="collection-external-arrow" aria-hidden="true">↗</span>
					</a>
				<?php endif; ?>
			<?php
			$can_edit   = $manage && westnote_glaze_can_manage_note( $post, 'edit' );
			$can_delete = $manage && EMPTY_TRASH_DAYS && westnote_glaze_can_manage_note( $post, 'delete' );
			if ( $can_edit || $can_delete ) :
				$edit_data = $can_edit ? westnote_glaze_note_editor_data( $post ) : array();
				?>
				<div class="talk-actions">
					<?php if ( $can_edit ) : ?>
						<a class="squircle" href="<?php echo esc_url( add_query_arg( array( 'glaze_note_editor' => 'edit', 'glaze_note_id' => get_the_ID() ), $url ) . '#talk-compose' ); ?>"
							data-glaze-talk-edit="<?php echo esc_attr( wp_json_encode( $edit_data ) ); ?>" aria-haspopup="dialog" aria-controls="talk-compose"><?php esc_html_e( 'Edit', 'glaze' ); ?></a>
					<?php endif; ?>
					<?php if ( $can_delete ) : ?>
						<a class="squircle" href="<?php echo esc_url( add_query_arg( array( 'glaze_note_editor' => 'delete', 'glaze_note_id' => get_the_ID() ), $url ) . '#talk-delete' ); ?>"
							data-glaze-talk-delete="<?php the_ID(); ?>" data-version="<?php echo esc_attr( $edit_data['version'] ?? westnote_glaze_note_version( $post ) ); ?>" aria-haspopup="dialog" aria-controls="talk-delete"><?php esc_html_e( 'Delete', 'glaze' ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			</div>
		</article>
	<?php endwhile; ?>
</div>
<?php
wp_reset_postdata();
$pagination = paginate_links( westnote_glaze_pagination_args() + array( 'current' => $page, 'total' => $notes->max_num_pages ) );
if ( $pagination ) :
	?>
	<nav class="navigation pagination collection-pagination" aria-label="<?php esc_attr_e( 'Notes pagination', 'glaze' ); ?>">
		<div class="nav-links"><?php echo wp_kses_post( $pagination ); ?></div>
	</nav>
	<?php
endif;
