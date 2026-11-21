<?php
get_header();
get_template_part( 'template-parts/layout/start' );
?>
<section class="page-heading glaze-panel error-page">
	<h1 class="glaze-page-title">
		<span class="glaze-page-title-icon squircle"><?php echo westnote_glaze_icon_markup( 'search' ); ?></span>
		<span><?php esc_html_e( 'Page not found', 'glaze' ); ?></span>
	</h1>
	<p><?php esc_html_e( 'The page may have moved. Search the site or return to the homepage.', 'glaze' ); ?></p>
	<?php get_search_form( array( 'aria_label' => __( 'Search for a page', 'glaze' ) ) ); ?>
	<a class="control-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'glaze' ); ?></a>
</section>
<?php
get_template_part( 'template-parts/layout/end' );
get_footer();
