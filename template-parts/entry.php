<?php
$entry_has_hero = is_singular( 'post' );
$collection     = is_page() ? westnote_glaze_collection_type( get_post() ) : '';
$archive_stats  = 'archives' === $collection && ! post_password_required();
$entry_classes  = $entry_has_hero ? 'entry entry-has-hero' : 'entry glaze-panel';
if ( $collection && ! post_password_required() ) {
	$entry_classes .= ' entry-collection';
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $entry_classes ); ?>>
	<?php if ( $entry_has_hero ) : ?>
		<?php get_template_part( 'template-parts/post-hero' ); ?>
		<div class="entry-main glaze-panel">
		<?php if ( ! post_password_required() ) : ?>
			<div class="entry-reading-controls squircle" hidden>
				<div class="entry-reading-size" role="group" aria-label="<?php esc_attr_e( 'Article text size', 'glaze' ); ?>" data-glaze-reading-size>
					<button type="button" class="squircle" aria-label="<?php esc_attr_e( 'Decrease article text size', 'glaze' ); ?>" data-glaze-tooltip data-glaze-reading-smaller>A−</button>
					<button type="button" class="squircle" aria-label="<?php esc_attr_e( 'Increase article text size', 'glaze' ); ?>" data-glaze-tooltip data-glaze-reading-larger>A+</button>
					<span class="screen-reader-text" aria-live="polite" data-glaze-reading-size-status></span>
				</div>
				<button type="button" class="entry-reading-toggle squircle" aria-label="<?php esc_attr_e( 'Reading focus', 'glaze' ); ?>" aria-pressed="false" data-glaze-tooltip data-glaze-reading-toggle>
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 4H6a2 2 0 0 0-2 2v3m11-5h3a2 2 0 0 1 2 2v3m0 6v3a2 2 0 0 1-2 2h-3m-6 0H6a2 2 0 0 1-2-2v-3"/><circle cx="12" cy="12" r="3"/></svg>
				</button>
			</div>
			<div class="entry-reading-veil" aria-hidden="true" hidden></div>
		<?php endif; ?>
	<?php else : ?>
		<header class="entry-header<?php if ( $archive_stats ) : ?> entry-header-archive<?php endif; ?>">
			<h1 class="entry-title<?php if ( is_page() ) : ?> glaze-page-title<?php endif; ?>"><?php
			if ( is_page() ) {
				$icon     = westnote_glaze_icon_markup( get_post_meta( get_the_ID(), '_glaze_page_icon', true ) );
				$fallback = 'subscriptions' === $collection ? 'feeds' : $collection;
				$fallback = $fallback ?: westnote_glaze_default_menu_icon( home_url( '/' . get_page_uri() ) );
				?>
				<span class="glaze-page-title-icon squircle"><?php echo $icon ?: westnote_glaze_icon_markup( $fallback ?: 'reading' ); ?></span>
				<?php
			}
			?><span><?php echo esc_html( get_the_title() ?: __( 'Untitled', 'glaze' ) ); ?></span><?php
			?></h1>
			<?php if ( $archive_stats ) : ?>
				<?php get_template_part( 'template-parts/collections/archive-stats' ); ?>
			<?php endif; ?>
			<?php if ( 'post' === get_post_type() ) : ?>
				<?php get_template_part( 'template-parts/post-meta' ); ?>
			<?php endif; ?>
		</header>
	<?php endif; ?>

	<?php if ( ! $entry_has_hero && has_post_thumbnail() && ! post_password_required() ) : ?>
		<figure class="entry-cover"><?php the_post_thumbnail( 'large' ); ?></figure>
	<?php endif; ?>

	<div class="entry-content" data-glaze-image-open="<?php esc_attr_e( 'Enlarge image', 'glaze' ); ?>" data-glaze-image-close="<?php esc_attr_e( 'Close image', 'glaze' ); ?>">
		<?php the_content(); ?>
		<?php
		if ( $collection && ! post_password_required() ) {
			get_template_part( 'template-parts/collections/' . $collection );
		}
		?>
		<?php wp_link_pages( array( 'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'glaze' ), 'after' => '</nav>' ) ); ?>
	</div>

	<?php if ( is_singular( 'post' ) && ! post_password_required() ) : ?>
		<?php $tags = get_the_tags(); ?>
		<footer class="entry-footer">
			<div class="entry-footer-categories">
				<?php get_template_part( 'template-parts/post-categories', null, array( 'fallback_icon' => 'categories' ) ); ?>
			</div>
			<?php if ( is_array( $tags ) && $tags ) : ?>
				<div class="post-tags">
					<?php foreach ( $tags as $tag ) : ?>
						<?php $url = get_tag_link( $tag ); ?>
						<?php if ( $url ) : ?>
							<a href="<?php echo esc_url( $url ); ?>" rel="tag">
								<span class="post-tag-hash" aria-hidden="true">#</span><?php echo esc_html( $tag->name ); ?><sup class="post-tag-count"><?php echo esc_html( number_format_i18n( (int) $tag->count ) ); ?></sup>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</footer>
	<?php endif; ?>
	<?php if ( $entry_has_hero ) : ?>
		</div>
	<?php endif; ?>
</article>
