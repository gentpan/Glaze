<?php
get_header();
get_template_part( 'template-parts/layout/start' );

while ( have_posts() ) {
	the_post();
	get_template_part( 'template-parts/entry' );
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}
}

get_template_part( 'template-parts/layout/end' );
get_footer();
