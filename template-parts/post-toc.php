<button class="post-toc-trigger control-button squircle" type="button" aria-controls="glaze-post-toc" aria-haspopup="dialog" aria-expanded="false" data-glaze-toc-open hidden>
	<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" aria-hidden="true"><path d="M10 6h10M10 12h10M10 18h10M4 6h1M4 12h1M4 18h1"/></svg>
	<?php esc_html_e( 'Contents', 'glaze' ); ?>
</button>
<dialog id="glaze-post-toc" class="post-toc-dialog squircle" aria-labelledby="glaze-post-toc-title">
	<div class="post-toc-inner">
		<div class="post-toc-heading">
			<h2 id="glaze-post-toc-title"><?php esc_html_e( 'Article contents', 'glaze' ); ?></h2>
			<form method="dialog">
				<button class="post-toc-close control-button" aria-label="<?php esc_attr_e( 'Close table of contents', 'glaze' ); ?>" autofocus>
					<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" aria-hidden="true"><path d="m5 5 14 14M19 5 5 19"/></svg>
				</button>
			</form>
		</div>
		<ol class="post-toc-list"></ol>
	</div>
</dialog>
