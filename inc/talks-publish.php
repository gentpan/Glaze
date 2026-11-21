<?php
defined( 'ABSPATH' ) || exit;

function westnote_glaze_can_publish_note() {
	$type = get_post_type_object( 'talk' );
	return $type && current_user_can( $type->cap->create_posts ) && current_user_can( $type->cap->publish_posts );
}

function westnote_glaze_note_upload_limit() {
	return min( wp_max_upload_size(), 5 * MB_IN_BYTES );
}

function westnote_glaze_note_uploads( $input ) {
	$uploads = array();
	$mimes   = array( 'image/jpeg', 'image/png', 'image/webp', 'image/gif' );
	foreach ( (array) ( $input['name'] ?? array() ) as $index => $name ) {
		$error = $input['error'][ $index ] ?? UPLOAD_ERR_NO_FILE;
		if ( UPLOAD_ERR_NO_FILE === $error ) {
			continue;
		}
		if ( ! current_user_can( 'upload_files' ) || count( $uploads ) >= 4 ) {
			return new WP_Error( 'glaze_note_images', __( 'You can attach up to four images with upload permission.', 'glaze' ) );
		}
		if ( UPLOAD_ERR_OK !== $error || ( $input['size'][ $index ] ?? 0 ) > westnote_glaze_note_upload_limit() ) {
			return new WP_Error( 'glaze_note_upload', __( 'An image could not be uploaded. Check its size and try again.', 'glaze' ) );
		}
		$file = $input['tmp_name'][ $index ] ?? '';
		$mime = is_string( $file ) && is_uploaded_file( $file ) ? wp_get_image_mime( $file ) : false;
		if ( ! is_string( $name ) || ! in_array( $mime, $mimes, true ) ) {
			return new WP_Error( 'glaze_note_format', __( 'Choose JPEG, PNG, WebP, or GIF images.', 'glaze' ) );
		}
		$uploads[] = array(
			'name'     => $name,
			'tmp_name' => $file,
			'type'     => $mime,
			'size'     => (int) $input['size'][ $index ],
			'error'    => UPLOAD_ERR_OK,
		);
	}
	return $uploads;
}

function westnote_glaze_can_manage_note( $note, $action ) {
	return $note instanceof WP_Post &&
		in_array( $note->post_type, westnote_glaze_note_post_types(), true ) &&
		'publish' === $note->post_status && '' === $note->post_password &&
		current_user_can( $action . '_post', $note->ID );
}

function westnote_glaze_note_terms( $note ) {
	$terms = get_the_terms( $note, 'feng_talk_tag' );
	return is_array( $terms ) ? $terms : array();
}

function westnote_glaze_note_has_markup( $note ) {
	return $note->post_content !== wp_strip_all_tags( $note->post_content );
}

function westnote_glaze_note_editable_content( $note ) {
	return westnote_glaze_note_has_markup( $note ) ? $note->post_content : html_entity_decode( $note->post_content, ENT_QUOTES, 'UTF-8' );
}

function westnote_glaze_note_version( $note ) {
	$term_ids = wp_list_pluck( westnote_glaze_note_terms( $note ), 'term_id' );
	sort( $term_ids );
	return hash( 'sha256', serialize( array(
		$note->post_modified_gmt,
		$note->post_title,
		$note->post_content,
		get_post_meta( $note->ID, '_feng_talk_images', true ),
		$term_ids,
	) ) );
}

function westnote_glaze_note_editor_data( $note ) {
	$images = array();
	foreach ( westnote_glaze_note_image_ids( $note ) as $id ) {
		$url = wp_get_attachment_image_url( $id, 'thumbnail' );
		if ( $url ) {
			$images[] = array(
				'id'  => $id,
				'url' => $url,
				'alt' => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
			);
		}
	}
	return array(
		'id'       => $note->ID,
		'version'  => westnote_glaze_note_version( $note ),
		'content'  => westnote_glaze_note_editable_content( $note ),
		'keywords' => implode( ', ', wp_list_pluck( westnote_glaze_note_terms( $note ), 'name' ) ),
		'images'   => $images,
		'markup'   => westnote_glaze_note_has_markup( $note ),
	);
}

function westnote_glaze_note_tools_available() {
	return is_user_logged_in() && ( current_user_can( 'edit_posts' ) || current_user_can( 'delete_posts' ) || westnote_glaze_can_publish_note() );
}

function westnote_glaze_note_page_number() {
	return max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
}

function westnote_glaze_note_page_link( $page_number ) {
	return remove_query_arg( array( 'glaze_note_submit', 'glaze_note_editor', 'glaze_note_id', 'glaze_note_deleted' ), get_pagenum_link( $page_number, false ) );
}

function westnote_glaze_enqueue_note_manager() {
	if ( ! is_page() || ! westnote_glaze_note_tools_available() ) {
		return;
	}
	$page = get_queried_object();
	if ( post_password_required( $page ) || 'talks' !== westnote_glaze_collection_type( $page ) ) {
		return;
	}
	$file = 'assets/js/talk-manager.min.js';
	$path = get_parent_theme_file_path( $file );
	if ( is_file( $path ) ) {
		wp_enqueue_script( 'westnote-glaze-talk-manager', get_parent_theme_file_uri( $file ), array(), (string) filemtime( $path ), true );
	}
}
add_action( 'wp_enqueue_scripts', 'westnote_glaze_enqueue_note_manager' );

function westnote_glaze_note_keywords( $input ) {
	$tags = array();
	foreach ( preg_split( '/[,，\n]+/u', $input ) ?: array() as $tag ) {
		$tag = sanitize_text_field( ltrim( trim( $tag ), '# ' ) );
		if ( '' !== $tag ) {
			$tags[] = $tag;
		}
	}
	$tags = array_values( array_unique( $tags ) );
	if ( count( $tags ) > 4 || array_filter( $tags, static function ( $tag ) { return mb_strlen( $tag ) > 20; } ) ) {
		return new WP_Error( 'glaze_note_length', __( 'Use up to 4,000 characters and four keywords of up to 20 characters each.', 'glaze' ) );
	}
	$taxonomy = get_taxonomy( 'feng_talk_tag' );
	if ( ! $taxonomy || ! current_user_can( $taxonomy->cap->assign_terms ) ) {
		return new WP_Error( 'glaze_note_keywords', __( 'You do not have permission to assign keywords.', 'glaze' ) );
	}
	return $tags;
}

function westnote_glaze_note_with_lock( $key, $callback ) {
	global $wpdb;
	$lock = 'glaze_note_' . substr( $key, 0, 48 );
	if ( 1 !== (int) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 0)', $lock ) ) ) {
		return new WP_Error( 'glaze_note_busy', __( 'This short note is still being saved. Try again in a moment.', 'glaze' ) );
	}
	try {
		return $callback();
	} finally {
		$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) );
	}
}

function westnote_glaze_delete_note_uploads( $images ) {
	foreach ( $images as $image ) {
		wp_delete_attachment( $image, true );
	}
}

function westnote_glaze_attach_note_images( $id, $uploads, $author ) {
	if ( ! $uploads ) {
		return array();
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$images = array();
	foreach ( $uploads as $upload ) {
		$file = wp_handle_upload( $upload, array( 'test_form' => false ) );
		if ( isset( $file['error'] ) ) {
			westnote_glaze_delete_note_uploads( $images );
			return new WP_Error( 'glaze_note_upload', __( 'An image could not be uploaded. Check its format and try again.', 'glaze' ) );
		}
		$image = wp_insert_attachment( array(
			'post_title'     => pathinfo( wp_basename( $file['file'] ), PATHINFO_FILENAME ),
			'post_mime_type' => $file['type'],
			'post_author'    => $author,
		), $file['file'], $id, true );
		if ( is_wp_error( $image ) || ! $image ) {
			wp_delete_file( $file['file'] );
			westnote_glaze_delete_note_uploads( $images );
			return new WP_Error( 'glaze_note_upload', __( 'An image could not be uploaded. Check its format and try again.', 'glaze' ) );
		}
		$images[] = $image;
		wp_update_attachment_metadata( $image, wp_generate_attachment_metadata( $image, $file['file'] ) );
	}
	return $images;
}

function westnote_glaze_create_note( $fields, $request, $files ) {
	if ( ! westnote_glaze_can_publish_note() ) {
		return new WP_Error( 'glaze_note_permission', __( 'You do not have permission to publish short notes.', 'glaze' ) );
	}

	global $wpdb;
	$author = get_current_user_id();
	$key    = hash( 'sha256', $wpdb->posts . ':' . $author . ':' . $request );
	return westnote_glaze_note_with_lock( $key, static function () use ( $fields, $files, $author, $key ) {
		$existing = get_posts( array(
			'post_type'   => 'talk',
			'post_status' => 'publish',
			'author'      => $author,
			'fields'      => 'ids',
			'numberposts' => 1,
			'meta_key'    => '_glaze_note_request',
			'meta_value'  => $key,
		) );
		if ( $existing ) {
			return (int) $existing[0];
		}

		$tags = '' === ( $fields['keywords'] ?? '' ) ? array() : westnote_glaze_note_keywords( $fields['keywords'] );
		if ( is_wp_error( $tags ) ) {
			return $tags;
		}
		$uploads = westnote_glaze_note_uploads( $files );
		if ( is_wp_error( $uploads ) ) {
			return $uploads;
		}
		if ( '' === $fields['content'] && ! $uploads ) {
			return new WP_Error( 'glaze_note_empty', __( 'Write something or add an image.', 'glaze' ) );
		}
		return westnote_glaze_save_note( $fields['content'], $tags, $uploads, $author, $key );
	} );
}

function westnote_glaze_save_note( $content, $tags, $uploads, $author, $request_key ) {
	$failure = new WP_Error( 'glaze_note_save', __( 'The short note could not be published. Please try again.', 'glaze' ) );
	$id      = wp_insert_post( wp_slash( array(
		'post_type'      => 'talk',
		'post_status'    => 'draft',
		'post_author'    => $author,
		'post_title'     => '' === $content ? __( 'Photo note', 'glaze' ) : '',
		'post_content'   => esc_html( $content ),
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	) ), true );
	if ( is_wp_error( $id ) || ! $id ) {
		return $failure;
	}

	$images    = array();
	$published = false;
	try {
		$uploaded = westnote_glaze_attach_note_images( $id, $uploads, $author );
		if ( is_wp_error( $uploaded ) ) {
			return $uploaded;
		}
		$images = $uploaded;
		if ( ( $images && ! add_post_meta( $id, '_feng_talk_images', $images, true ) ) ||
			( $tags && is_wp_error( wp_set_object_terms( $id, $tags, 'feng_talk_tag' ) ) ) ||
			! add_post_meta( $id, '_glaze_note_request', $request_key, true ) ) {
			return $failure;
		}
		$published = $id === wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ), true ) && 'publish' === get_post_status( $id );
		return $published ? $id : $failure;
	} finally {
		if ( ! $published ) {
			westnote_glaze_delete_note_uploads( $images );
			wp_delete_post( $id, true );
		}
	}
}

function westnote_glaze_update_note( $note, $fields, $uploads, $removed ) {
	$failure    = new WP_Error( 'glaze_note_edit', __( 'The changes could not be saved. Please try again.', 'glaze' ) );
	$id         = $note->ID;
	$old_images = get_post_meta( $id, '_feng_talk_images', true );
	$valid      = westnote_glaze_note_image_ids( $note );
	$removed    = array_intersect( $valid, $removed );
	$kept       = array_diff( $valid, $removed );
	$old_terms  = westnote_glaze_note_terms( $note );
	$old_input  = implode( ', ', wp_list_pluck( $old_terms, 'name' ) );
	$tags       = isset( $fields['keywords'] ) && $fields['keywords'] !== $old_input ? westnote_glaze_note_keywords( $fields['keywords'] ) : null;
	if ( is_wp_error( $tags ) ) {
		return $tags;
	}
	$empty = '' === $fields['content'];
	$legacy_link = (bool) get_post_meta( $id, 'p1_note_link_url', true );
	if ( $empty && ! $kept && ! $uploads && ! $legacy_link ) {
		return new WP_Error( 'glaze_note_empty', __( 'Write something or add an image.', 'glaze' ) );
	}
	if ( $uploads && count( $kept ) + count( $uploads ) > 4 ) {
		return new WP_Error( 'glaze_note_images', __( 'You can attach up to four images with upload permission.', 'glaze' ) );
	}

	$uploaded = westnote_glaze_attach_note_images( $id, $uploads, get_current_user_id() );
	if ( is_wp_error( $uploaded ) ) {
		return $uploaded;
	}
	$images         = array_merge( array_diff( wp_parse_id_list( (array) $old_images ), $removed ), $uploaded );
	$old_tags       = array_map( 'intval', wp_list_pluck( $old_terms, 'term_id' ) );
	$images_changed = (bool) ( $removed || $uploaded );
	$saved          = false;
	try {
		if ( null !== $tags && is_wp_error( wp_set_object_terms( $id, $tags, 'feng_talk_tag' ) ) ) {
			return $failure;
		}
		if ( $images_changed && (array) $old_images !== $images && ! update_post_meta( $id, '_feng_talk_images', $images ) ) {
			return $failure;
		}
		$title = $note->post_title;
		if ( in_array( $title, array( '', 'Photo note', __( 'Photo note', 'glaze' ), trim( wp_strip_all_tags( $note->post_content ) ) ), true ) ) {
			$title = $empty && $images ? __( 'Photo note', 'glaze' ) : '';
		}
		$content = $note->post_content;
		if ( trim( westnote_glaze_note_editable_content( $note ) ) !== $fields['content'] ) {
			$content = westnote_glaze_note_has_markup( $note ) ? wp_kses_post( $fields['content'] ) : esc_html( $fields['content'] );
		}
		$saved   = $id === wp_update_post( wp_slash( array( 'ID' => $id, 'post_title' => $title, 'post_content' => $content ) ), true );
		return $saved ? $id : $failure;
	} finally {
		if ( ! $saved ) {
			if ( null !== $tags ) {
				wp_set_object_terms( $id, $old_tags, 'feng_talk_tag' );
			}
			if ( $images_changed ) {
				if ( '' === $old_images ) {
					delete_post_meta( $id, '_feng_talk_images' );
				} else {
					update_post_meta( $id, '_feng_talk_images', $old_images );
				}
			}
			westnote_glaze_delete_note_uploads( $uploaded );
		}
	}
}

function westnote_glaze_manage_note( $id, $operation, $version, $fields, $files, $removed ) {
	global $wpdb;
	$key = hash( 'sha256', $wpdb->posts . ':post:' . $id );
	return westnote_glaze_note_with_lock( $key, static function () use ( $id, $operation, $version, $fields, $files, $removed ) {
		clean_post_cache( $id );
		$note   = get_post( $id );
		$delete = 'delete' === $operation;
		if ( ! westnote_glaze_can_manage_note( $note, $delete ? 'delete' : 'edit' ) ) {
			return new WP_Error( 'glaze_note_permission', __( 'You do not have permission to manage this short note.', 'glaze' ) );
		}
		if ( ! hash_equals( westnote_glaze_note_version( $note ), $version ) ) {
			return new WP_Error( 'glaze_note_conflict', __( 'This short note changed elsewhere. Reload the page before editing it again.', 'glaze' ) );
		}
		if ( $delete ) {
			return EMPTY_TRASH_DAYS && wp_trash_post( $id ) ? $id : new WP_Error( 'glaze_note_delete', __( 'The short note could not be moved to the trash.', 'glaze' ) );
		}

		$uploads = westnote_glaze_note_uploads( $files );
		if ( is_wp_error( $uploads ) ) {
			return $uploads;
		}
		return westnote_glaze_update_note( $note, $fields, $uploads, $removed );
	} );
}

function westnote_glaze_submit_note() {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || '1' !== ( $_GET['glaze_note_submit'] ?? '' ) || ! is_page() ) {
		return;
	}
	$page = get_queried_object();
	if ( 'publish' !== $page->post_status || '' !== $page->post_password || 'talks' !== westnote_glaze_collection_type( $page ) ) {
		return;
	}

	nocache_headers();
	$input = wp_unslash( $_POST );
	$id    = (int) ( is_scalar( $input['glaze_note_id'] ?? null ) ? $input['glaze_note_id'] : 0 );
	$op    = 'delete' === ( $input['glaze_note_operation'] ?? '' ) ? 'delete' : ( $id > 0 ? 'edit' : 'new' );
	$note  = $id > 0 ? get_post( $id ) : null;
	$ajax  = '1' === ( $_SERVER['HTTP_X_GLAZE_TALK'] ?? '' );

	$allowed = 'new' === $op ? westnote_glaze_can_publish_note() : westnote_glaze_can_manage_note( $note, $op );
	if ( ! $allowed ) {
		$message = __( 'You do not have permission to manage this short note.', 'glaze' );
		if ( $ajax ) {
			wp_send_json_error( array( 'message' => $message ), 403 );
		}
		wp_die( esc_html( $message ), '', array( 'response' => 403 ) );
	}

	$raw     = $input['glaze_note_content'] ?? '';
	$raw     = is_string( $raw ) ? wp_check_invalid_utf8( $raw ) : '';
	$content = trim( $raw );
	$keyword = $input['glaze_note_keywords'] ?? null;
	$keyword = is_string( $keyword ) ? sanitize_textarea_field( $keyword ) : null;
	$request = $input['glaze_note_request'] ?? '';
	$request = is_string( $request ) && wp_is_uuid( $request, 4 ) ? $request : '';
	$version = $input['glaze_note_version'] ?? '';
	$version = is_string( $version ) ? $version : '';
	$nonce   = $input['glaze_note_nonce'] ?? '';
	$removed = wp_parse_id_list( $input['glaze_note_remove_images'] ?? array() );
	$fields  = array( 'content' => $content, 'keywords' => $keyword );
	$files   = $_FILES['glaze_note_images'] ?? array();

	if ( ( 'new' === $op && ! $request ) || ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, 'glaze_publish_note_' . $page->ID ) ) {
		$result = new WP_Error( 'glaze_note_session', __( 'The form expired or the upload was too large. Please try again.', 'glaze' ) );
	} elseif ( 'delete' !== $op && mb_strlen( $content ) > 4000 && ( ! $note || $content !== trim( westnote_glaze_note_editable_content( $note ) ) ) ) {
		$result = new WP_Error( 'glaze_note_length', __( 'Use up to 4,000 characters and four keywords of up to 20 characters each.', 'glaze' ) );
	} elseif ( 'new' === $op ) {
		$result = westnote_glaze_create_note( $fields, $request, $files );
	} else {
		$result = westnote_glaze_manage_note( $id, $op, $version, $fields, $files, $removed );
	}
	if ( is_wp_error( $result ) ) {
		if ( $ajax ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}
		set_query_var( 'glaze_note_form', array(
			'mode'     => $op,
			'id'       => $id,
			'content'  => $raw,
			'keywords' => $keyword ?? '',
			'request'  => $request,
			'version'  => $version,
			'removed'  => $removed,
			'error'    => $result->get_error_message(),
		) );
		return;
	}

	$number = 'new' === $op ? 1 : westnote_glaze_note_page_number();
	if ( 'delete' === $op && $number > 1 ) {
		$latest = westnote_glaze_notes_query( westnote_glaze_notes_per_page(), 1, true );
		$number = min( $number, max( 1, $latest->max_num_pages ) );
	}
	$url = westnote_glaze_note_page_link( $number );
	$url = 'delete' === $op ? add_query_arg( 'glaze_note_deleted', '1', $url ) : $url . '#note-' . $result;
	if ( $ajax ) {
		wp_send_json_success( array( 'location' => $url ) );
	}
	wp_safe_redirect( $url, 303 );
	exit;
}
add_action( 'template_redirect', 'westnote_glaze_submit_note', 9 );

function westnote_glaze_note_form_state() {
	$state = get_query_var( 'glaze_note_form', array() );
	$mode  = $state['mode'] ?? ( $_GET['glaze_note_editor'] ?? '' );
	$id    = (int) ( $state['id'] ?? ( is_scalar( $_GET['glaze_note_id'] ?? null ) ? $_GET['glaze_note_id'] : 0 ) );
	if ( 'new' === $mode && westnote_glaze_can_publish_note() ) {
		return $state + array( 'mode' => 'new' );
	}
	if ( in_array( $mode, array( 'edit', 'delete' ), true ) && $id > 0 ) {
		$note = get_post( $id );
		if ( westnote_glaze_can_manage_note( $note, $mode ) ) {
			return $state + array( 'mode' => $mode ) + westnote_glaze_note_editor_data( $note );
		}
	}
	return array();
}
