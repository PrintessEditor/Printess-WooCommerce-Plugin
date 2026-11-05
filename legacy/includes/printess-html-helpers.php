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

if ( class_exists( 'PrintessHtmlHelpers', false ) ) {
	return;
}

/**
 * Small HTML rendering helpers for the admin screens.
 */
class PrintessHtmlHelpers {
	/**
	 * Renders a select with option group support that is used for the drop shipping confgiuration
	 *
	 * @param mixed  $html_id The id of the new html element.
	 * @param string $title The title of the dropshipping option.
	 * @param mixed  $dropshippers A list of all dropshippers together with their dropship product definitions.
	 * @param mixed  $selected_dropshipping_id The preselected dropshipping product definition id.
	 * @param mixed  $has_do_not_use_override Decides if the option for the dropü shipping override should be ignored.
	 */
	public static function render_select_with_option_groups( $html_id, $title, &$dropshippers, $selected_dropshipping_id, $has_do_not_use_override = false ) {
		$selected_title = '';

		if ( intval( $selected_dropshipping_id ) === -2 ) {
			$selected_title = __( 'Do not use dropshipping override', 'printess-editor' );
		} elseif ( intval( $selected_dropshipping_id ) === -1 ) {
			$selected_title = __( 'No dropshipping', 'printess-editor' );
		} elseif ( intval( $selected_dropshipping_id ) === 0 ) {
			$selected_title = __( 'Use template settings', 'printess-editor' );
		} elseif ( $selected_dropshipping_id > 0 ) {
			foreach ( $dropshippers as &$dropshipper ) {
				if ( ! isset( $dropshipper['productDefinitions'] ) || empty( $dropshipper['productDefinitions'] ) ) {
					continue;
				}

				foreach ( $dropshipper['productDefinitions'] as &$option ) {
					if ( intval( $option['id'] ) === intval( $selected_dropshipping_id ) ) {
						$selected_title = $option['display'];
						break;
					}
				}

				if ( '' !== $selected_title ) {
					break;
				}
			}
		}

		if ( ! $selected_title ) {
			$selected_dropshipping_id = -1;
			$selected_title           = __( 'No dropshipping', 'printess-editor' );
		}

		?>
            <p class="form-field <?php echo esc_attr( $html_id ); ?>-dropdown" id="<?php echo esc_attr( $html_id ); ?>_field" data-priority="">
        <label for="<?php echo esc_attr( $html_id ); ?>" class=""><?php echo esc_html( $title ); ?></label>
        <span class="woocommerce-input-wrapper">
        <select name="<?php echo esc_attr( $html_id ); ?>" id="<?php echo esc_attr( $html_id ); ?>" class="select " data-placeholder="" autocomplete="<?php echo esc_attr( $html_id ); ?>">
        <option value="<?php echo esc_attr( $selected_dropshipping_id ); ?>"><?php echo esc_html( $selected_title ); ?></option>

        <optgroup label="<?php echo esc_html__( 'Defaults', 'printess-editor' ); ?>">;
            <?php
			if ( true === $has_do_not_use_override ) {
				?>
                <option value="-2"><?php echo esc_html__( 'Do not use dropshipping override', 'printess-editor' ); ?></option>
                <?php
			}
			?>

            <option value="-1"><?php echo esc_html__( 'No dropshipping', 'printess-editor' ); ?></option>
            <option value="0"><?php echo esc_html__( 'Use template settings', 'printess-editor' ); ?></option>
        </optgroup>

        <?php
		foreach ( $dropshippers as &$optgroup_options ) {
			?>
            <optgroup label="<?php echo esc_html( $optgroup_options['display'] ); ?>">;
            <?php

			if ( array_key_exists( 'productDefinitions', $optgroup_options ) ) {
				foreach ( $optgroup_options['productDefinitions'] as $option ) {
					$selected = ( $selected_dropshipping_id ) === intval( $option['id'] ) ? ' selected="selected"' : '';
					?>
                    <option value="<?php echo esc_attr( $option['id'] ); ?>" <?php echo $selected; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>><?php echo esc_html( $option['display'] ); ?></option>
                    <?php
				}
			}
			?>
            
            </optgroup>
            <?php
		}
		?>

        </select></span></p>

        <?php
	}
}


?>