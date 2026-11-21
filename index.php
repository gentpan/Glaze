<?php
get_header();
get_template_part( 'template-parts/layout/start' );
?>
<header class="page-heading glaze-panel">
	<h1><?php esc_html_e( 'Posts', 'glaze' ); ?></h1>
</header>
<?php
get_template_part( 'template-parts/loop' );
get_template_part( 'template-parts/layout/end' );
get_footer();
