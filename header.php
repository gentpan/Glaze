<?php $glaze_background = westnote_glaze_get_background(); ?>
<!doctype html>
<html <?php language_attributes(); ?>
	data-glaze-palette="<?php echo esc_attr( westnote_glaze_get_palette() ); ?>"
	data-glaze-palettes="<?php echo esc_attr( implode( ' ', array_keys( westnote_glaze_palettes() ) ) ); ?>"
	data-glaze-scheme="<?php echo esc_attr( westnote_glaze_get_scheme() ); ?>"
	data-glaze-background="<?php echo esc_attr( $glaze_background ); ?>"
	data-glaze-corners="<?php echo esc_attr( westnote_glaze_get_corner_mode() ); ?>"
	data-glaze-content-width="<?php echo esc_attr( sprintf( '%04d', westnote_glaze_get_content_width() ) ); ?>"
	<?php if ( is_customize_preview() ) : ?>data-glaze-customize-preview<?php endif; ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if ( 'none' !== $glaze_background ) : ?>
	<div class="glaze-light-ripples" aria-hidden="true"></div>
<?php endif; ?>
<a class="screen-reader-text skip-link" href="#content"><?php esc_html_e( 'Skip to content', 'glaze' ); ?></a>
<header class="site-header" id="site-top">
	<div class="site-brand">
		<?php if ( has_custom_logo() ) : ?>
			<?php the_custom_logo(); ?>
		<?php endif; ?>
		<a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php if ( ! has_custom_logo() ) : ?>
				<span class="site-brand-mark squircle" aria-hidden="true"></span>
			<?php endif; ?>
			<span class="site-title-label"><?php echo westnote_glaze_site_title_markup(); ?></span>
		</a>
		<?php $site_description = get_bloginfo( 'description' ); ?>
		<?php if ( '' !== $site_description ) : ?>
			<p class="site-description" title="<?php echo esc_attr( $site_description ); ?>"><?php echo esc_html( $site_description ); ?></p>
		<?php endif; ?>
	</div>
	<div class="header-controls">
		<details class="header-search" data-glaze-header-search>
			<summary class="header-control header-search-toggle squircle" aria-label="<?php esc_attr_e( 'Search', 'glaze' ); ?>" aria-controls="glaze-header-search" aria-keyshortcuts="Meta+K Control+K">
				<svg class="header-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" aria-hidden="true"><circle cx="10.8" cy="10.8" r="7.2"/><path d="m16 16 4.6 4.6"/></svg>
				<svg class="header-search-close" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" aria-hidden="true"><path d="m5 5 14 14M19 5 5 19"/></svg>
			</summary>
			<div class="header-popup header-search-popup glaze-panel t-dropdown" id="glaze-header-search" data-origin="top-right">
				<?php get_search_form( array( 'aria_label' => __( 'Header search', 'glaze' ), 'glaze_header' => true ) ); ?>
			</div>
		</details>
		<?php get_template_part( 'template-parts/header-account' ); ?>
	</div>
</header>

<div class="site-dock">
<nav class="site-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'glaze' ); ?>">
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'menu_class'     => 'site-menu',
			'container'      => false,
			'depth'          => 1,
			'fallback_cb'    => 'westnote_glaze_primary_navigation',
		)
	);
	?>
</nav>
	<?php if ( is_singular( 'post' ) ) : ?>
		<?php get_template_part( 'template-parts/post-shortcuts' ); ?>
	<?php endif; ?>
</div>
