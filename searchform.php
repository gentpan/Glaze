<?php
$search_id   = wp_unique_id( 'glaze-search-' );
$search_name = $args['aria_label'] ?? __( 'Search the site', 'glaze' );
$is_header   = ! empty( $args['glaze_header'] );
$placeholder = $is_header ? __( 'Enter an article keyword', 'glaze' ) : __( 'Search posts…', 'glaze' );
?>
<form role="search" method="get" class="search-form<?php echo $is_header ? ' header-search-form' : ''; ?>" action="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $search_name ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $search_id ); ?>"><?php esc_html_e( 'Search query', 'glaze' ); ?></label>
	<?php if ( $is_header ) : ?><div class="header-search-input"><?php endif; ?>
		<input id="<?php echo esc_attr( $search_id ); ?>" class="search-field" type="search" name="s" value="<?php echo get_search_query(); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" required enterkeyhint="search">
	<?php if ( $is_header ) : ?>
		<kbd class="header-search-key squircle" aria-hidden="true" data-glaze-search-key hidden></kbd>
	</div>
	<?php else : ?>
		<button class="search-submit control-button" type="submit"><?php esc_html_e( 'Search', 'glaze' ); ?></button>
	<?php endif; ?>
</form>
