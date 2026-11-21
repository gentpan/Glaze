<?php
$page       = get_queried_object_id();
$state      = westnote_glaze_note_form_state();
$mode       = $state['mode'] ?? '';
$editing    = 'edit' === $mode;
$deleting   = 'delete' === $mode;
$base       = westnote_glaze_note_page_link( westnote_glaze_note_page_number() );
$action     = add_query_arg( 'glaze_note_submit', '1', $base );
$error      = $state['error'] ?? '';
$taxonomy   = get_taxonomy( 'feng_talk_tag' );
$can_tag    = $taxonomy && current_user_can( $taxonomy->cap->assign_terms );
$can_upload = current_user_can( 'upload_files' );
$images     = $editing ? ( $state['images'] ?? array() ) : array();
?>
<?php if ( westnote_glaze_can_publish_note() || '1' === ( $_GET['glaze_note_deleted'] ?? '' ) ) : ?>
	<div class="talk-toolbar">
		<?php if ( '1' === ( $_GET['glaze_note_deleted'] ?? '' ) ) : ?>
			<p class="talk-feedback" role="status"><?php esc_html_e( 'Short note moved to the trash.', 'glaze' ); ?></p>
		<?php endif; ?>
		<?php if ( westnote_glaze_can_publish_note() ) : ?>
			<a class="control-button talk-compose-trigger squircle" href="<?php echo esc_url( add_query_arg( 'glaze_note_editor', 'new', $base ) . '#talk-compose' ); ?>" data-glaze-talk-compose aria-haspopup="dialog" aria-controls="talk-compose">
				<span aria-hidden="true">+</span><?php esc_html_e( 'Write a short note', 'glaze' ); ?>
			</a>
		<?php endif; ?>
	</div>
<?php endif; ?>

<dialog id="talk-compose" class="talk-editor glaze-panel squircle" aria-labelledby="talk-compose-title"
	data-new-title="<?php esc_attr_e( 'Write a short note', 'glaze' ); ?>"
	data-edit-title="<?php esc_attr_e( 'Edit short note', 'glaze' ); ?>"
	data-publish-label="<?php esc_attr_e( 'Publish short note', 'glaze' ); ?>"
	data-save-label="<?php esc_attr_e( 'Save changes', 'glaze' ); ?>"
	data-upload-count-error="<?php esc_attr_e( 'This short note can have up to four images.', 'glaze' ); ?>"
	data-upload-size-error="<?php esc_attr_e( 'An image could not be uploaded. Check its size and try again.', 'glaze' ); ?>"
	data-upload-format-error="<?php esc_attr_e( 'Choose JPEG, PNG, WebP, or GIF images.', 'glaze' ); ?>"
	data-network-error="<?php esc_attr_e( 'The request could not be completed. Please try again.', 'glaze' ); ?>"
	<?php echo in_array( $mode, array( 'new', 'edit' ), true ) ? 'open' : ''; ?>>
	<header class="talk-editor-head">
		<h2 id="talk-compose-title"><?php echo esc_html( $editing ? __( 'Edit short note', 'glaze' ) : __( 'Write a short note', 'glaze' ) ); ?></h2>
		<a class="talk-editor-close squircle" href="<?php echo esc_url( $base ); ?>" aria-label="<?php esc_attr_e( 'Close', 'glaze' ); ?>" data-glaze-talk-close><span aria-hidden="true">×</span></a>
	</header>
	<form class="talk-editor-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( $action . '#talk-compose' ); ?>">
		<input type="hidden" name="glaze_note_nonce" value="<?php echo esc_attr( wp_create_nonce( 'glaze_publish_note_' . $page ) ); ?>">
		<input type="hidden" name="glaze_note_request" value="<?php echo esc_attr( ( $state['request'] ?? '' ) ?: wp_generate_uuid4() ); ?>">
		<input type="hidden" name="glaze_note_id" value="<?php echo $editing ? (int) $state['id'] : 0; ?>">
		<input type="hidden" name="glaze_note_version" value="<?php echo esc_attr( $editing ? $state['version'] : '' ); ?>">
		<div class="talk-editor-body">
			<p class="talk-editor-error squircle" role="alert"<?php echo $error && ! $deleting ? '' : ' hidden'; ?>><?php echo esc_html( $deleting ? '' : $error ); ?></p>
			<div class="talk-writing squircle">
				<label class="screen-reader-text" for="glaze-note-content"><?php esc_html_e( 'Note', 'glaze' ); ?></label>
				<textarea id="glaze-note-content" name="glaze_note_content" rows="5" maxlength="<?php echo max( 4000, mb_strlen( $state['content'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'What would you like to remember?', 'glaze' ); ?>" autofocus><?php echo esc_textarea( $deleting ? '' : ( $state['content'] ?? '' ) ); ?></textarea>
				<div class="talk-writing-meta">
					<small data-glaze-talk-markup<?php echo $editing && ! empty( $state['markup'] ) ? '' : ' hidden'; ?>><?php esc_html_e( 'This note uses HTML formatting.', 'glaze' ); ?></small>
					<span data-glaze-talk-count hidden>0 / 4000</span>
				</div>
			</div>
			<?php if ( $can_tag ) : ?>
				<div class="talk-editor-field">
					<label for="glaze-note-keywords"><?php esc_html_e( 'Keyword', 'glaze' ); ?></label>
					<input id="glaze-note-keywords" class="squircle" type="text" name="glaze_note_keywords" maxlength="<?php echo max( 100, mb_strlen( $state['keywords'] ?? '' ) ); ?>" value="<?php echo esc_attr( $deleting ? '' : ( $state['keywords'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Separate up to four keywords with commas.', 'glaze' ); ?>">
				</div>
			<?php endif; ?>
			<ul class="talk-editor-images" data-glaze-talk-images<?php echo $images ? '' : ' hidden'; ?>>
				<?php foreach ( $images as $image ) : ?>
					<li class="talk-editor-image squircle">
						<img src="<?php echo esc_url( $image['url'] ); ?>" alt="<?php echo esc_attr( $image['alt'] ); ?>">
						<label>
							<input type="checkbox" name="glaze_note_remove_images[]" value="<?php echo (int) $image['id']; ?>" <?php checked( in_array( $image['id'], $state['removed'] ?? array(), true ) ); ?>>
							<span><?php esc_html_e( 'Remove image', 'glaze' ); ?></span>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
			<ul class="talk-editor-images" data-glaze-talk-previews hidden></ul>
			<?php if ( $can_upload ) : ?>
				<div class="talk-editor-attachments">
					<label class="control-button talk-editor-attach squircle">
						<span aria-hidden="true">+</span><?php esc_html_e( 'Add images', 'glaze' ); ?>
						<input type="file" name="glaze_note_images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple aria-describedby="glaze-note-images-hint" data-limit="<?php echo westnote_glaze_note_upload_limit(); ?>">
					</label>
					<small id="glaze-note-images-hint"><?php printf( esc_html__( 'Up to four images, %s each.', 'glaze' ), esc_html( size_format( westnote_glaze_note_upload_limit() ) ) ); ?></small>
				</div>
			<?php endif; ?>
		</div>
		<footer class="talk-editor-actions">
			<a class="control-button squircle" href="<?php echo esc_url( $base ); ?>" data-glaze-talk-close><?php esc_html_e( 'Cancel', 'glaze' ); ?></a>
			<button type="submit" class="control-button talk-editor-primary squircle"><?php echo esc_html( $editing ? __( 'Save changes', 'glaze' ) : __( 'Publish short note', 'glaze' ) ); ?></button>
		</footer>
	</form>
	<template data-glaze-talk-image-tile>
		<li class="talk-editor-image squircle">
			<img alt="">
			<label><input type="checkbox" name="glaze_note_remove_images[]"><span><?php esc_html_e( 'Remove image', 'glaze' ); ?></span></label>
		</li>
	</template>
</dialog>

<dialog id="talk-delete" class="talk-editor talk-editor-confirm glaze-panel squircle" aria-labelledby="talk-delete-title" aria-describedby="talk-delete-summary" <?php echo $deleting ? 'open' : ''; ?>>
	<header class="talk-editor-head">
		<h2 id="talk-delete-title"><?php esc_html_e( 'Delete this short note?', 'glaze' ); ?></h2>
	</header>
	<form class="talk-editor-form" method="post" action="<?php echo esc_url( $action . '#talk-delete' ); ?>">
		<input type="hidden" name="glaze_note_nonce" value="<?php echo esc_attr( wp_create_nonce( 'glaze_publish_note_' . $page ) ); ?>">
		<input type="hidden" name="glaze_note_operation" value="delete">
		<input type="hidden" name="glaze_note_id" value="<?php echo $deleting ? (int) $state['id'] : 0; ?>">
		<input type="hidden" name="glaze_note_version" value="<?php echo esc_attr( $deleting ? $state['version'] : '' ); ?>">
		<div class="talk-editor-body">
			<p id="talk-delete-summary"><?php esc_html_e( 'The note will move to the WordPress trash, where it can be restored.', 'glaze' ); ?></p>
			<p class="talk-editor-error squircle" role="alert"<?php echo $error && $deleting ? '' : ' hidden'; ?>><?php echo esc_html( $deleting ? $error : '' ); ?></p>
		</div>
		<footer class="talk-editor-actions">
			<a class="control-button squircle" href="<?php echo esc_url( $base ); ?>" data-glaze-talk-close autofocus><?php esc_html_e( 'Cancel', 'glaze' ); ?></a>
			<button type="submit" class="control-button talk-editor-danger squircle"><?php esc_html_e( 'Move to trash', 'glaze' ); ?></button>
		</footer>
	</form>
</dialog>
