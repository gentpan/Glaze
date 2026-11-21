<?php
$active_palette = westnote_glaze_get_palette();
$active_scheme  = westnote_glaze_get_scheme();
?>
<details class="palette-picker" data-glaze-palette-picker hidden>
	<summary class="footer-control squircle" data-glaze-tooltip aria-label="<?php esc_attr_e( 'Theme palette', 'glaze' ); ?>" aria-controls="glaze-palette-panel">
		<span class="palette-dot" aria-hidden="true"></span>
	</summary>
	<div class="palette-panel glaze-panel t-dropdown" id="glaze-palette-panel" data-origin="bottom-right">
		<div class="palette-grid squircle" role="group" aria-label="<?php esc_attr_e( 'Theme palette', 'glaze' ); ?>">
			<?php foreach ( westnote_glaze_palettes() as $value => $label ) : ?>
				<div class="palette-option">
					<button type="button" class="palette-swatch-button squircle" data-glaze-swatch="<?php echo esc_attr( $value ); ?>" aria-label="<?php echo esc_attr( $label ); ?>" aria-pressed="<?php echo $value === $active_palette ? 'true' : 'false'; ?>">
						<span class="palette-swatch squircle" aria-hidden="true"></span>
					</button>
					<span aria-hidden="true"><?php echo esc_html( $label ); ?></span>
				</div>
			<?php endforeach; ?>
			<div class="palette-option">
				<button type="button" class="palette-swatch-button palette-scheme-toggle squircle" data-glaze-theme-toggle data-glaze-tooltip="<?php esc_attr_e( 'Switch light and dark appearance', 'glaze' ); ?>" aria-label="<?php esc_attr_e( 'Dark appearance', 'glaze' ); ?>" aria-pressed="<?php echo 'dark' === $active_scheme ? 'true' : 'false'; ?>">
					<span class="palette-swatch palette-scheme-swatch squircle" aria-hidden="true">
						<svg class="theme-icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.4 1.4m11.2 11.2L19 19M5 19l1.4-1.4M17.6 6.4 19 5"/></svg>
						<svg class="theme-icon-moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"><path d="M21 15.1A9 9 0 0 1 8.9 3a9.15 9.15 0 1 0 12.1 12.1Z"/></svg>
					</span>
				</button>
				<span aria-hidden="true"><?php esc_html_e( 'Dark', 'glaze' ); ?></span>
			</div>
		</div>
	</div>
</details>
