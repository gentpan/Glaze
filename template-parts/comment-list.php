<?php
$glaze_post_id = get_queried_object_id();
$glaze_walker  = new WestNote_Glaze_Comment_Walker( $glaze_post_id );
$glaze_floors  = $glaze_walker->get_floor_count();
$glaze_total   = (int) get_comments( array( 'post_id' => $glaze_post_id, 'status' => 'approve', 'type' => 'comment', 'count' => true ) );
?>
<div data-glaze-comment-list>
	<header class="comments-summary">
		<h2 id="comments-title" class="comments-title">
			<?php echo westnote_glaze_font_awesome_icon( 'comments', 'comments-title-icon' ); ?>
			<span>
				<?php
				/* translators: 1: Number of comments including replies, 2: Number of top-level threads. */
				echo esc_html( sprintf( __( 'Thanks for joining in! %1$s comments across %2$s threads.', 'glaze' ), number_format_i18n( $glaze_total ), number_format_i18n( $glaze_floors ) ) );
				?>
			</span>
		</h2>
		<?php if ( comments_open() ) : ?>
			<a class="comments-write-link" href="<?php echo esc_attr( ! is_user_logged_in() && get_option( 'comment_registration' ) ? '#respond' : '#comment' ); ?>"><span aria-hidden="true">»»</span> <?php esc_html_e( 'Join in?', 'glaze' ); ?></a>
		<?php endif; ?>
	</header>

	<?php if ( have_comments() ) : ?>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'format'      => 'html5',
					'short_ping'  => true,
					'avatar_size' => 32,
					'walker'      => $glaze_walker,
				)
			);
			?>
		</ol>
		<?php
		$strip_receipt = static function ( $link ) {
			return remove_query_arg( 'glaze_sent', $link );
		};
		add_filter( 'paginate_links', $strip_receipt );
		the_comments_pagination( westnote_glaze_pagination_args() );
		remove_filter( 'paginate_links', $strip_receipt );
		?>
	<?php endif; ?>
</div>
