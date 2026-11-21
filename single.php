<?php
get_header();
get_template_part( 'template-parts/layout/start' );

while ( have_posts() ) {
	the_post();
	get_template_part( 'template-parts/entry' );
	if ( is_singular( 'post' ) && ! post_password_required() ) {
		get_template_part( 'template-parts/post-toc' );
	}
	get_template_part( 'template-parts/post-navigation' );
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}
}

get_template_part( 'template-parts/layout/end' );
get_footer();
