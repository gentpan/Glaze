<?php
if ( post_password_required() ) {
	return;
}

$outcomes = array(
	'approved' => __( 'Comment sent.', 'glaze' ),
	'pending'  => __( 'Comment sent and awaiting moderation.', 'glaze' ),
	'failed'   => '',
);
$confirmation = westnote_glaze_comment_confirmation();
if ( isset( $outcomes[ $confirmation ] ) ) {
	get_template_part(
		'template-parts/comment-confirmation',
		null,
		array(
			'state'   => $confirmation,
			'message' => $outcomes[ $confirmation ],
		)
	);
}
?>
<section id="comments" class="comments-area glaze-panel" aria-labelledby="comments-title"
	data-comment-unconfirmed="<?php esc_attr_e( 'Could not confirm whether your comment was sent. Your text is still in the form.', 'glaze' ); ?>"
	data-comment-list-unavailable="<?php esc_attr_e( 'Your comment was received, but the comment list could not be updated.', 'glaze' ); ?>"
	data-comment-rejected="<?php esc_attr_e( 'The server rejected the comment (HTTP %s).', 'glaze' ); ?>">
	<?php get_template_part( 'template-parts/comment-list' ); ?>
	<p class="glaze-comment-error" data-glaze-comment-page-error role="alert" hidden><?php esc_html_e( 'This page of comments could not be loaded.', 'glaze' ); ?></p>

	<?php if ( comments_open() ) : ?>
		<?php
		if ( is_user_logged_in() ) {
			$comment_user = wp_get_current_user();
			$identity = '<div class="logged-in-as glaze-comment-identity">' . get_avatar( $comment_user->ID, 28, '', '' )
				. '<span>' . esc_html__( 'Commenting as', 'glaze' ) . ' <strong>' . esc_html( $comment_user->display_name ) . '</strong></span>'
				. '<a href="' . esc_url( wp_logout_url( get_permalink() ) ) . '">' . esc_html__( 'Log out', 'glaze' ) . '</a></div>';
		}
		$commenter     = wp_get_current_commenter();
		$name_required = (bool) get_option( 'require_name_email' );
		$field_details = array(
			'author' => array( __( 'Nickname' ), 'text', 'name', 245, 'user' ),
			'email'  => array( __( 'Email' ), 'email', 'email', 100, 'envelope' ),
			'url'    => array( __( 'Blog link (optional)', 'glaze' ), 'url', 'url', 200, 'link' ),
		);
		$fields = array();
		foreach ( $field_details as $name => list( $label, $type, $autocomplete, $maxlength, $icon ) ) {
			$required = $name_required && 'url' !== $name;
			$fields[ $name ] = sprintf(
				'<p class="comment-form-%1$s glaze-comment-field"><label class="screen-reader-text" for="%1$s">%2$s</label><input id="%1$s" name="%1$s" type="%3$s" autocomplete="%4$s" maxlength="%5$d" value="%6$s" placeholder="%7$s"%8$s>%9$s</p>',
				$name,
				esc_html( $label ),
				$type,
				$autocomplete,
				$maxlength,
				esc_attr( $commenter[ 'comment_author' . ( 'author' === $name ? '' : '_' . $name ) ] ),
				esc_attr( $label . ( $required ? ' *' : '' ) ),
				$required ? ' required' : '',
				westnote_glaze_font_awesome_icon( $icon, 'glaze-comment-field-icon' )
			);
		}
		if ( has_action( 'set_comment_cookies', 'wp_set_comment_cookies' ) ) {
			$fields['cookies'] = '<input type="hidden" name="wp-comment-cookies-consent" value="yes">';
		}
		ob_start();
		?>
		<div class="comment-form-comment squircle">
			<label class="screen-reader-text" for="comment"><?php echo esc_html( _x( 'Comment', 'noun' ) ); ?></label>
			<textarea id="comment" name="comment" rows="7" maxlength="65525" required placeholder="<?php esc_attr_e( 'This floor is yours. Write what you would like to say.', 'glaze' ); ?>"></textarea>
			<?php get_template_part( 'template-parts/comment-emoji' ); ?>
		</div>
		<?php
		$comment_field = ob_get_clean();
		$cancel_icon  = westnote_glaze_font_awesome_icon( 'xmark' );
		$comment_icon = westnote_glaze_font_awesome_icon( 'comment' );
		$comment_guidance = esc_html__( 'Please keep the conversation respectful and free of advertising or spam.', 'glaze' );
		$profile_fields_first = static fn( $form_fields ) => array_intersect_key( $form_fields, $field_details ) + $form_fields;
		add_filter( 'comment_form_fields', $profile_fields_first, 20 );
		comment_form(
			array(
				'fields'         => $fields,
				'comment_field'  => $comment_field,
				'comment_notes_before' => '',
				'logged_in_as'   => $identity ?? '',
				'title_reply'    => __( 'Leave a comment', 'glaze' ),
				'title_reply_before' => '<div class="glaze-comment-heading"><h3 id="reply-title" class="comment-reply-title">',
				'title_reply_after' => '<span class="glaze-comment-title-icon">' . $comment_icon . '</span></h3><p class="glaze-comment-guidance">' . $comment_guidance . '</p></div>',
				'cancel_reply_before' => '<img class="glaze-reply-avatar" width="24" height="24" alt="" hidden><small>',
				'cancel_reply_link' => $cancel_icon . esc_html__( 'Cancel reply' ),
				'class_submit'  => 'submit glaze-comment-submit',
				'submit_button' => '<button name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s"><span class="glaze-comment-submit-label">' . $comment_icon . '%4$s</span><span class="glaze-comment-submit-spinner" aria-hidden="true"></span></button>',
				'submit_field'  => '<p class="form-submit">%1$s %2$s<input type="hidden" name="glaze_comment_form" value="1"></p><p class="glaze-comment-error" role="alert" hidden></p>',
			)
		);
		remove_filter( 'comment_form_fields', $profile_fields_first, 20 );
		?>
		<?php foreach ( $outcomes as $state => $message ) : ?>
			<template data-glaze-comment-outcome="<?php echo esc_attr( $state ); ?>">
				<?php get_template_part( 'template-parts/comment-confirmation', null, array( 'state' => $state, 'message' => $message ) ); ?>
			</template>
		<?php endforeach; ?>
	<?php elseif ( get_comments_number() ) : ?>
		<p class="comments-closed"><?php esc_html_e( 'Comments are closed.', 'glaze' ); ?></p>
	<?php endif; ?>
</section>
