<?php
defined( 'ABSPATH' ) || exit;

function westnote_glaze_icon_url( $name ) {
	return get_parent_theme_file_uri( 'assets/images/menu-icons/' . $name . '.svg' );
}

function westnote_glaze_icon_name( $value, $groups ) {
	if ( ! is_string( $value ) || '' === $value ) {
		return '';
	}
	$catalog = westnote_glaze_icon_catalog();
	foreach ( $groups as $group ) {
		if ( isset( $catalog[ $group ][ $value ] ) ) {
			return $value;
		}
	}
	return '';
}

function westnote_glaze_icon_markup( $value, $class = '' ) {
	$name = westnote_glaze_icon_name( $value, array( 'pages', 'categories', 'social' ) );
	if ( '' === $name ) {
		return '';
	}
	return '<span aria-hidden="true" class="' . esc_attr( trim( 'glaze-icon ' . $class ) ) . '" data-glaze-icon="' . esc_attr( $name ) . '"></span>';
}

function westnote_glaze_icon_picker( $field, $value, $groups, $empty_label ) {
	$catalog = westnote_glaze_icon_catalog();
	$labels  = array(
		'pages'      => __( 'Blog pages', 'glaze' ),
		'categories' => __( 'Blog categories', 'glaze' ),
		'social'     => __( 'Social platforms', 'glaze' ),
	);
	?>
	<details class="glaze-icon-picker">
		<summary><?php esc_html_e( 'Choose a preset icon', 'glaze' ); ?></summary>
		<label class="glaze-icon-default">
			<input type="radio" name="<?php echo esc_attr( $field ); ?>" value="" <?php checked( '', $value ); ?>>
			<?php echo esc_html( $empty_label ); ?>
		</label>
		<?php foreach ( $groups as $group ) : ?>
			<details <?php echo count( $groups ) === 1 || isset( $catalog[ $group ][ $value ] ) ? 'open' : ''; ?>>
				<summary><?php echo esc_html( $labels[ $group ] ); ?></summary>
				<div class="glaze-icon-options">
					<?php foreach ( $catalog[ $group ] as $name => $label ) : ?>
						<label>
							<input type="radio" name="<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( $name ); ?>" <?php checked( $name, $value ); ?>>
							<img src="<?php echo esc_url( westnote_glaze_icon_url( $name ) ); ?>" width="22" height="22" alt="" loading="lazy">
							<span><?php echo esc_html( $label ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</details>
		<?php endforeach; ?>
	</details>
	<?php
}

function westnote_glaze_icon_picker_assets() {
	$screen = get_current_screen();
	if ( ! $screen || ( 'nav-menus' !== $screen->base && 'page' !== $screen->post_type && 'category' !== $screen->taxonomy ) ) {
		return;
	}
	westnote_glaze_enqueue_stylesheet( 'westnote-glaze-settings', 'admin-settings' );
}
add_action( 'admin_enqueue_scripts', 'westnote_glaze_icon_picker_assets' );

function westnote_glaze_page_icon_fields( $post ) {
	wp_nonce_field( 'glaze_save_page_icon', 'glaze_page_icon_nonce' );
	$value = westnote_glaze_icon_name( get_post_meta( $post->ID, '_glaze_page_icon', true ), array( 'pages' ) );
	westnote_glaze_icon_picker( 'glaze_page_icon', $value, array( 'pages' ), __( 'No preset icon', 'glaze' ) );
}

function westnote_glaze_add_page_icon_box() {
	add_meta_box( 'glaze-page-icon', __( 'Glaze Page Icon', 'glaze' ), 'westnote_glaze_page_icon_fields', 'page', 'side' );
}
add_action( 'add_meta_boxes_page', 'westnote_glaze_add_page_icon_box' );

function westnote_glaze_save_page_icon( $post_id ) {
	if ( ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['glaze_page_icon_nonce'], $_POST['glaze_page_icon'] ) || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['glaze_page_icon_nonce'] ) ), 'glaze_save_page_icon' ) ) {
		return;
	}
	update_post_meta( $post_id, '_glaze_page_icon', westnote_glaze_icon_name( wp_unslash( $_POST['glaze_page_icon'] ), array( 'pages' ) ) );
}
add_action( 'save_post_page', 'westnote_glaze_save_page_icon' );

function westnote_glaze_category_icon_fields( $term = null ) {
	wp_nonce_field( 'glaze_save_category_icon', 'glaze_category_icon_nonce' );
	$value = $term ? westnote_glaze_icon_name( get_term_meta( $term->term_id, '_glaze_category_icon', true ), array( 'categories' ) ) : '';
	westnote_glaze_icon_picker( 'glaze_category_icon', $value, array( 'categories' ), __( 'No preset icon', 'glaze' ) );
}

function westnote_glaze_add_category_icon() {
	?>
	<div class="form-field">
		<p><strong><?php esc_html_e( 'Glaze Category Icon', 'glaze' ); ?></strong></p>
		<?php westnote_glaze_category_icon_fields(); ?>
	</div>
	<?php
}
add_action( 'category_add_form_fields', 'westnote_glaze_add_category_icon' );

function westnote_glaze_edit_category_icon( $term ) {
	?>
	<tr class="form-field">
		<th scope="row"><?php esc_html_e( 'Glaze Category Icon', 'glaze' ); ?></th>
		<td><?php westnote_glaze_category_icon_fields( $term ); ?></td>
	</tr>
	<?php
}
add_action( 'category_edit_form_fields', 'westnote_glaze_edit_category_icon' );

function westnote_glaze_save_category_icon( $term_id ) {
	if ( ! current_user_can( 'edit_term', $term_id ) || ! isset( $_POST['glaze_category_icon_nonce'], $_POST['glaze_category_icon'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['glaze_category_icon_nonce'] ) ), 'glaze_save_category_icon' ) ) {
		return;
	}
	update_term_meta( $term_id, '_glaze_category_icon', westnote_glaze_icon_name( wp_unslash( $_POST['glaze_category_icon'] ), array( 'categories' ) ) );
}
add_action( 'created_category', 'westnote_glaze_save_category_icon' );
add_action( 'edited_category', 'westnote_glaze_save_category_icon' );
