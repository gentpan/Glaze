<?php
defined( 'ABSPATH' ) || exit;

function westnote_glaze_render_comment_images( $text ) {
	return preg_replace_callback(
		'~\[img\]\s*(https?://[^\s<\[\]]+)\s*\[/img\]~i',
		static function ( $match ) {
			$url = esc_url( $match[1], array( 'http', 'https' ) );
			if ( ! $url ) {
				return $match[0];
			}
			return '<img class="comment-image" src="' . $url . '" alt="" loading="lazy" decoding="async" referrerpolicy="no-referrer">';
		},
		$text
	) ?? $text;
}
add_filter( 'comment_text', 'westnote_glaze_render_comment_images', 8 );

function westnote_glaze_macos_version( $version ) {
	return is_string( $version ) && preg_match( '/\A[1-9][0-9]{0,2}(?:\.[0-9]{1,3}){1,2}\z/', $version ) ? $version : '';
}

function westnote_glaze_store_comment_macos_version( $comment_id, $approved, $comment_data ) {
	if ( '1' !== ( $_POST['glaze_comment_form'] ?? '' ) || ! in_array( (string) $approved, array( '0', '1' ), true ) ) {
		return;
	}
	$version = westnote_glaze_macos_version( $_POST['glaze_macos_version'] ?? '' );
	if ( '' === $version ) {
		return;
	}
	$agent = $comment_data['comment_agent'] ?? '';
	if ( 'google-chrome' === westnote_glaze_comment_browser( $agent )['icon'] && 'macOS' === ( westnote_glaze_comment_system( $agent )['label'] ?? '' ) ) {
		add_comment_meta( $comment_id, '_glaze_macos_version', $version, true );
	}
}
add_action( 'comment_post', 'westnote_glaze_store_comment_macos_version', 10, 3 );

function westnote_glaze_comment_redirect( $location, $comment ) {
	if ( '1' !== ( $_POST['glaze_comment_form'] ?? '' ) ) {
		return $location;
	}

	$send_json = '1' === ( $_SERVER['HTTP_X_GLAZE_COMMENT'] ?? '' ) && wp_is_json_request();
	if ( ! in_array( (string) $comment->comment_approved, array( '0', '1' ), true ) ) {
		if ( $send_json ) {
			wp_send_json_error( array( 'message' => __( 'Comment could not be sent.', 'glaze' ) ), 400 );
		}
		return $location;
	}

	$id       = (int) $comment->comment_ID;
	$receipt  = $id . '.' . wp_create_nonce( 'glaze_comment_sent_' . $id );
	$location = add_query_arg( 'glaze_sent', $receipt, $location );
	if ( $send_json ) {
		$redirect = wp_validate_redirect( $location );
		if ( ! $redirect ) {
			$redirect = add_query_arg( 'glaze_sent', $receipt, get_comment_link( $comment ) );
		}
		wp_send_json_success(
			array(
				'location' => $redirect,
				'state'    => '1' === (string) $comment->comment_approved ? 'approved' : 'pending',
			)
		);
	}
	return $location;
}
add_filter( 'comment_post_redirect', 'westnote_glaze_comment_redirect', PHP_INT_MAX, 2 );

function westnote_glaze_comment_confirmation() {
	$receipt = $_GET['glaze_sent'] ?? '';
	if ( ! is_string( $receipt ) || ! preg_match( '/\A([1-9][0-9]{0,18})\.([a-zA-Z0-9]{10})\z/', $receipt, $parts ) ) {
		return '';
	}

	$id = (int) $parts[1];
	if ( ! wp_verify_nonce( $parts[2], 'glaze_comment_sent_' . $id ) ) {
		return '';
	}

	$comment = get_comment( $id );
	if ( ! $comment || (int) $comment->comment_post_ID !== (int) get_queried_object_id() ) {
		return '';
	}
	if ( '1' === (string) $comment->comment_approved ) {
		return 'approved';
	}
	return '0' === (string) $comment->comment_approved ? 'pending' : '';
}

function westnote_glaze_send_comment_list() {
	if ( '1' !== ( $_SERVER['HTTP_X_GLAZE_COMMENTS'] ?? '' ) || 'GET' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		return;
	}

	nocache_headers();
	$post = get_queried_object();
	if ( ! is_singular() || ! $post instanceof WP_Post || post_password_required( $post ) || ( ! is_post_publicly_viewable( $post ) && ! current_user_can( 'read_post', $post->ID ) ) ) {
		wp_send_json_error( null, 403 );
	}
	if ( isset( $_GET['glaze_sent'] ) && ! westnote_glaze_comment_confirmation() ) {
		wp_send_json_error( null, 403 );
	}

	ob_start();
	comments_template( '/template-parts/comment-list.php' );
	wp_send_json_success( array( 'html' => ob_get_clean() ) );
}
add_action( 'template_redirect', 'westnote_glaze_send_comment_list', 9 );

function westnote_glaze_recent_home_comments( $limit ) {
	$admins = get_users( array( 'role' => 'administrator', 'fields' => array( 'ID', 'user_email' ) ) );
	$admin_comments = array();
	foreach ( $admins as $admin ) {
		if ( '' !== $admin->user_email ) {
			$admin_comments = array_merge(
				$admin_comments,
				get_comments( array( 'author_email' => $admin->user_email, 'fields' => 'ids', 'status' => 'approve' ) )
			);
		}
	}

	$post_types = array( 'post', 'page' );
	$protected_posts = get_posts(
		array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'has_password'   => true,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	return get_comments(
		array(
			'status'                    => 'approve',
			'type'                      => 'comment',
			'author__not_in'            => wp_list_pluck( $admins, 'ID' ),
			'comment__not_in'           => $admin_comments,
			'post_status'               => 'publish',
			'post_type'                 => $post_types,
			'post__not_in'              => $protected_posts,
			'number'                    => $limit,
			'orderby'                   => 'comment_date_gmt',
			'order'                     => 'DESC',
			'update_comment_post_cache' => true,
		)
	);
}
