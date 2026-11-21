<?php
get_header();
get_template_part( 'template-parts/layout/start' );
?>
<header class="page-heading glaze-panel">
	<?php
	$category_icon = is_category() ? get_term_meta( get_queried_object_id(), '_glaze_category_icon', true ) : '';
	$icon = westnote_glaze_icon_markup( $category_icon ) ?: westnote_glaze_icon_markup( is_category() ? 'categories' : ( is_tag() ? 'tags' : 'archives' ) );
	the_archive_title( '<h1 class="glaze-page-title"><span class="glaze-page-title-icon squircle">' . $icon . '</span><span>', '</span></h1>' );
	?>
	<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
</header>
<?php
get_template_part( 'template-parts/loop' );
get_template_part( 'template-parts/layout/end' );
get_footer();
