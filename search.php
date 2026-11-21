<?php
get_header();
get_template_part( 'template-parts/layout/start' );
?>
<header class="page-heading glaze-panel">
	<h1 class="glaze-page-title">
		<span class="glaze-page-title-icon squircle"><?php echo westnote_glaze_icon_markup( 'search' ); ?></span>
		<span><?php printf( esc_html__( 'Search results for “%s”', 'glaze' ), get_search_query() ); ?></span>
	</h1>
	<?php get_search_form( array( 'aria_label' => __( 'Search results', 'glaze' ) ) ); ?>
</header>
<?php
get_template_part( 'template-parts/loop' );
get_template_part( 'template-parts/layout/end' );
get_footer();
