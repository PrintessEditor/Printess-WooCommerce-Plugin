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

use Automattic\WooCommerce\Blocks\StoreApi\Schemas\CartItemSchema;

/**
 * The Printess data of a cart line for the Store API (cart and checkout blocks).
 *
 * @param array $cart_item The cart item.
 * @return array Save token, thumbnail, date added, design name and id, edit link.
 */
function printess_on_add_printess_cart_item_data( $cart_item ) {
	if ( array_key_exists( 'printess-save-token', $cart_item ) && null !== $cart_item['printess-save-token'] && ! empty( $cart_item['printess-save-token'] ) ) {
		$product             = wc_get_product( $cart_item['variation_id'] > 0 ? $cart_item['variation_id'] : $cart_item['product_id'] );
		$printess_save_token = $cart_item['printess-save-token'];
		$edit_link           = '';

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core hooks
		if ( $product && $product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item['key'] ) ) {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core hook
			$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink( $cart_item ) : '', $cart_item, $cart_item['key'] );
			$edit_link         = add_query_arg( 'printess-save-token', $printess_save_token, $product_permalink );

			if ( array_key_exists( 'key', $cart_item ) ) {
				$id = $cart_item['key'];

				if ( null !== $id && ! empty( $id ) ) {
					$edit_link = add_query_arg( 'printess_item_key', $id, $edit_link );
				}
			}

			if ( array_key_exists( 'printess-display-name', $cart_item ) ) {
				$edit_link = add_query_arg( 'display_name', $cart_item['printess-display-name'], $edit_link );
			}

			if ( array_key_exists( 'printess-design-name', $cart_item ) ) {
				$edit_link = add_query_arg( 'design_name', $cart_item['printess-design-name'], $edit_link );
			}
		}

		$ret = array(
			'saveToken'    => array_key_exists( 'printess-save-token', $cart_item ) ? $cart_item['printess-save-token'] : '',
			'thumbnailUrl' => array_key_exists( 'printess-thumbnail-url', $cart_item ) ? $cart_item['printess-thumbnail-url'] : '',
			'dateAdded'    => array_key_exists( 'printess_date_added', $cart_item ) ? $cart_item['printess_date_added'] : '',
			'designName'   => array_key_exists( 'printess-design-name', $cart_item ) ? $cart_item['printess-design-name'] : '',
			'designId'     => array_key_exists( 'printess-design-id', $cart_item ) ? $cart_item['printess-design-id'] : '',
			'editLink'     => $edit_link,
		);

		return $ret;
	}

	return array();
}

/**
 * Registers the Store API extension "printess-editor" for cart items.
 */
function printess_register_block_hooks() {

	woocommerce_store_api_register_endpoint_data(
		array(
			'endpoint'      => CartItemSchema::IDENTIFIER,
			'namespace'     => 'printess-editor',
			'data_callback' => function ( $cart_item ) {
				return printess_on_add_printess_cart_item_data( $cart_item ); },
		)
	);
}
