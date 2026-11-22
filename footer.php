<?php
$glaze_last_visit = westnote_glaze_latest_pageview();
$glaze_visit_flag = westnote_glaze_last_visit_flag( $glaze_last_visit );
$glaze_article    = is_singular( 'post' ) && ! is_preview() ? get_queried_object() : null;
?>
<footer class="site-footer">
	<div class="site-footer-inner">
		<div class="site-footer-meta">
			<p class="site-credit">
				&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
			</p>
			<span class="site-online" data-glaze-presence="<?php echo esc_url( rest_url( 'glaze/v1/presence' ) ); ?>"<?php if ( westnote_glaze_public_article( $glaze_article ) ) : ?> data-glaze-post-id="<?php echo esc_attr( $glaze_article->ID ); ?>"<?php endif; ?> lang="zh-CN" hidden>
				<span data-glaze-online-count></span> 人在线
			</span>
			<span class="site-footer-stat" lang="zh-CN">
				<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M2.5 12S6 6 12 6s9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="2.4"/></svg>
				总浏览量 <span data-glaze-views><?php echo esc_html( number_format_i18n( westnote_glaze_total_pageviews() ) ); ?></span>
			</span>
			<span class="site-footer-stat" lang="zh-CN">
				<svg class="site-visit-pin" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" aria-hidden="true"><path d="M19 10c0 5-7 11-7 11s-7-6-7-11a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.25"/></svg>
				<img data-glaze-last-visit-flag width="14" height="14" alt="" referrerpolicy="no-referrer" decoding="async"<?php if ( $glaze_visit_flag ) : ?> src="<?php echo esc_url( $glaze_visit_flag ); ?>"<?php else : ?> hidden<?php endif; ?>>
				最近来访 <span data-glaze-last-visit><?php echo esc_html( westnote_glaze_last_visit_label( $glaze_last_visit ) ); ?></span>
			</span>
			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<nav class="footer-navigation" aria-label="<?php esc_attr_e( 'Footer navigation', 'glaze' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'menu_class'     => 'site-menu',
							'container'      => false,
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>
		</div>
		<div class="footer-controls">
			<button type="button" class="footer-control squircle" data-glaze-rss-copy data-glaze-tooltip
				data-glaze-copy-link="<?php echo esc_url( get_feed_link( 'rss2' ) ); ?>"
				data-copy-success="<?php esc_attr_e( 'RSS link copied', 'glaze' ); ?>"
				data-copy-error="<?php esc_attr_e( 'Could not copy the RSS link.', 'glaze' ); ?>"
				aria-label="<?php esc_attr_e( 'Copy RSS link', 'glaze' ); ?>" hidden>
				<?php echo westnote_glaze_font_awesome_icon( 'rss', 'footer-rss-icon' ); ?>
				<?php echo westnote_glaze_font_awesome_icon( 'check', 'footer-rss-check' ); ?>
			</button>
			<?php get_template_part( 'template-parts/footer-links' ); ?>
			<?php get_template_part( 'template-parts/color-mode' ); ?>
		</div>
	</div>
</footer>
<div id="site-bottom">
	<a class="site-footer-watermark" href="https://wordpress.org/" aria-label="WordPress">
		<?php echo westnote_glaze_font_awesome_icon( 'wordpress' ); ?>
	</a>
</div>
<div class="glaze-page-loading" data-glaze-page-loading hidden>
	<div class="glaze-page-loading-status" role="status">
		<span class="glaze-page-loading-spinner" aria-hidden="true"></span>
		<?php esc_html_e( 'Loading page…', 'glaze' ); ?>
	</div>
</div>
<div class="glaze-toast-stack" data-glaze-toast-stack data-dismiss-label="<?php esc_attr_e( 'Dismiss notification', 'glaze' ); ?>"></div>
<div class="screen-reader-text" role="status" aria-live="polite" aria-atomic="true" data-glaze-toast-status></div>
<?php wp_footer(); ?>
</body>
</html>
