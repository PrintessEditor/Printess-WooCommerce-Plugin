<?php

if ( class_exists( 'PrintessDialogs', false ) ) return;


class PrintessDialogs {
    public static function render_display_name_dialog() {
	?>
		<div class="printess_overlay_background printess-owned" id="printess_display_name_overlay_background" style="display:none;">
			<div class="printess_overlay">
				<div class="printess_overlay_content">
					<div class="header">
          	<span class="title"><?php echo '<span class="highlight">' . esc_html__( 'Please provide a design name', 'printess-editor' ) . '</span>' ?></span>
					</div>

					<form class="woocommerce-form">
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="printess_display_name_edit"><?php echo esc_html__( 'Design name', 'printess-editor' ); ?></label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="printess_display_name_edit" id="printess_display_name_edit" value="" placeholder="<?php echo esc_html__( 'Enter the design name', 'printess-editor' ); ?>">
						</p>
					</form>
				</div>

				<div class="printess_overlay_footer">
					<span></span>
					<button id="printess_display_name_ok_button" class="button alt wp-element-button"><?php echo esc_html__( 'Ok', 'printess-editor' ); ?></button>
					<button id="printess_display_name_cancel_button" class="button wp-element-button"><?php echo esc_html__( 'Cancel', 'printess-editor' ); ?></button>
				</div>
			</div>
		</div>
	<?php
    }
}