<?php
defined( 'ABSPATH' ) || exit;

function westnote_glaze_collection_type( $page ) {
	$templates = array(
		'page-memos.php'    => 'talks',
		'pages/talks.php'   => 'talks',
		'pages/friends.php' => 'links',
	);
	$slugs = array(
		'archives'      => 'archives',
		'links'         => 'links',
		'friends'       => 'links',
		'talks'         => 'talks',
		'memos'         => 'talks',
		'notes'         => 'talks',
		'feeds'         => 'subscriptions',
		'subscriptions' => 'subscriptions',
	);
	return $templates[ get_page_template_slug( $page ) ] ?? $slugs[ $page->post_name ] ?? '';
}

function westnote_glaze_collection_url( $type ) {
	foreach ( get_pages() as $page ) {
		if ( '' === $page->post_password && $type === westnote_glaze_collection_type( $page ) ) {
			return get_permalink( $page );
		}
	}
	return '';
}

function westnote_glaze_archive_posts() {
	static $posts;
	if ( null === $posts ) {
		$posts = get_posts( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'has_password'   => false,
			'posts_per_page' => -1,
			'orderby'        => array( 'date' => 'DESC', 'ID' => 'DESC' ),
		) );
	}
	return $posts;
}

function westnote_glaze_archive_post_views( $post_id ) {
	return max( 0, (int) get_post_meta( $post_id, 'p1_post_views', true ), (int) get_post_meta( $post_id, '_feng_views', true ) );
}

function westnote_glaze_archive_fragment() {
	if ( '1' !== ( $_GET['glaze_archive_fragment'] ?? '' ) || ! is_page() ) {
		return;
	}

	$page = get_queried_object();
	if ( 'publish' !== $page->post_status || '' !== $page->post_password || 'archives' !== westnote_glaze_collection_type( $page ) ) {
		return;
	}

	nocache_headers();
	get_template_part( 'template-parts/collections/archives', null, array( 'fragment' => true ) );
	exit;
}
add_action( 'template_redirect', 'westnote_glaze_archive_fragment' );

function westnote_glaze_notes_per_page() {
	return max( 1, (int) get_option( 'posts_per_page' ) );
}

function westnote_glaze_note_post_types() {
	// Legacy records can outlive the theme that registered their post type.
	return array( 'talk', 'p1_note', 'feng_talk' );
}

function westnote_glaze_register_notes() {
	if ( ! post_type_exists( 'talk' ) ) {
		register_post_type(
			'talk',
			array(
				'labels' => array(
					'name'          => __( 'Short notes', 'glaze' ),
					'singular_name' => __( 'Short note', 'glaze' ),
					'add_new'       => __( 'Add short note', 'glaze' ),
					'add_new_item'  => __( 'Add short note', 'glaze' ),
					'edit_item'     => __( 'Edit short note', 'glaze' ),
				),
				'public'           => false,
				'show_ui'          => true,
				'rewrite'          => false,
				'map_meta_cap'     => true,
				'delete_with_user' => false,
				'menu_icon'        => 'dashicons-format-status',
				'supports'         => array( 'title', 'editor', 'author', 'revisions' ),
			)
		);
	}

	foreach ( array( 'p1_note', 'feng_talk' ) as $legacy_type ) {
		if ( ! post_type_exists( $legacy_type ) ) {
			register_post_type( $legacy_type, array(
				'public'           => false,
				'rewrite'          => false,
				'map_meta_cap'     => true,
				'delete_with_user' => false,
				'supports'         => array( 'title', 'editor', 'author', 'revisions' ),
			) );
		}
	}

	if ( taxonomy_exists( 'feng_talk_tag' ) ) {
		foreach ( westnote_glaze_note_post_types() as $type ) {
			register_taxonomy_for_object_type( 'feng_talk_tag', $type );
		}
		return;
	}

	register_taxonomy(
		'feng_talk_tag',
		westnote_glaze_note_post_types(),
		array(
			'labels' => array(
				'name'          => __( 'Short note keywords', 'glaze' ),
				'singular_name' => __( 'Keyword', 'glaze' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'rewrite'           => false,
		)
	);
}
add_action( 'init', 'westnote_glaze_register_notes' );

function westnote_glaze_notes_query( $limit, $page = 1, $paginate = false ) {
	return new WP_Query(
		array(
			'post_type'           => westnote_glaze_note_post_types(),
			'post_status'         => 'publish',
			'has_password'        => false,
			'posts_per_page'      => $limit,
			'paged'               => $page,
			'orderby'             => array( 'date' => 'DESC', 'ID' => 'DESC' ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => ! $paginate,
		)
	);
}

function westnote_glaze_note_image_ids( $note ) {
	$ids = wp_parse_id_list( (array) get_post_meta( $note->ID, '_feng_talk_images', true ) );
	return array_filter( $ids, 'wp_attachment_is_image' );
}

function westnote_glaze_note_thumbnail( $note ) {
	$ids = array_merge( westnote_glaze_note_image_ids( $note ), array( get_post_thumbnail_id( $note ) ) );
	foreach ( $ids as $id ) {
		$url = wp_get_attachment_image_url( $id, 'thumbnail' );
		if ( $url ) {
			return $url;
		}
	}

	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return '';
	}
	$images = new WP_HTML_Tag_Processor( $note->post_content );
	while ( $images->next_tag( 'IMG' ) ) {
		$url = esc_url_raw( (string) $images->get_attribute( 'src' ), array( 'http', 'https' ) );
		if ( $url ) {
			return $url;
		}
	}
	return '';
}

require_once get_parent_theme_file_path( 'inc/talks-publish.php' );
