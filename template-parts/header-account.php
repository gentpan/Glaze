<?php if ( is_user_logged_in() ) : ?>
	<?php
	$user     = wp_get_current_user();
	$avatar   = get_avatar( $user->ID, 36, '', '' );
	$settings = current_user_can( 'manage_options' ) ? admin_url( 'options-general.php' ) : get_edit_profile_url( $user->ID );
	?>
	<details class="header-account" data-glaze-header-account>
		<summary class="header-control header-avatar squircle" aria-label="<?php esc_attr_e( 'Account menu', 'glaze' ); ?>" aria-controls="glaze-account-menu">
			<?php if ( $avatar ) : ?>
				<?php echo $avatar; ?>
			<?php else : ?>
				<span aria-hidden="true"><?php echo esc_html( wp_html_excerpt( $user->display_name, 1 ) ); ?></span>
			<?php endif; ?>
		</summary>
		<nav class="header-popup header-account-popup glaze-panel t-dropdown" id="glaze-account-menu" data-origin="top-right" aria-label="<?php esc_attr_e( 'Account menu', 'glaze' ); ?>">
			<p class="header-account-name"><?php echo esc_html( $user->display_name ); ?></p>
			<?php if ( current_user_can( 'edit_posts' ) ) : ?>
				<a class="squircle" href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>"><?php esc_html_e( 'Write a post', 'glaze' ); ?></a>
			<?php endif; ?>
			<?php if ( current_user_can( 'moderate_comments' ) ) : ?>
				<a class="squircle" href="<?php echo esc_url( admin_url( 'edit-comments.php' ) ); ?>"><?php esc_html_e( 'Manage comments', 'glaze' ); ?></a>
			<?php endif; ?>
			<?php if ( current_user_can( 'switch_themes' ) ) : ?>
				<a class="squircle" href="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>"><?php esc_html_e( 'Switch theme', 'glaze' ); ?></a>
			<?php endif; ?>
			<a class="squircle" href="<?php echo esc_url( $settings ); ?>"><?php esc_html_e( 'Settings', 'glaze' ); ?></a>
			<a class="squircle" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log out', 'glaze' ); ?></a>
		</nav>
	</details>
<?php else : ?>
	<button type="button" class="header-control header-avatar squircle" data-glaze-open-login aria-label="<?php esc_attr_e( 'Log in', 'glaze' ); ?>" aria-haspopup="dialog" aria-controls="glaze-login-dialog" hidden>
		<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="10" r="3"/><path d="M5.3 18a7.2 7.2 0 0 1 13.4 0"/></svg>
	</button>
	<dialog class="glaze-login-dialog glaze-panel" id="glaze-login-dialog" aria-labelledby="glaze-login-title">
		<div class="glaze-login-heading">
			<h2 id="glaze-login-title"><?php esc_html_e( 'Log in', 'glaze' ); ?></h2>
			<button type="button" class="header-control squircle" data-glaze-close-login aria-label="<?php esc_attr_e( 'Close login', 'glaze' ); ?>">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
			</button>
		</div>
		<form class="glaze-login-form" method="post" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" data-glaze-login-form
			data-login-error="<?php esc_attr_e( 'Login failed. Check your username and password and try again.', 'glaze' ); ?>"
			data-network-error="<?php esc_attr_e( 'Unable to reach the login service. Please try again.', 'glaze' ); ?>">
			<label for="glaze-login-username"><?php esc_html_e( 'Username or email', 'glaze' ); ?></label>
			<input class="squircle" id="glaze-login-username" name="log" autocomplete="username" autocapitalize="none" required autofocus>
			<label for="glaze-login-password"><?php esc_html_e( 'Password', 'glaze' ); ?></label>
			<input class="squircle" id="glaze-login-password" type="password" name="pwd" autocomplete="current-password" required>
			<input type="hidden" name="testcookie" value="1">
			<label class="glaze-login-remember"><input type="checkbox" name="rememberme" value="forever"><?php esc_html_e( 'Remember me', 'glaze' ); ?></label>
			<p class="glaze-login-error" role="alert" hidden></p>
			<button type="submit" class="search-submit control-button"><?php esc_html_e( 'Log in', 'glaze' ); ?></button>
		</form>
	</dialog>
<?php endif; ?>
