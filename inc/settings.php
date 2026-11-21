<?php
defined( 'ABSPATH' ) || exit;

function westnote_glaze_register_settings_page() {
	add_theme_page(
		__( 'Glaze Settings', 'glaze' ),
		__( 'Glaze Settings', 'glaze' ),
		'edit_theme_options',
		'glaze-settings',
		'westnote_glaze_settings_page'
	);
}
add_action( 'admin_menu', 'westnote_glaze_register_settings_page' );

function westnote_glaze_enable_blogroll_manager( $enabled ) {
	if ( is_admin() && ( 'links' === westnote_glaze_home_rail_type( 'left' ) || 'links' === westnote_glaze_home_rail_type( 'right' ) ) ) {
		return 1;
	}
	return $enabled;
}
add_filter( 'pre_option_link_manager_enabled', 'westnote_glaze_enable_blogroll_manager' );

function westnote_glaze_settings_assets( $page ) {
	if ( get_plugin_page_hookname( 'glaze-settings', 'themes.php' ) !== $page ) {
		return;
	}

	westnote_glaze_enqueue_stylesheet( 'westnote-glaze-settings', 'admin-settings' );

	$dependencies = array();
	if ( current_user_can( 'upload_files' ) ) {
		wp_enqueue_media();
		$dependencies[] = 'media-editor';
	}
	$script = 'assets/js/admin-settings.min.js';
	$path   = get_parent_theme_file_path( $script );
	if ( is_file( $path ) ) {
		wp_enqueue_script( 'westnote-glaze-settings', get_parent_theme_file_uri( $script ), $dependencies, (string) filemtime( $path ), true );
	}
}
add_action( 'admin_enqueue_scripts', 'westnote_glaze_settings_assets' );

function westnote_glaze_save_settings() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( '', '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'westnote_glaze_settings' );

	$settings = array(
		'westnote_glaze_palette'       => 'westnote_glaze_sanitize_palette',
		'westnote_glaze_scheme'        => 'westnote_glaze_sanitize_scheme',
		'westnote_glaze_background'    => 'westnote_glaze_sanitize_background',
		'westnote_glaze_corner_mode'   => 'westnote_glaze_sanitize_corner_mode',
		'westnote_glaze_content_width' => 'westnote_glaze_content_width',
	);
	foreach ( westnote_glaze_font_settings() as $setting => list( $script, $default ) ) {
		$settings[ "westnote_glaze_font_{$setting}" ] = static fn( $value ) => westnote_glaze_sanitize_font( $value, $script, $default );
	}
	foreach ( westnote_glaze_home_rail_defaults() as $side => $default ) {
		$settings[ "westnote_glaze_home_rail_{$side}" ] = static fn( $value ) => westnote_glaze_sanitize_home_rail( $value, $default );
	}
	for ( $slot = 1; $slot <= 6; $slot++ ) {
		$settings[ "westnote_glaze_footer_preset_{$slot}" ] = 'westnote_glaze_sanitize_footer_icon_preset';
		$settings[ "westnote_glaze_footer_icon_{$slot}" ] = 'westnote_glaze_sanitize_svg_icon';
		$settings[ "westnote_glaze_footer_url_{$slot}" ]  = 'westnote_glaze_footer_link_url';
	}
	foreach ( $settings as $name => $sanitize ) {
		if ( isset( $_POST[ $name ] ) && is_string( $_POST[ $name ] ) ) {
			set_theme_mod( $name, $sanitize( wp_unslash( $_POST[ $name ] ) ) );
		}
	}

	if ( isset( $_POST['westnote_glaze_pagination_motto'] ) && is_string( $_POST['westnote_glaze_pagination_motto'] ) ) {
		$motto = sanitize_text_field( wp_unslash( $_POST['westnote_glaze_pagination_motto'] ) );
		$saved = westnote_glaze_get_pagination_motto();
		if ( $motto !== $saved['text'] || ( '' !== $motto && '' === $saved['svg'] ) ) {
			$svg = westnote_glaze_generate_text_svg( $motto, 'inc/motto-glyphs.bin', 19, 18 );
			set_theme_mod( 'westnote_glaze_pagination_motto', array( 'text' => $motto, 'svg' => $svg ) );
		}
	}

	if ( current_user_can( 'manage_options' ) ) {
		foreach ( array( 'blogname', 'blogdescription' ) as $name ) {
			if ( isset( $_POST[ $name ] ) && is_string( $_POST[ $name ] ) ) {
				update_option( $name, sanitize_text_field( wp_unslash( $_POST[ $name ] ) ) );
			}
		}
	}

	if ( current_user_can( 'upload_files' ) && isset( $_POST['custom_logo'] ) && is_string( $_POST['custom_logo'] ) ) {
		$attachment_id = absint( wp_unslash( $_POST['custom_logo'] ) );
		if ( 0 === $attachment_id || wp_attachment_is_image( $attachment_id ) ) {
			set_theme_mod( 'custom_logo', $attachment_id );
		}
	}

	$section  = isset( $_POST['glaze_section'] ) && is_string( $_POST['glaze_section'] ) ? sanitize_key( wp_unslash( $_POST['glaze_section'] ) ) : '';
	$redirect = admin_url( 'themes.php?page=glaze-settings&glaze-updated=1' );
	wp_safe_redirect( '' === $section ? $redirect : $redirect . '#' . $section );
	exit;
}
add_action( 'admin_post_westnote_glaze_save_settings', 'westnote_glaze_save_settings' );

function westnote_glaze_setting_select( $name, $label, $choices, $current ) {
	?>
	<div class="glaze-settings__field">
		<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
		<select id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>">
			<?php foreach ( $choices as $value => $text ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $current ); ?>><?php echo esc_html( $text ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php
}

function westnote_glaze_setting_palette() {
	$palettes = westnote_glaze_palettes();
	$current  = westnote_glaze_get_palette();
	?>
	<div class="glaze-settings__field">
		<span class="glaze-settings__label" id="glaze-settings-palette-label"><?php esc_html_e( 'Default palette', 'glaze' ); ?></span>
		<details class="glaze-settings__palette" data-glaze-admin-palette>
			<summary aria-labelledby="glaze-settings-palette-label glaze-settings-palette-current">
				<span class="glaze-settings__swatch" data-glaze-swatch="<?php echo esc_attr( $current ); ?>" aria-hidden="true"></span>
				<span id="glaze-settings-palette-current" data-glaze-palette-current><?php echo esc_html( $palettes[ $current ] ); ?></span>
			</summary>
			<div class="glaze-settings__palette-list" role="radiogroup" aria-labelledby="glaze-settings-palette-label">
				<?php foreach ( $palettes as $value => $label ) : ?>
					<label class="glaze-settings__palette-option">
						<input type="radio" name="westnote_glaze_palette" value="<?php echo esc_attr( $value ); ?>" <?php checked( $value, $current ); ?>>
						<span class="glaze-settings__swatch" data-glaze-swatch="<?php echo esc_attr( $value ); ?>" aria-hidden="true"></span>
						<span data-glaze-palette-name><?php echo esc_html( $label ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</details>
	</div>
	<?php
}

function westnote_glaze_settings_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$locations         = get_nav_menu_locations();
	$menu_link         = empty( $locations['primary'] ) ? admin_url( 'nav-menus.php?action=locations' ) : admin_url( 'nav-menus.php?menu=' . absint( $locations['primary'] ) );
	$width             = westnote_glaze_get_content_width();
	$site_name         = get_option( 'blogname' );
	$palette           = westnote_glaze_get_palette();
	$scheme            = westnote_glaze_get_scheme();
	$corners           = westnote_glaze_get_corner_mode();
	$background        = westnote_glaze_get_background();
	$can_edit_identity = current_user_can( 'manage_options' );
	$can_edit_logo     = current_user_can( 'upload_files' );
	$sections          = array(
		'glaze-appearance'       => array( __( 'Appearance', 'glaze' ), 'art' ),
		'glaze-typography'       => array( __( 'Fonts', 'glaze' ), 'editor-textcolor' ),
		'glaze-site-identity'    => array( __( 'Site identity', 'glaze' ), 'id-alt' ),
		'glaze-home-sidebars'    => array( __( 'Homepage sidebars', 'glaze' ), 'columns' ),
		'glaze-pagination-motto' => array( __( 'Homepage pagination motto', 'glaze' ), 'editor-quote' ),
		'glaze-footer-buttons'   => array( __( 'Glaze Footer Buttons', 'glaze' ), 'admin-links' ),
		'glaze-menus'            => array( __( 'Menus and icons', 'glaze' ), 'menu-alt3' ),
	);
	if ( ! $can_edit_identity && ! $can_edit_logo ) {
		unset( $sections['glaze-site-identity'] );
	}
	?>
	<div class="wrap glaze-settings">
		<header class="glaze-settings__masthead">
			<h1 id="glaze-settings-title"><?php esc_html_e( 'Glaze Settings', 'glaze' ); ?></h1>
			<a class="glaze-settings__visit" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">
				<?php esc_html_e( 'Visit Site' ); ?>
				<span class="dashicons dashicons-external" aria-hidden="true"></span>
			</a>
		</header>
		<hr class="wp-header-end">

		<div class="glaze-settings__workspace">
			<nav class="glaze-settings__nav" aria-labelledby="glaze-settings-title">
				<ul>
					<?php foreach ( $sections as $id => list( $label, $icon ) ) : ?>
						<li>
							<a class="glaze-settings__nav-link" href="#<?php echo esc_attr( $id ); ?>" <?php if ( 'glaze-appearance' === $id ) : ?>aria-current="location"<?php endif; ?>>
								<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
								<?php echo esc_html( $label ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>

			<form class="glaze-settings__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="westnote_glaze_save_settings">
				<input type="hidden" name="glaze_section" value="">
				<?php wp_nonce_field( 'westnote_glaze_settings' ); ?>

				<section class="glaze-settings__section" id="glaze-appearance" aria-labelledby="glaze-appearance-title">
					<h2 id="glaze-appearance-title"><?php echo esc_html( $sections['glaze-appearance'][0] ); ?></h2>
					<div class="glaze-settings__appearance">
						<div class="glaze-settings__fields">
							<div class="glaze-settings__field">
								<label for="westnote_glaze_content_width"><?php esc_html_e( 'Page width', 'glaze' ); ?></label>
								<div class="glaze-settings__width">
									<div class="glaze-settings__unit">
										<input type="number" id="westnote_glaze_content_width" name="westnote_glaze_content_width" min="360" max="1600" step="1" value="<?php echo esc_attr( $width ); ?>" aria-describedby="glaze-width-description" required>
										<span aria-hidden="true">px</span>
									</div>
									<div class="glaze-settings__buttons">
										<?php foreach ( array( 600, 800, 960 ) as $preset ) : ?>
											<button type="button" class="button" data-glaze-width="<?php echo esc_attr( $preset ); ?>" aria-pressed="<?php echo $width === $preset ? 'true' : 'false'; ?>"><?php echo esc_html( $preset ); ?> px</button>
										<?php endforeach; ?>
									</div>
								</div>
								<p class="description" id="glaze-width-description"><?php esc_html_e( 'Choose 600, 800 or 960 px, or enter any width from 360 to 1600 px. The header, content and footer share this width.', 'glaze' ); ?></p>
							</div>
							<?php
							westnote_glaze_setting_palette();
							westnote_glaze_setting_select( 'westnote_glaze_scheme', __( 'Default appearance', 'glaze' ), westnote_glaze_schemes(), $scheme );
							westnote_glaze_setting_select( 'westnote_glaze_corner_mode', __( 'Corner style', 'glaze' ), westnote_glaze_corner_modes(), $corners );
							westnote_glaze_setting_select( 'westnote_glaze_background', __( 'Page background', 'glaze' ), westnote_glaze_backgrounds(), $background );
							?>
						</div>
						<figure class="glaze-settings__preview">
							<figcaption>
								<span class="dashicons dashicons-desktop" aria-hidden="true"></span>
								<?php esc_html_e( 'Appearance preview', 'glaze' ); ?>
							</figcaption>
							<div class="glaze-settings__sample" data-glaze-admin-sample data-glaze-swatch="<?php echo esc_attr( $palette ); ?>" data-scheme="<?php echo esc_attr( $scheme ); ?>" data-corners="<?php echo esc_attr( $corners ); ?>" data-background="<?php echo esc_attr( $background ); ?>" aria-hidden="true">
								<div class="glaze-settings__sample-content">
									<div class="glaze-settings__sample-header">
										<span data-glaze-sample-title><?php echo esc_html( $site_name ); ?></span>
										<span class="glaze-settings__sample-menu"></span>
									</div>
									<div class="glaze-settings__sample-post">
										<span class="glaze-settings__sample-image"></span>
										<span class="glaze-settings__sample-line"></span>
										<span class="glaze-settings__sample-line"></span>
										<span class="glaze-settings__sample-line"></span>
									</div>
									<div class="glaze-settings__sample-footer"><span></span><span></span><span></span></div>
								</div>
							</div>
						</figure>
					</div>
				</section>

				<section class="glaze-settings__section" id="glaze-typography" aria-labelledby="glaze-typography-title">
					<h2 id="glaze-typography-title"><?php echo esc_html( $sections['glaze-typography'][0] ); ?></h2>
					<p class="description"><?php esc_html_e( 'Content fonts apply to the body of posts and pages. Web fonts are loaded from Google Fonts.', 'glaze' ); ?></p>
					<div class="glaze-settings__fields">
						<?php
						foreach ( westnote_glaze_font_settings() as $setting => list( $script, $default, $label ) ) {
							westnote_glaze_setting_select( "westnote_glaze_font_{$setting}", $label, westnote_glaze_font_choices( $script, $default ), westnote_glaze_get_font( $setting, $script, $default ) );
						}
						?>
					</div>
				</section>

				<?php if ( $can_edit_identity || $can_edit_logo ) : ?>
					<section class="glaze-settings__section" id="glaze-site-identity" aria-labelledby="glaze-site-identity-title">
						<h2 id="glaze-site-identity-title"><?php echo esc_html( $sections['glaze-site-identity'][0] ); ?></h2>
						<div class="glaze-settings__fields">
							<?php if ( $can_edit_identity ) : ?>
								<div class="glaze-settings__field">
									<label for="blogname"><?php esc_html_e( 'Site title', 'glaze' ); ?></label>
									<input id="blogname" name="blogname" type="text" value="<?php echo esc_attr( $site_name ); ?>">
								</div>
								<div class="glaze-settings__field">
									<label for="blogdescription"><?php esc_html_e( 'Site description', 'glaze' ); ?></label>
									<input id="blogdescription" name="blogdescription" type="text" value="<?php echo esc_attr( get_option( 'blogdescription' ) ); ?>">
								</div>
							<?php endif; ?>
							<?php if ( $can_edit_logo ) : ?>
								<?php
								$logo_id = absint( get_theme_mod( 'custom_logo', 0 ) );
								$logo    = wp_get_attachment_image_url( $logo_id, 'thumbnail' );
								?>
								<div class="glaze-settings__field">
									<span class="glaze-settings__label"><?php esc_html_e( 'Site logo', 'glaze' ); ?></span>
									<div class="glaze-settings__logo" data-glaze-logo>
										<input name="custom_logo" type="hidden" value="<?php echo esc_attr( $logo_id ); ?>">
										<div class="glaze-settings__logo-frame" aria-hidden="true">
											<span class="dashicons dashicons-format-image"></span>
											<img width="60" height="60" alt="" <?php if ( $logo ) : ?>src="<?php echo esc_url( $logo ); ?>"<?php else : ?>hidden<?php endif; ?>>
										</div>
										<div class="glaze-settings__buttons">
											<button type="button" class="button" data-glaze-pick-logo><?php esc_html_e( 'Choose logo', 'glaze' ); ?></button>
											<button type="button" class="button" data-glaze-remove-logo <?php if ( ! $logo ) : ?>hidden<?php endif; ?>><?php esc_html_e( 'Remove logo', 'glaze' ); ?></button>
										</div>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</section>
				<?php endif; ?>

				<section class="glaze-settings__section" id="glaze-home-sidebars" aria-labelledby="glaze-home-sidebars-title">
					<h2 id="glaze-home-sidebars-title"><?php echo esc_html( $sections['glaze-home-sidebars'][0] ); ?></h2>
					<p class="description"><?php esc_html_e( 'Choose what each side shows on the homepage. Each sidebar displays up to five cards.', 'glaze' ); ?></p>
					<div class="glaze-settings__fields">
						<?php
						$rail_types = westnote_glaze_home_rail_types();
						westnote_glaze_setting_select( 'westnote_glaze_home_rail_left', __( 'Left sidebar', 'glaze' ), $rail_types, westnote_glaze_home_rail_type( 'left' ) );
						westnote_glaze_setting_select( 'westnote_glaze_home_rail_right', __( 'Right sidebar', 'glaze' ), $rail_types, westnote_glaze_home_rail_type( 'right' ) );
						?>
					</div>
					<?php if ( current_user_can( 'manage_links' ) ) : ?>
						<p class="glaze-settings__related"><a href="<?php echo esc_url( admin_url( 'link-manager.php' ) ); ?>"><?php esc_html_e( 'Manage blogroll', 'glaze' ); ?></a></p>
					<?php endif; ?>
				</section>

				<section class="glaze-settings__section" id="glaze-pagination-motto" aria-labelledby="glaze-pagination-motto-title">
					<h2 id="glaze-pagination-motto-title"><?php echo esc_html( $sections['glaze-pagination-motto'][0] ); ?></h2>
					<div class="glaze-settings__field">
						<label for="westnote_glaze_pagination_motto"><?php esc_html_e( 'Motto text', 'glaze' ); ?></label>
						<input id="westnote_glaze_pagination_motto" name="westnote_glaze_pagination_motto" type="text" value="<?php echo esc_attr( westnote_glaze_get_pagination_motto()['text'] ); ?>" aria-describedby="glaze-pagination-motto-description">
						<p class="description" id="glaze-pagination-motto-description"><?php esc_html_e( 'Save to create a handwritten SVG. Leave blank to hide it.', 'glaze' ); ?></p>
					</div>
				</section>

				<section class="glaze-settings__section" id="glaze-footer-buttons" aria-labelledby="glaze-footer-buttons-title">
					<h2 id="glaze-footer-buttons-title"><?php echo esc_html( $sections['glaze-footer-buttons'][0] ); ?></h2>
					<p class="description"><?php esc_html_e( 'Choose a built-in icon or paste a custom SVG, then enter its destination URL.', 'glaze' ); ?></p>
					<div class="glaze-settings__footer-buttons">
						<?php for ( $slot = 1; $slot <= 6; $slot++ ) : ?>
							<?php
							$preset_id = "westnote_glaze_footer_preset_{$slot}";
							$icon_id = "westnote_glaze_footer_icon_{$slot}";
							$url_id  = "westnote_glaze_footer_url_{$slot}";
							$slot_id = "glaze-footer-slot-{$slot}";
							?>
							<div class="glaze-settings__footer-button" role="group" aria-labelledby="<?php echo esc_attr( $slot_id ); ?>">
								<h3 id="<?php echo esc_attr( $slot_id ); ?>"><?php
									/* translators: %d: footer button number. */
									echo esc_html( sprintf( __( 'Button %d', 'glaze' ), $slot ) );
								?></h3>
								<div>
									<?php westnote_glaze_setting_select( $preset_id, __( 'Built-in icon', 'glaze' ), array( '' => __( 'Use custom SVG', 'glaze' ) ) + westnote_glaze_footer_icon_presets(), get_theme_mod( $preset_id, '' ) ); ?>
									<div class="glaze-settings__field">
										<label for="<?php echo esc_attr( $icon_id ); ?>"><?php esc_html_e( 'SVG icon', 'glaze' ); ?></label>
										<textarea class="code" id="<?php echo esc_attr( $icon_id ); ?>" name="<?php echo esc_attr( $icon_id ); ?>" rows="2" dir="ltr"><?php echo esc_textarea( get_theme_mod( $icon_id, '' ) ); ?></textarea>
									</div>
								</div>
								<div class="glaze-settings__field">
									<label for="<?php echo esc_attr( $url_id ); ?>"><?php esc_html_e( 'Destination URL', 'glaze' ); ?></label>
									<input type="url" id="<?php echo esc_attr( $url_id ); ?>" name="<?php echo esc_attr( $url_id ); ?>" value="<?php echo esc_attr( get_theme_mod( $url_id, '' ) ); ?>" dir="ltr">
								</div>
							</div>
						<?php endfor; ?>
					</div>
				</section>

				<section class="glaze-settings__section" id="glaze-menus" aria-labelledby="glaze-menus-title">
					<h2 id="glaze-menus-title"><?php echo esc_html( $sections['glaze-menus'][0] ); ?></h2>
					<div class="glaze-settings__shortcuts">
						<?php
						$shortcuts = array(
							array( $menu_link, __( 'Edit right-side menu', 'glaze' ), 'menu' ),
							array( admin_url( 'edit.php?post_type=page' ), __( 'Edit page icons', 'glaze' ), 'admin-page' ),
							array( admin_url( 'edit-tags.php?taxonomy=category' ), __( 'Edit category icons', 'glaze' ), 'category' ),
						);
						foreach ( $shortcuts as list( $url, $label, $icon ) ) :
							?>
							<a href="<?php echo esc_url( $url ); ?>">
								<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
								<span><?php echo esc_html( $label ); ?></span>
								<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
							</a>
						<?php endforeach; ?>
					</div>
				</section>

				<div class="glaze-settings__save"><?php submit_button( null, 'primary', 'submit', false ); ?></div>
			</form>
		</div>
		<?php if ( isset( $_GET['glaze-updated'] ) && '1' === $_GET['glaze-updated'] ) : ?>
			<div class="glaze-toast-stack" data-glaze-toast-stack data-dismiss-label="<?php esc_attr_e( 'Dismiss notification', 'glaze' ); ?>" data-glaze-saved-message="<?php esc_attr_e( 'Glaze settings saved.', 'glaze' ); ?>"></div>
			<div class="screen-reader-text" role="status" aria-live="polite" aria-atomic="true" data-glaze-toast-status></div>
		<?php endif; ?>
	</div>
	<?php
}
