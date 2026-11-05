<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2026 Printess GmbH & Co. KG
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * Modification is permitted under the terms above. Printess GmbH & Co. KG
 * supports only the official releases of this plugin and does not provide
 * support for modified copies.
 *
 * @package PrintessEditor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'PrintessDialogs', false ) ) {
	return;
}


/**
 * The design name and save reminder dialogs of the product page.
 */
class PrintessDialogs {
	/**
	 * Prints the dialog that asks for a design name.
	 */
	public static function render_display_name_dialog() {
		?>
		<div class="printess_overlay_background printess-owned" id="printess_display_name_overlay_background" style="display:none;">
			<div class="printess_overlay">
				<div class="printess_overlay_content">
					<div class="header">
          	<span class="title"><?php echo '<span class="highlight">' . esc_html__( 'Please provide a design name', 'printess-editor' ) . '</span>'; ?></span>
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

	/**
	 * Prints the dialog that reminds the shopper to save the design.
	 *
	 * @param mixed $number_of_minutes Minutes after which the reminder appears.
	 */
	public static function render_save_reminder_dialog( $number_of_minutes ) {
		$number_of_minutes = null === $number_of_minutes ? '' : '' . $number_of_minutes;

		?>
		<div class="printess_overlay_background printess-owned" id="printess_save_reminder_overlay_background" style="display:none;">
			<div class="printess_overlay">
				<div class="printess_overlay_content">
					<div class="header">
          	<span class="title"><?php echo '<span class="highlight">' . esc_html__( 'Reminder to save your work', 'printess-editor' ) . '</span>'; ?></span>
					</div>

					<form class="woocommerce-form">
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" id="printess_save_reminder_caption">
							<?php echo esc_html( str_replace( '{MINUTES}', $number_of_minutes, __( 'You did not save your work within the last {MINUTES} minutes. Do you want to save your changes now?', 'printess-editor' ) ) ); ?>
						</p>
					</form>
				</div>

				<div class="printess_overlay_footer">
					<span></span>
					<button id="printess_save_reminder_ok_button" class="button alt wp-element-button"><?php echo esc_html__( 'Save', 'printess-editor' ); ?></button>
					<button id="printess_save_reminder_cancel_button" class="button wp-element-button"><?php echo esc_html__( 'Cancel', 'printess-editor' ); ?></button>
				</div>
			</div>
		</div>
	<?php
	}
}