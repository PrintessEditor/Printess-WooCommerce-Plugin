<?php
/**
 * Plugin Name: Printess Editor
 * Description: Personalize anything! Friendship mugs, t-shirts, greeting cards. Limitless possibilities.
 * Plugin URI: https://printess.com/kb/integrations/woo-commerce/index.html
 * Developer: Bastian Kröger (support@printess.com); Alexander Oser (support@printess.com)
 * Version: 1.6.47
 * Author: Printess
 * Author URI: https://printess.com
 * Text Domain: printess-editor
 * Domain Path: /languages
 * Requires at least: 5.9
 * Requires PHP: 8.1
 * Tested up to: 6.8
 *
 * Woo: 10000:924006dfsfhsf8429842385wdff234sfd
 * WC requires at least: 5.8
 * WC tested up to: 9.8.2
 */

include_once("includes/printess-admin-settings.php");
include_once("includes/printess-api.php");
include_once("includes/printess-html-helpers.php");
include_once("includes/printess-product-helpers.php");

$printess_global_plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';

/**
 * Renders the status overlay that is displayed
 */
function printess_render_information_overlay() {
	?>
	<div class="printess_overlay_background" id="printess_information_overlay_background" style="display:none;">
		<div class="printess_overlay printess_information_overlay">
			<div class="printess_overlay_content progress">
				<p id="printess_information_overlay_text"><p>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Renders the dialog that pops up when klicking on save inside the printess editor
 */
function printess_render_save_dialog() {
	?>
		<div class="printess_overlay_background printess-owned" id="printess_overlay_background" style="display:none;">
			<div class="printess_overlay">
				<div class="printess_overlay_content">
					<div class="header">
						<span class="title"><?php echo esc_html__( 'Saving your design', 'printess-editor' ); ?></span>
					</div>

					<p class="printess_show_if_not_logged_in" id="printess_show_if_not_logged_in"><?php echo str_replace( '{SAVE_DESIGN}', '<span class="highlight">' . esc_html__( 'Save design', 'printess-editor' ) . '</span>', esc_html__( 'You are currently not logged in. To be able to save designs you need to be logged in. You will be redirected to the login page after clicking on {SAVE_DESIGN}. After logging in / account creation you will be redirected back to your current design so that you can continue working on your current design.', 'printess-editor' ) ); ?></p>

					<div class="printess_show_if_no_design_name" id="printess_show_if_no_design_name">
						<p><?php echo str_replace( '{SAVE_DESIGN}', '<span class="highlight">' . esc_html__( 'Saved designs', 'printess-editor' ) . '</span>', esc_html__( 'You need to provide a display name for your saved design. This name is shown on the {SAVE_DESIGN} page so that you can easily find it on future visits.', 'printess-editor' ) ); ?></p>
					</div>

					<div class="printess_show_if_design_name" id="printess_show_if_design_name">
						<p><?php echo str_replace( '{SAVE_DESIGN}', '<span class="highlight">' . esc_html__( 'Saved designs', 'printess-editor' ) . '</span>', esc_html__( 'You can provide a new design name to store this version under a different name. This new version can then be found on your {SAVE_DESIGN} page as well.', 'printess-editor' ) ); ?></p>
					</div>

					<form class="woocommerce-form">
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="printess_designnameedit"><?php echo esc_html__( 'Design name', 'printess-editor' ); ?></label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="printess_designnameedit" id="printess_designnameedit" value="" placeholder="<?php echo esc_html__( 'Enter your display name', 'printess-editor' ); ?>">
						</p>
					</form>


					<p><?php echo str_replace( '{SAVE_DESIGN}', '<span class="highlight">' . esc_html__( 'Saved designs', 'printess-editor' ) . '</span>', esc_html__( 'After saving, your saved design can be found under {SAVE_DESIGN} on your account page.', 'printess-editor' ) ); ?></p>
				</div>

				<div class="printess_overlay_footer">
					<span></span>
					<button id="printess_save_design_button" class="button alt wp-element-button"><?php echo esc_html__( 'Save design', 'printess-editor' ); ?></button>
					<button id="printess_cancel_button" class="button wp-element-button"><?php echo esc_html__( 'Cancel', 'printess-editor' ); ?></button>
				</div>
			</div>
		</div>
	<?php
}

/**
 * Adds the necessary Printess values to the order line items.
 *
 * @param mixed  $item          The order line item.
 * @param string $cart_item_key The cart item key.
 * @param mixed  $values        The values of the cart item.
 */
function printess_add_save_token_to_order_items( $item, $cart_item_key, $values ) {
	if ( empty( $values['printess-save-token'] ) ) {
		return;
	}

	$item->add_meta_data( '_printess-save-token', $values['printess-save-token'], true );

	if ( ! empty( $values['printess-thumbnail-url'] ) ) {
		$item->add_meta_data( '_printess-thumbnail-url', $values['printess-thumbnail-url'], true );
	}

	if ( ! empty( $values['printess-design-id'] ) ) {
		$item->add_meta_data( '_printess-design-id', $values['printess-design-id'], true );
	}

	if ( ! empty( $values['printess-design-name'] ) ) {
		$item->add_meta_data( '_printess-design-name', $values['printess-design-name'], true );
	}

	if ( ! empty( $values['printess-additional-settings'] ) ) {
		$item->add_meta_data( 'printess-additional-settings', $values['printess-additional-settings'], true );
	}

	$product = wc_get_product( $values['product_id'] );

	if ( isset( $product ) ) {
		$item->add_meta_data( '_printess-dropshipping', $product->get_meta( 'printess_dropshipping' ), true );
	}

	$time_out = printess_create_new_unexpiration_date( true );

	$item->add_meta_data( '_printess-valid-until', $time_out->format( 'Y-m-d H:i:s' ), true );
}

/**
 * Adds the Printess specific values to the cart items.
 *
 * @param mixed $cart_item_data The cart item data.
 * @return mixed the modified cart item data
 */
function printess_add_cart_item_data( $cart_item_data ) {
	$save_token                     = filter_input( INPUT_POST, 'printess-save-token', FILTER_SANITIZE_SPECIAL_CHARS );
	$save_token_to_remove_from_cart = filter_input( INPUT_POST, 'printess-save-token-to-remove-from-cart', FILTER_SANITIZE_SPECIAL_CHARS );
	$thumbnail_url                  = filter_input( INPUT_POST, 'printess-thumbnail-url', FILTER_VALIDATE_URL );
	$design_id                      = filter_input( INPUT_POST, 'printess-design-id', FILTER_SANITIZE_SPECIAL_CHARS );
	$design_name                    = filter_input( INPUT_POST, 'printess-design-name', FILTER_SANITIZE_SPECIAL_CHARS );
	$additionalSettings             = filter_input( INPUT_POST, 'printess-additional-settings', FILTER_SANITIZE_SPECIAL_CHARS );
	$item_usage						= filter_input( INPUT_POST, 'printess_item_usage', FILTER_UNSAFE_RAW );
	$remove_items_from_cart    = false;
	$remove_option_value       = get_option( 'printess_show_original_product_in_basket', true );
	$remove_basket_item_on_same_design_name = PrintessAdminSettings::get_delete_original_basket_item_only_on_same_design_name();

	if ( isset( $remove_option_value ) && ( empty( $remove_option_value ) || 'off' === $remove_option_value ) ) {
		$remove_items_from_cart = true;
	}

	if ( empty( $save_token ) || strlen( $save_token ) !== 89 ) {
		return $cart_item_data;
	}

	//In case we have a save token to delete, remove it from the basket
	if(null !== $save_token_to_remove_from_cart && !empty($save_token_to_remove_from_cart)) {
		//Get all cart items and check the ones with save tokens if it can be found
		$cart_contents = $items = WC()->cart->get_cart();

		foreach($cart_contents as $item_key => &$item) {
			if(array_key_exists("printess-save-token", $item)) {
				$item_save_token = $item["printess-save-token"];
				$item_design_name = $item["printess-design-name"];

				if($item_save_token === $save_token_to_remove_from_cart) {
					// $items_to_remove_from_cart = WC()->cart->find_product_in_cart( $cart_item_id );

					if(null !== $design_name && !empty($design_name) && null !== $item_design_name && !empty($item_design_name) && $design_name === $item_design_name) {
						if(true === $remove_basket_item_on_same_design_name) {
							WC()->cart->remove_cart_item( $item_key );
						}
					} else {
						if(true === $remove_items_from_cart) {
							WC()->cart->remove_cart_item( $item_key );
						} else {
							$item['printess-was-edited'] = 1;
							$cart_item_data['printess-was-edited'] = 1;
						}
					}

					WC()->cart->set_session();

					break;
				}
			}
		}
	}


	$cart_item_data['printess-save-token'] = $save_token;

	if ( ! empty( $save_token_to_remove_from_cart ) && strlen( $save_token_to_remove_from_cart ) === 89 ) {
		$cart_item_data['printess-save-token-to-remove-from-cart'] = $save_token_to_remove_from_cart;
	}

	if ( ! empty( $thumbnail_url ) ) {
		$cart_item_data['printess-thumbnail-url'] = $thumbnail_url;
	}

	if ( ! empty( $additionalSettings ) ) {
		$cart_item_data['printess-additional-settings'] = $additionalSettings;
	}

	if ( ! empty( $design_id ) ) {
		$cart_item_data['printess-design-id'] = $design_id;
	}

	if ( ! empty( $design_name ) ) {
		$cart_item_data['printess-design-name'] = $design_name;
	}

	if ( ! empty( $item_usage ) ) {
		$cart_item_data['printess-item-usage'] = $item_usage;
	}

	$cart_item_data['printess_date_added'] = ( new DateTime() )->format( 'Y-m-d H:i:s' );

	return $cart_item_data;
}

/**
 * Changes the cart item thumbnail to use the one from the customized Printess product.
 *
 * @param mixed $item_data The cart item data.
 * @param mixed $cart_item The cart item.
 */
function printess_get_thumbnail( $item_data, $cart_item ) {
	if ( ! empty( $cart_item['printess-thumbnail-url'] ) ) {
		return '<img src="' . $cart_item['printess-thumbnail-url'] . '" style="background-color: #ffffff;" />';
	}

	return $item_data;
}

/**
 * Returns the current price format options as json string
 */
function printess_get_price_format_options() {
	return array(
		"decimalSeperator" => wc_get_price_decimal_separator(),
		"thousandsSeperator" => wc_get_price_thousand_separator(),
		"decimals" => wc_get_price_decimals(),
		"priceFormat" => get_woocommerce_price_format(),
		"currencySymbol" => html_entity_decode(get_woocommerce_currency_symbol()),
		"currencySymbolOnLeftSide" => get_option( 'woocommerce_currency_pos' ) === "left"
	);
}

/**
 * Returns an array containing all required product details
 *
 * @param mixed $product The product db object the information is pulled from.
 */
function printess_get_product_json( $product ) {
	$parent_id = $product->get_data()["parent_id"];#

	if($parent_id > 0) {
		$product = wc_get_product( $parent_id );
	}

	$js_product = array(
		'id'             => $product->get_id(),
		'name'           => $product->get_name(),
		'templateName'   => $product->get_meta( 'printess_template', true ),
		'mergeTemplates' => array(),
		'attributes'     => array(),
		'attributesLookup' => array(),
		'variants'       => array(),
		'regular_price'  => $product->get_regular_price(),
		'sale_price'     => $product->get_sale_price(),
		'price'          => $product->get_price(),
		'available'      => false === $product->get_data()['manage_stock'] || $product->get_stock_quantity() > 0
	);

	$js_product['mergeTemplates'] = array_filter(
		array( $product->get_meta( 'printess_merge_template_1', true ), $product->get_meta( 'printess_merge_template_2', true ), $product->get_meta( 'printess_merge_template_3', true ) ),
		function ( $template ) {
			return ! empty( $template );
		}
	);

	$js_product["attributes"] = PrintessProductHelpers::get_product_attributes($product);

	$product_variation_ids = $product->get_children();

	if ( isset( $product_variation_ids ) && count( $product_variation_ids ) > 0 ) {
		foreach ( $product_variation_ids as $id ) {
			$variation  = wc_get_product( $id );
			$js_variant = array(
				'attributes'   => array(),
				'id'           => $id,
				'templateName' => $variation->get_meta( 'printess_template_name', true ),
				'parentId'     => $variation->get_data()['parent_id'],
				'regularPrice' => $variation->get_regular_price(),
				'salePrice'    => $variation->get_sale_price(),
				'price'        => $variation->get_price(),
				'available'    => false === $variation->get_data()['manage_stock'] || $variation->get_stock_quantity() > 0,
			);

			foreach ( $variation->get_attributes() as $key => $value ) {
				$js_variant['attributes'][ $key ] = $value;
			}

			$js_product['variants'][] = $js_variant;
		}
	}

	if (method_exists( $product, 'get_meta' ) ) {
		$redirect_page = $product->get_meta( 'printess_cart_redirect_page', true );

		if ( null !== $redirect_page && '' !== $redirect_page ) {
			$pages = printess_get_available_pages( true );

			if ( array_key_exists( $redirect_page, $pages ) ) {
				$result = get_permalink( $pages[ $redirect_page ] );

				if ( false === $result ) {
					$js_product["redirectUrl"] = site_url( $redirect_page );
				} else {
					$js_product["redirectUrl"] = $result;
				}
			} else {
				$js_product["redirectUrl"] = site_url( $redirect_page );
			}
		}
	}

	$js_product["ajaxEnabled"] = "yes" === get_option( 'woocommerce_enable_ajax_add_to_cart', '' ) || "on" === get_option( 'woocommerce_enable_ajax_add_to_cart', '' );

	return $js_product;
}

/**
 * Renders a list of key value pairs that should be pulled in as form fields automatically
 *
 * @param mixed  $user_id The user id of the current logged in user
 */
function printess_get_custom_formfields($user_id) {
	$form_fields = array();
	$filter_lookup = array();
	$user_field_filter = PrintessAdminSettings::get_user_fields();

	if(null !== $user_field_filter && !empty($user_field_filter)) {
		foreach(explode(",", str_replace("\r", ",", str_replace("\n", ",", str_replace("\r\n", ",", $user_field_filter)))) as $value) {//, seperator or new line supported
			$value = strtolower(trim($value));

			if(!empty($value)) {
				$filter_lookup[] = $value;
			}
		}
	}

	//add user details
	if($user_id > 0) {
		$current_user = wp_get_current_user();

		$customer = new WC_Customer( $user_id );

		if(in_array("all", $filter_lookup) || in_array("email", $filter_lookup))$form_fields["eMail"] = $customer->email;
		if(in_array("all", $filter_lookup) || in_array("displayname", $filter_lookup))$form_fields["Displayname"] = $customer->display_name;
		if(in_array("all", $filter_lookup) || in_array("firstname", $filter_lookup))$form_fields["Firstname"] = $customer->__get('first_name');
		if(in_array("all", $filter_lookup) || in_array("lastname", $filter_lookup))$form_fields["Lastname"] = $customer->__get('last_name');
		if(in_array("all", $filter_lookup) || in_array("description", $filter_lookup))$form_fields["Description"] = get_user_meta($user_id, 'description', true);




		if(in_array("all", $filter_lookup) || in_array("billingfirstname", $filter_lookup))$form_fields["BillingFirstname"] = $customer->billing["first_name"];
		if(in_array("all", $filter_lookup) || in_array("billinglastname", $filter_lookup))$form_fields["BillingLastname"] = $customer->billing["last_name"];
		if(in_array("all", $filter_lookup) || in_array("billingcompany", $filter_lookup))$form_fields["BillingCompany"] = $customer->billing["company"];
		if(in_array("all", $filter_lookup) || in_array("billingemail", $filter_lookup))$form_fields["BillingEmail"] = $customer->billing["email"];
		if(in_array("all", $filter_lookup) || in_array("billingphone", $filter_lookup))$form_fields["BillingPhone"] = $customer->billing["phone"];
		if(in_array("all", $filter_lookup) || in_array("billingcity", $filter_lookup))$form_fields["BillingCity"] = $customer->billing["city"];
		if(in_array("all", $filter_lookup) || in_array("billingstate", $filter_lookup))$form_fields["BillingState"] = $customer->billing["state"];
		if(in_array("all", $filter_lookup) || in_array("billingpostcode", $filter_lookup))$form_fields["BillingPostcode"] = $customer->billing["postcode"];
		if(in_array("all", $filter_lookup) || in_array("billingcountry", $filter_lookup))$form_fields["BillingCountry"] = $customer->billing["country"];
		if(in_array("all", $filter_lookup) || in_array("billingaddress1", $filter_lookup))$form_fields["BillingAddress1"] = $customer->billing["address_1"];
		if(in_array("all", $filter_lookup) || in_array("billingaddress2", $filter_lookup))$form_fields["BillingAddress2"] = $customer->billing["address_2"];

		if(in_array("all", $filter_lookup) || in_array("shippingfirstname", $filter_lookup))$form_fields["ShippingFirstname"] = $customer->shipping["first_name"];
		if(in_array("all", $filter_lookup) || in_array("shippinglastname", $filter_lookup))$form_fields["ShippingLastname"] = $customer->shipping["last_name"];
		if(in_array("all", $filter_lookup) || in_array("shippingcompany", $filter_lookup))$form_fields["ShippingCompany"] = $customer->shipping["company"];
		if(in_array("all", $filter_lookup) || in_array("shippingphone", $filter_lookup))$form_fields["ShippingPhone"] = $customer->shipping["phone"];
		if(in_array("all", $filter_lookup) || in_array("shippingcity", $filter_lookup))$form_fields["ShippingCity"] = $customer->shipping["city"];
		if(in_array("all", $filter_lookup) || in_array("shippingstate", $filter_lookup))$form_fields["ShippingState"] = $customer->shipping["state"];
		if(in_array("all", $filter_lookup) || in_array("shippingpostcode", $filter_lookup))$form_fields["ShippingPostcode"] = $customer->shipping["postcode"];
		if(in_array("all", $filter_lookup) || in_array("shippingcountry", $filter_lookup))$form_fields["ShippingCountry"] = $customer->shipping["country"];
		if(in_array("all", $filter_lookup) || in_array("shippingaddress1", $filter_lookup))$form_fields["ShippingAddress1"] = $customer->shipping["address_1"];
		if(in_array("all", $filter_lookup) || in_array("shippingaddress2", $filter_lookup))$form_fields["ShippingAddress2"] = $customer->shipping["address_2"];
	}

	//add acf field values
	if($user_id > 0 && function_exists("get_field_objects")) {
		$acf_fields = get_field_objects("user_" . $user_id);

		if(null !== $acf_fields && false !== $acf_fields && is_array($acf_fields)) {
			foreach($acf_fields as $key => &$field) {
				if(is_array($field) && array_key_exists("label", $field) && array_key_exists("value", $field) && (in_array("all", $filter_lookup) || in_array(strtolower($field["label"]), $filter_lookup))) {
					$form_fields[$field["label"]] = $field["value"];
				}
			}
		}
	}

	return $form_fields;
}

/**
 * Renders all the required html + javascript required for the editor integration inside the product page
 *
 * @param mixed  $product The product for which the editor should be used.
 * @param string $mode Indicator if this method was called from within the admin pages or from the product page (Buyer = Product page).
 */
function printess_render_editor_integration( $product, $mode = 'buyer' ) {
	$printess_attribute = '';
	$cart_id = WC()->session->get( 'printess-cart-id' );
	$attachParams = array();
	$additionalAttachParams = array();
	$theme = PrintessAdminSettings::get_default_theme();
	$printess_helpers = new PrintessProductHelpers($product->get_id());
	$page_count_formField = $printess_helpers->get_page_count_option();


	if ( empty( $cart_id ) ) {
		$cart_id = uniqid( '', true );

		WC()->session->set( 'printess-cart-id', $cart_id);
	}

	wp_enqueue_style( 'printess-editor' );

	if ( isset( $product ) ) {
		$printess_attribute = $product->get_meta( 'printess_template', true );

		$productTheme = $product->get_meta( 'printess_editor_theme', true );

		if(null !== $productTheme && !empty($productTheme)) {
			$theme = $productTheme;
		}
	}

	$attachParams["basketThumbnailMaxWidth"] = PrintessAdminSettings::get_thumbnail_width();
	$attachParams["basketThumbnailMaxHeight"] = PrintessAdminSettings::get_thumbnail_height();

	if(!empty($page_count_formField)) {
		$additionalAttachParams["pageCountFormField"] = $page_count_formField;
	}

	if ( ! empty( $printess_attribute ) ) {
		$prod_id     = $product->get_id();
		$account_url = get_permalink( wc_get_page_id( 'myaccount' ) ); // Account page which will force login on open.
		$account_url = add_query_arg( 'productId', '__ProductId__', $account_url );
		$account_url = add_query_arg( 'saveToken', '__SaveToken__', $account_url );
		$account_url = add_query_arg( 'thumbnailUrl', '__ThumbnailUrl__', $account_url );
		$account_url = add_query_arg( 'variantId', '__VariantId__', $account_url );
		$account_url = add_query_arg( 'options', '__Options__', $account_url );
		$account_url = add_query_arg( 'token', rawurlencode( printess_create_url_token( $prod_id ) ), $account_url );
		$account_url = add_query_arg( 'displayName', '__DisplayName__', $account_url );
		$js_product  = printess_get_product_json( $product );

		printess_render_save_dialog();
		printess_render_information_overlay();

		include_once("includes/printess-dialogs.php");
		PrintessDialogs::render_display_name_dialog();

		$printess_ui_version = $product->get_meta( 'printess_ui_version', true);

		if(!isset($printess_ui_version) || empty($printess_ui_version)) {
			$printess_ui_version = "classical";
		}

		$itemUsage = $product->get_meta( 'printess_item_usage', true );

		if(null === $itemUsage) {
			$itemUsage = "";
		}

		$form_fields = printess_get_custom_formfields(get_current_user_id());
?>

		<script id="printess-integration">
			var printessGlobalConfig = {};
			let itemUsage = <?php echo(!empty($itemUsage) ? wp_json_encode($itemUsage) : "\"\""); ?>;

			try {
				printessGlobalConfig.pricePerUsageFields = JSON.parse(itemUsage);
			} catch(ex) {

			}

			<?php
				if(count($form_fields) > 0) {
					?>
						printessGlobalConfig.formFields = <?php echo wp_json_encode($form_fields); ?>;
					<?php
				}
			?>


			let showPrintessEditor = function() {};
			const userMessages = {
				"noDisplayName": <?php echo wp_json_encode( __( 'Please provide a display name.', 'printess-editor' ) ); ?>,
				"saveError": <?php echo wp_json_encode( __( 'There was an error while trying to save your design', 'printess-editor' ) ); ?>,
				"savingDesign": <?php echo wp_json_encode( __( 'Saving design to your list of saved designs', 'printess-editor' ) ); ?>,
				"closeWindow": <?php echo wp_json_encode( __( 'Please close this window or tab.', 'printess-editor' ) ); ?>
			};

			document.addEventListener("DOMContentLoaded", function() {
				const idsToHide = (<?php echo wp_json_encode( PrintessAdminSettings::get_ids_to_hide() ); ?> || "").split(",").map( (x) => x.trim());
				const classesToHide = (<?php echo wp_json_encode( PrintessAdminSettings::get_class_names_to_hide() ); ?> || "").split(",").map( (x) => x.trim());
				const product =  <?php echo wp_json_encode( $js_product ); ?>;
				const editor = typeof initPrintessWCEditor !== "undefined" ? initPrintessWCEditor({
																									"apiDomain:": <?php echo wp_json_encode( PrintessAdminSettings::get_domain() ); ?>,
																									"uiSettings": {
																										"startupLogoUrl": "",
																										"showStartupAnimation": true,
																										"uiVersion": <?php echo wp_json_encode( $printess_ui_version ); ?>,
																										"theme": <?php echo wp_json_encode( $theme ); ?>
																									},
																									"showAlertOnTabClose": <?php echo wp_json_encode(PrintessAdminSettings::get_show_warning_on_tab_close() === true) ?>,
																									"autoImportImageUrlsInFormFields": true,
																									"editorUrl": <?php echo wp_json_encode( PrintessAdminSettings::get_embed_html_url() ); ?>,
																									"shopToken": <?php echo wp_json_encode( PrintessAdminSettings::get_shop_token() ); ?>,
																									"hidePricesInEditor": false,
																									"editorVersion": "",
																									"priceFormatOptions": <?php echo wp_json_encode(printess_get_price_format_options()) ?>,
																									"idsToHide": idsToHide,
																									"classesToHide": classesToHide,
																									"addToCartAfterCustomization": <?php echo wp_json_encode( PrintessAdminSettings::get_add_to_cart_after_customization() ); ?>,
																									"cartUrl": <?php echo wp_json_encode( wc_get_cart_url() ) ?>,
																									"editorMode": <?php echo wp_json_encode( $mode ); ?>,
																									"userIsLoggedIn": <?php echo wp_json_encode( is_user_logged_in() ); ?>,
																									"accountPageUrl": <?php echo wp_json_encode( $account_url ); ?>,
																									"nonce": <?php echo wp_json_encode( wp_create_nonce( 'wp_rest' ) ); ?>,
																									"urlToken": <?php echo wp_json_encode( printess_create_url_token( $product->get_id() ) ); ?>,
																									"userMessages": userMessages,
																									"askForNameOnResave":
																									<?php
																									$show_dlg = get_option( 'printess_ask_for_name_on_resave', 'wpadminbar, page' );
																									echo wp_json_encode( true === $show_dlg || 'true' === $show_dlg || 'on' === $show_dlg );
																									?>
																									,
																									"customizeButtonClasses": <?php echo wp_json_encode( PrintessAdminSettings::get_customize_button_class() ); ?>,
																									"showPricesInEditor": <?php echo wp_json_encode( get_option( 'printess_show_prices_in_editor', 'off' ) === 'on' ); ?>,
																									"showProductName": <?php echo wp_json_encode( get_option( 'printess_show_product_name_in_editor', 'off' ) === 'on' ); ?>,
																									"attachParams": <?php echo wp_json_encode( $attachParams ); ?>
																									}) : null;

				if(!editor) {
					console.warn("Unable to initialize printess editor.");
					return;
				}

				const buttonLabelHtml = <?php echo wp_json_encode( esc_html__( 'Customize', 'printess-editor' ) ); ?>;
				const enforceDisplayName = <?php echo json_encode(PrintessAdminSettings::get_enforce_design_name()) ?>;

				showPrintessEditor = function(saveToken = null, basketItemId = null) {
					const pageCountFormFieldOption = <?php echo wp_json_encode( PrintessAdminSettings::get_legal_text()  ); ?>;
					basketItemId = basketItemId || null;

					const settings = {
						templateNameOrSaveToken: saveToken || product.templateName,
						product: product,
						basketItemId: basketItemId,
						basektId: <?php echo wp_json_encode( $cart_id ); ?>,
						userId: <?php echo wp_json_encode( get_current_user_id() ); ?>,
						optionValueMappings: <?php echo wp_json_encode( $product->get_meta( 'printess_custom_formfield_mappings', true ) ); ?>,
						legalText: <?php echo wp_json_encode( PrintessAdminSettings::get_legal_text()  ); ?>,
						additionalAttachParams: <?php echo wp_json_encode( $additionalAttachParams, JSON_FORCE_OBJECT ); ?>,
						enforceDisplayName: enforceDisplayName
					};

					editor.show(settings);
				};

				editor.initProductPage(product, product.templateName, <?php echo wp_json_encode( PrintessAdminSettings::get_customize_button_class() ); ?>, buttonLabelHtml, showPrintessEditor, "form.cart"/* product: IWooCommerceProduct, saveToken: string, customizeButtonClass: string, designNowButtonLabel: string = "Customize", formSelector: string = "form.cart" */, enforceDisplayName);
			});
		</script>
		<?php
	}
}

	/**
	 * Registers the needed hooks for the single product view to handle Printess products.
	 */
function printess_show_printess_view_if_printess_product() {
	global $product;

	if ( isset( $product ) ) {
		$printess_attribute = $product->get_meta( 'printess_template', true );
	}

	if ( ! empty( $printess_attribute ) ) {
		printess_render_editor_integration( $product, 'buyer' );
	}
}

function printess_extract_yith_plugin_values($yith_meta_data) {
	$ret = array();

	$data = $yith_meta_data->get_data();

	if(isset($data) && array_key_exists("value", $data)) {
		$values = $data["value"];

		foreach($values as $x => $y) {
			foreach($y as $key => $value) {
				$label = $value["display_label"];

				if(str_contains($value["display_value"], ": ")) {
					$index = strpos($value["display_value"], ": ");

					if(false !== $index && $index >= 0) {
						$ret[$label] = substr($value["display_value"], $index + 2);
					}
				} else {
					$ret[$label] = $value["display_value"];
				}
				break;
			}
		}
	}

	return $ret;
}

function add_production_vdp_data(&$order, &$line_item, &$product, &$produce_payload) {
	$produce_payload["vdp"]["form"]["itemQuantity"] = "{$line_item->get_quantity()}";
	$produce_payload["vdp"]["form"]["itemSku"] = $product->get_sku();

	try {
		$produce_payload["vdp"]["form"]["orderDate"] = $order->get_date_created()->format('c');
	} catch(\Exception $ex) {

	}

	$produce_payload["vdp"]["form"]["productName"] = $product->get_name();

	$produce_payload["vdp"]["form"]["ShippingFirstName"] = $order->get_shipping_first_name();
	$produce_payload["vdp"]["form"]["ShippingLastName"] = $order->get_shipping_last_name();
	$produce_payload["vdp"]["form"]["ShippingName"] = $order->get_shipping_first_name() . " " . $order->get_shipping_last_name();
	$produce_payload["vdp"]["form"]["ShippingAddress1"] = $order->get_shipping_address_1();
	$produce_payload["vdp"]["form"]["ShippingAddress2"] = $order->get_shipping_address_2();
	$produce_payload["vdp"]["form"]["ShippingCity"] = $order->get_shipping_city();
	$produce_payload["vdp"]["form"]["ShippingCompany"] = $order->get_shipping_company();
	$produce_payload["vdp"]["form"]["ShippingCountry"] =  $order->get_shipping_country();
	$produce_payload["vdp"]["form"]["ShippingProvince"] = $order->get_shipping_state();
	$produce_payload["vdp"]["form"]["ShippingZip"] = $order->get_shipping_postcode();
	$produce_payload["vdp"]["form"]["ShippingPhone"] = $order->get_shipping_phone();
	// $produce_payload["vdp"]["form"]["ShippingCountryCode"]
	// $produce_payload["vdp"]["form"]["ShippingProvinceCode"]

	$produce_payload["vdp"]["form"]["BillingFirstName"] = $order->get_billing_first_name();
	$produce_payload["vdp"]["form"]["BillingLastName"] = $order->get_billing_last_name();
	$produce_payload["vdp"]["form"]["BillingName"] = $order->get_billing_first_name() . " " . $order->get_billing_last_name();
	$produce_payload["vdp"]["form"]["BillingAddress1"] = $order->get_billing_address_1();
	$produce_payload["vdp"]["form"]["BillingAddress2"] = $order->get_billing_address_2();
	$produce_payload["vdp"]["form"]["BillingCity"] = $order->get_billing_city();
	$produce_payload["vdp"]["form"]["BillingCompany"] = $order->get_billing_company();
	$produce_payload["vdp"]["form"]["BillingCountry"] = $order->get_billing_country();
	$produce_payload["vdp"]["form"]["BillingProvince"] = $order->get_billing_state();
	$produce_payload["vdp"]["form"]["BillingZip"] = $order->get_billing_postcode();
	$produce_payload["vdp"]["form"]["BillingPhone"] = $order->get_billing_phone();
	// $produce_payload["vdp"]["form"]["BillingProvinceCode"]
	// $produce_payload["vdp"]["form"]["BillingCountryCode"]

	$user_id = get_post_meta( $order->get_id(), '_customer_user', true );

	try {
		if(isset($user_id) && $user_id > 0) {
			$produce_payload["vdp"]["form"]["CustomerFirstName"] = get_user_meta( $user_id, 'shipping_first_name', true );
			$produce_payload["vdp"]["form"]["CustomerLastName"] = get_user_meta( $user_id, 'shipping_last_name', true );
			$produce_payload["vdp"]["form"]["CustomerName"] = get_user_meta( $user_id, 'shipping_first_name', true ) . " " . get_user_meta( $user_id, 'shipping_last_name', true );
			$produce_payload["vdp"]["form"]["CustomerAddress1"] = get_user_meta( $user_id, 'shipping_address_1', true );
			$produce_payload["vdp"]["form"]["CustomerAddress2"] = get_user_meta( $user_id, 'shipping_address_2', true );
			$produce_payload["vdp"]["form"]["CustomerCity"] = get_user_meta( $user_id, 'shipping_city', true );
			$produce_payload["vdp"]["form"]["CustomerCompany"] = get_user_meta( $user_id, 'shipping_company', true );
			$produce_payload["vdp"]["form"]["CustomerCountry"] = get_user_meta( $user_id, 'shipping_country', true );
			$produce_payload["vdp"]["form"]["CustomerPhone"] = get_user_meta( $user_id, 'shipping_phone', true );
			$produce_payload["vdp"]["form"]["CustomerProvince"] = get_user_meta( $user_id, 'shipping_state', true );
			$produce_payload["vdp"]["form"]["CustomerZip"] = get_user_meta( $user_id, 'shipping_postcode', true );
						//$produce_payload["vdp"]["form"]["CustomerProvinceCode"]
			//$produce_payload["vdp"]["form"]["CustomerCountryCode"]
		}
	} catch(\Exception $ex) {

	}

	//Categories and tags needs to be read from parent product in case of variant
	$parent_id = $product->get_parent_id();
	$parent_product = $product;

	if ( isset( $parent_id ) && 0 < $parent_id ) {
		$ppt = wc_get_product( $parent_id );

		if ( isset( $ppt ) ) {
			$parent_product = $ppt;
		}
	}

	$product_categories = get_the_terms( $parent_product->get_id(), "product_cat" );

	$category_list = "";
	$cslug_list = "";
	if(is_array($product_categories) && count($product_categories) > 0) {
		for($i = 0; $i < count($product_categories); ++$i) {
			if($i > 0) {
				$category_list .= ",";
				$cslug_list .= "_";
			}

			$category_list .= $product_categories[$i]->name;
			$cslug_list .= $product_categories[$i]->slug;
		}
	}

	$produce_payload["vdp"]["form"]["productCategories"] = $category_list;
	$produce_payload["vdp"]["form"]["productCategorySlugs"] = $cslug_list;

	$product_tags = get_the_terms( $parent_product->get_id(), "product_tag" );

	$tag_list = "";
	$tslug_list = "";
	if(is_array($product_tags) && count($product_tags) > 0) {
		for($i = 0; $i < count($product_tags); ++$i) {
			if($i > 0) {
				$tag_list .= ",";
				$tslug_list .= "_";
			}

			$tag_list .= $product_tags[$i]->name;
			$tslug_list .= $product_tags[$i]->slug;
		}
	}

	$produce_payload["vdp"]["form"]["productTags"] = $tag_list;
	$produce_payload["vdp"]["form"]["productTagSlugs"] = $tslug_list;

	$onlyCharAndNumber = function($star) {
		$ret = "";

		for($i = 0; $i < strlen($star); ++$i) {
			if(($star[$i] >= "0" && $star[$i] <= "9") || ($star[$i] >= "a" && $star[$i] <= "z") || ($star[$i] >= "A" && $star[$i] <= "Z")) {
				$ret .= $star[$i];
			}
		}

		return $ret;
	};

	$product_helper = new PrintessProductHelpers($product->get_id());

	try {
		$meta_data = $line_item->get_meta_data();

		foreach($meta_data as $key => $value) {
			$mapped_name_value = $product_helper->map_attribute_name_and_value($value->key, $value->value);

			if("_ywapo_meta_data" === $value->key) {
				$yith_values = printess_extract_yith_plugin_values($value);

				foreach($yith_values as $label => $value) {
					$name = $onlyCharAndNumber($label);
					if(!array_key_exists($name, $produce_payload["vdp"]["form"])) {
						$produce_payload["vdp"]["form"][$name] = $value;
					}
				}
			} else if(!str_starts_with($value->key, "ywapo-")) {
				$name = $onlyCharAndNumber($mapped_name_value["name"]);
				if(!array_key_exists($name, $produce_payload["vdp"]["form"])) {
					$produce_payload["vdp"]["form"][$name] = $mapped_name_value["value"];
				}
			}
		}
	}
	catch(ex) {

	}

	$productAttributes = $product_helper->get_attributes();

	try {
		foreach ($productAttributes  as $key => $attribute ) {
			if(is_string($attribute)) {
				$mapped_name_value = $product_helper->map_attribute_name_and_value($key, $attribute);

				if(!array_key_exists($mapped_name_value["name"], $produce_payload["vdp"]["form"])) {
					$produce_payload["vdp"]["form"][$onlyCharAndNumber($mapped_name_value["name"])] = $mapped_name_value["value"];
				}
			} else if(array_key_exists("key", $attribute) && array_key_exists("name", $attribute) && array_key_exists("values", $attribute)) {
				$label = $onlyCharAndNumber($attribute["name"]);
				$value = "";

				foreach($attribute["values"] as $value_entry) {
					if(!empty($value)) {
						$value .= "_";
					}

					$value .= $value_entry;
				}

				if(!array_key_exists($label, $produce_payload["vdp"]["form"])) {
					$produce_payload["vdp"]["form"][$label] = $value;
				}
			} else {
				$taxonomy = get_taxonomy($attribute['name']);
				$options = wc_get_product_terms( $product->get_id(), $attribute['name'], array( 'fields' => 'all' ) );
				$label = $onlyCharAndNumber(str_replace('Product ', '', $taxonomy->label));
				$value = "";

				foreach($options as $option) {
					if(!empty($value)) {
						$value .= "_";
					}

					$value .= $option->name;
				}

				if(!array_key_exists($label, $produce_payload["vdp"]["form"])) {
					$produce_payload["vdp"]["form"][$label] = $value;
				}
			}
		}
	} catch(ex) {

	}
}

	/**
	 * Sends the order line item to the Printess api for production.
	 *
	 * @param WC_Product $product           The product.
	 * @param mixed      $order_id          The order id.
	 * @param mixed      $line_item_id      The order line item id.
	 * @param mixed      $save_token        The save token.
	 * @param mixed      $dropship_data_dto The dropship data.
	 * @param int        $copies The number of copies that should be produced (quantity).
	 */
function printess_produce( $product, $order_id, $line_item_id, $line_item, $save_token, $dropship_data_dto = null, $copies = 1 ) {
	$printess_host  = PrintessAdminSettings::get_host();
	$site_url       = get_site_url();
	$callback_url   = "$site_url/wp-json/printess/v1/job/finished";
	$dpi            = $product->get_meta( 'printess_dpi', true );
	$output_type    = $product->get_meta( 'printess_output_type', true );
	$jpg_compresion = $product->get_meta( 'printess_jpg_compression', true );
	$outputFilesConfig = $product->get_meta( 'printess_output_files', true );
	$sku = $product->get_sku();

	 if(PrintessAdminSettings::get_debug()) {
	 	$callback_url = str_replace("https://", "http://", $callback_url);
	 }

	if($outputFilesConfig !== null && strlen(trim($outputFilesConfig)) > 0) {
		$outputFilesConfig = json_decode($outputFilesConfig, true);
	}

	$parent_id = $product->get_parent_id();

	if ( isset( $parent_id ) && 0 < $parent_id ) {
		$parent_product = wc_get_product( $parent_id );

		if ( isset( $parent_product ) ) {
			$dpi            = $parent_product->get_meta( 'printess_dpi', true );
			$output_type    = $parent_product->get_meta( 'printess_output_type', true );
			$jpg_compresion = $parent_product->get_meta( 'printess_jpg_compression', true );
		}
	}

	if ( ! isset( $dpi ) || empty( $dpi ) ) {
		$dpi = 300;
	}

	if ( ! is_int( $dpi ) ) {
		$dpi = intval( $dpi );
	}

	if ( $dpi <= 0 ) {
		$dpi = 300;
	}

	if ( ! isset( $output_type ) || empty( $output_type ) ) {
		$output_type = get_option( 'printess_output_format', 'pdf' );

		if ( null === $output_type || empty( $output_type ) ) {
			$output_type = 'pdf';
		}
	}

	if ( ! isset( $jpg_compresion ) || empty( $jpg_compresion ) || '0' === $jpg_compresion || 0 === $jpg_compresion ) {
		$jpg_compresion = get_option( 'printess_jpg_compression', '90' );

		if ( ! isset( $jpg_compresion ) || empty( $jpg_compresion ) || '0' === $jpg_compresion || 0 === $jpg_compresion ) {
			$jpg_compresion = '90';
		}
	}

	if ( ! is_int( $jpg_compresion ) ) {
		$jpg_compresion = intval( $jpg_compresion );
	}

	$data = array(
		'externalOrderId' => "$order_id",
		'templateName'    => $save_token,
		'copies'          => $copies,
		'meta'            => wp_json_encode(
			array(
				'orderId'    => $order_id,
				'lineItemId' => $line_item_id,
			)
		),
		'callbackUrl'     => $callback_url,
		'origin'          => "WC $site_url",
		'outputType'      => $output_type,
		'outputSettings'  => array(
			'dpi'             => $dpi,
			'jpegCompression' => $jpg_compresion,
		),
		'vdp'             => array(
			'data' => array(
				'orderId'    => "{$order_id}",
				'lineItemId' => "{$line_item_id}",
				"sku" => $sku
			),
			'form' => array(
				'orderId'    => "{$order_id}",
				'lineItemId' => "{$line_item_id}",
				"sku" => $sku
			),
		),
	);

	$order = wc_get_order( $order_id );
	add_production_vdp_data($order, $line_item, $product, $data);

	//In case output file config is provided
	if(is_array($outputFilesConfig) && count($outputFilesConfig) > 0) {
		$data["outputFiles"] = array();

		foreach($outputFilesConfig as $documentName => &$outputSettings) {
			if(is_array($outputSettings)) {
				$outputSettings["documentName"] = $documentName;

				if(array_key_exists("outputFileName", $outputSettings)) {
					$outputSettings["outputFileName"] = printess_variable_replacer($outputSettings["outputFileName"], $data["vdp"]["form"], function($variableName, &$data) {
						if(array_key_exists($variableName, $data)) {
							return $data[$variableName];
						}

						return $variableName;
					});
				}

				$data["outputFiles"][] = $outputSettings;
			}
		}
	}

	$data["orderMetaData"] = json_encode($data["vdp"]["form"]);

	$api_endpoint = '/production/produce';

	if ( isset( $dropship_data_dto ) ) {
		$data['dropship'] = $dropship_data_dto;
		$api_endpoint     = '/dropship/produce';
	}

	$response = PrintessApi::send_post_request(
		$printess_host . $api_endpoint,
		PrintessAdminSettings::get_service_token(),
		$data
	);

	return $response['jobId'];
}

/**
 * Checks if an item has drop shipping enabled or not and returns the corresponding drop ship product definition id
 *
 * @param mixed $product The product information of the drop shipping item.
 * @param mixed $item   The order item.
 */
function printess_order_item_get_dropship_product_definition_id( $product, &$item ) {
	$system_dropshipping = get_option( 'printess_system_default_dropshipping', '' );
	$save_token          = $item->get_meta( '_printess-save-token', true );

	if ( ! isset( $save_token ) || '' === $save_token ) {
		return -1;
	}

	if ( null === $system_dropshipping || '' === $system_dropshipping || ! is_numeric( $system_dropshipping ) ) {
		$system_dropshipping = -2;
	} else {
		$system_dropshipping = intval( $system_dropshipping );
	}

	$dropshipping = $item->get_meta( '_printess-dropshipping', true );

	if ( -2 !== $system_dropshipping && isset( $dropshipping ) && 0 === intval( $dropshipping ) ) {
		$dropshipping = $system_dropshipping;
	}

	return intval("" . $dropshipping);
}

/**
 * Creates a new drop shipping address via the printess api
 *
 * @param mixed $data The Address data that should be used.
 */
function printess_create_dropshipping_address( $data ) {
	$printess_host = PrintessAdminSettings::get_host();

	return PrintessApi::send_post_request(
		"$printess_host/dropshipData/save",
		PrintessAdminSettings::get_service_token(),
		$data
	);
}

/**
 * Returns a shipping property by its given key and returns en empty string in case there is no value
 *
 * @param string $key     the property name.
 * @param mixed  $address the address object.
 */
function printess_get_shipping_value( $key, $address ) {
	if ( null !== $address && array_key_exists( $key, $address ) && ! empty( '' . $address[ $key ] ) ) {
		return '' . $address[ $key ];
	}

	return '';
}

/**
 * Checks if we have to use the billing address instead of the shipping address in case of drop shipping orders
 *
 * @param mixed $shipping_address The shipping address object of the order.
 */
function printess_use_billing_address( $shipping_address ) {
	// None of the name values provided or no street provided.
	if ( ( empty( printess_get_shipping_value( 'first_name', $shipping_address ) ) && empty( printess_get_shipping_value( 'last_name', $shipping_address ) ) ) || empty( 'address_1' ) ) {
		return true;
	}

	return false;
}

/***
 * Splits all order items into dropship and non dropship items and handles the printess production
 *
 * @param mixed $order the current order the order items are coming from.
 * @param mixed $order_items The order Items.
 */
function printess_handle_order_items( &$order, &$order_items ) {
	$dropship_items     = array();
	$non_dropship_items = array();
	$order_id           = $order->get_id();

	$repo = new Printess_Saved_Design_Repository();

	// Seperates dropshipping and non drop shipping products.
	foreach ( $order_items as $key => &$value ) {
		if ( printess_order_item_get_dropship_product_definition_id( $value->get_product(), $value ) >= 0 ) {
			$dropship_items[ $key ] = $value;
		} else {
			$non_dropship_items[ $key ] = $value;
		}
	}

	foreach ( $non_dropship_items as $key => &$value ) {
		$save_token = $value->get_meta( '_printess-save-token', true );
		$job_id     = $value->get_meta( '_printess-job-id', true );
		$design_id  = $value->get_meta( '_printess-design-id', true );

		if ( ! empty( $save_token ) && empty( $job_id ) ) {
			$product  = $value->get_product();
			$quantity = $value->get_quantity();
			$job_id   = printess_produce( $product, $order_id, $key, $value, $save_token, null, $quantity );

			$value->update_meta_data( '_printess-job-id', $job_id, true );
			$value->save_meta_data();

			if ( $design_id ) {
				printess_unexpire_save_token( $save_token, printess_create_new_unexpiration_date( true ) );

				$repo->update_last_ordered( $order->get_customer_id(), intval( $design_id ) );
			}
		}
	}

	if ( count( $dropship_items ) > 0 ) {
		$shipping = $order->get_address( 'shipping' );

		if ( printess_use_billing_address( $shipping ) ) {
			$shipping = $order->get_address(); // Should return billing address.
		}

		$dropship_data = array(
			'companyName'  => printess_get_shipping_value( 'company', $shipping ),
			'firstName'    => printess_get_shipping_value( 'first_name', $shipping ),
			'lastName'     => printess_get_shipping_value( 'last_name', $shipping ),
			'address1'     => printess_get_shipping_value( 'address_1', $shipping ),
			'address2'     => printess_get_shipping_value( 'address_2', $shipping ),
			'city'         => printess_get_shipping_value( 'city', $shipping ),
			'zip'          => printess_get_shipping_value( 'postcode', $shipping ),
			'country'      => printess_get_shipping_value( 'country', $shipping ),
			'countryState' => printess_get_shipping_value( 'state', $shipping ),
			'phone'        => printess_get_shipping_value( 'phone', $shipping ),
			'email'        => null !== $order->get_billing_email() && '' !== $order->get_billing_email() ? $order->get_billing_email() : null,
		);

		$shipping_items = $order->get_items( 'shipping' );

		if ( isset( $shipping_items ) && count( $shipping_items ) > 0 ) {
			foreach ( $shipping_items as $item_id => &$item ) {
				$dropship_data['shipping'] = $item->get_method_title();
				break;
			}
		}

		$customer_notes = $order->get_customer_note();

		if ( isset( $customer_notes ) && ! empty( $customer_notes ) ) {
			$dropship_data['dispatchNotice'] = $customer_notes;
		}

		$dropship_id = printess_create_dropshipping_address(
			array(
				'userId' => '' . $order->get_customer_id(),
				'type'   => 'printess-shipping',
				'json'   => wp_json_encode( $dropship_data ),
			)
		);

		foreach ( $dropship_items as $key => &$value ) {
			$save_token = $value->get_meta( '_printess-save-token', true );
			$job_id     = $value->get_meta( '_printess-job-id', true );
			$design_id  = $value->get_meta( '_printess-design-id', true );

			if ( ! empty( $save_token ) && empty( $job_id ) ) {
				$product  = $value->get_product();
				$quantity = $value->get_quantity();
				$site_url = get_site_url();
				$nonce    = uniqid( 'printess_', true );

				$value->update_meta_data( '_printess-dropship-nonce', $nonce, true );
				$value->save_meta_data();

				$dropship_data_dto = array(
					'dropshipDataId'         => $dropship_id,
					'productDefinitionId'    => printess_order_item_get_dropship_product_definition_id($product, $value),
					'callbackType'           => 3, /*url*/
					'callbackPayload'        => array(
						'url'          => "$site_url/wp-json/printess/v1/order/status/changed",
						'thumbnailUrl' => $value->get_meta( '_printess-thumbnail-url', true ),
						'orderId'      => $order_id,
						'lineItemId'   => $value->get_id(),
						'nonce'        => $nonce,
					),
					'linkedOrderLineItems'   => count( $dropship_items ) > 1 ? count( $dropship_items ) : 0,
					'linkedOrderLineItemsId' => count( $dropship_items ) > 1 ? '' . $order_id : null,
				);

				$job_id = printess_produce( $product, $order_id, $key, $value, $save_token, $dropship_data_dto, $quantity );

				$value->update_meta_data( '_printess-job-id', $job_id, true );
				$value->save_meta_data();

				if ( $design_id ) {
					printess_unexpire_save_token( $save_token, printess_create_new_unexpiration_date( true ) );

					$repo->update_last_ordered( $order->get_customer_id(), intval( $design_id ) );
				}
			}
		}
	}
}

	/**
	 * Sends the Printess products from the order to Printess for production.
	 * Sets the Printess job id in the meta data.
	 *
	 * @param mixed $order_id The order id.
	 */
function printess_send_to_printess_api( $order_id ) {
	$approval_mode = PrintessAdminSettings::get_approval_mode();

	if ( 'manual' === $approval_mode ) {
		return;
	}

	$order = new WC_Order( $order_id );
	$items = $order->get_items();

	printess_handle_order_items( $order, $items );
}

	/**
	 * The callback method when Printess finished production.
	 * Will set the result for this order line item.
	 *
	 * @param mixed $request The request coming in.
	 */
function printess_post_custom_method( $request ) {
	$parameters   = $request->get_json_params();
	$meta         = json_decode( $parameters['meta'], true );
	$job_id       = $parameters['jobId'];
	$order_id     = $meta['orderId'];
	$line_item_id = $meta['lineItemId'];

	if ( ! empty( $order_id ) && ! empty( $line_item_id ) ) {
		$order = new WC_Order( $order_id );
		$item  = $order->get_item( $line_item_id );

		if ( isset( $item ) ) {
			$item_job_id = $item->get_meta( '_printess-job-id', true );

			if ( $item_job_id === $job_id ) {
				$item->update_meta_data( '_printess-result', wp_json_encode( $parameters ), true );

				try {
					if(array_key_exists("result", $parameters) && array_key_exists("ff", $parameters["result"])) {
						$formFieldLink = $parameters["result"]["ff"];

						if(null != $formFieldLink && !empty($formFieldLink)) {
							$formFields = PrintessApi::send_get_request($formFieldLink);

							if(null != $formFields && is_array($formFields) && array_key_exists("environment", $formFields)) {
								$item->update_meta_data( '_printess-form-fields', wp_json_encode( $formFields["environment"] ), true );
							}
						}
					}
				} catch(\Exception $e) {

				}

				$item->save_meta_data();
			} else {
				return 'jobId differs';
			}
		} else {
			return 'cannot find order line item';
		}
	} else {
		return 'either orderId or lineItemId are empty';
	}

	return $parameters;
}

	/**
	 * The callback method when you want to approve an order externally.
	 *
	 * @param mixed $request The request coming in.
	 */
function printess_post_order_item_approve( $request ) {
	$parameters   = $request->get_json_params();
	$order_id     = $parameters['orderId'];
	$line_item_id = $parameters['lineItemId'];
	$save_token   = $parameters['saveToken'];
	$access_token = $parameters['accessToken'];

	$expected_access_token = PrintessAdminSettings::get_access_token();

	if ( ! empty( $order_id ) && ! empty( $line_item_id ) && ! empty( $save_token ) && ! empty( $access_token ) ) {
		if ( $expected_access_token !== $access_token ) {
			return 'access tokens do not match';
		}

		$order = new WC_Order( $order_id );
		$item  = $order->get_item( $line_item_id );

		if ( isset( $item ) ) {
			$item->delete_meta_data( '_printess-result' );
			$item->delete_meta_data( '_printess-job-id' );
			$item->update_meta_data( '_printess-save-token', $save_token, true );
			$item->save_meta_data();
			$order_items = array( $line_item_id => $item );
			printess_handle_order_items( $order, $order_items );
			return 'ok';
		} else {
			return 'cannot find order line item';
		}
	} else {
		return 'either orderId / lineItemId / saveToken / accessToken are empty';
	}

	return $parameters;
}

/**
 * The callback method when Printess received a dropship status changed.
 * Will set the result for the order line items.
 *
 * @param mixed $request The request coming in.
 */
function printess_post_status_changed( $request ) {
	$parameters = $request->get_json_params();

	if ( ! isset( $parameters )
		|| ! array_key_exists( 'orderId', $parameters )
		|| ! array_key_exists( 'orderStatus', $parameters )
		|| ! array_key_exists( 'lineItems', $parameters )
		|| ! isset( $parameters['lineItems'] )
		|| empty( '' . $parameters['orderId'] )
		|| empty( '' . $parameters['orderStatus'] )
	) {

		return 'Invalid order id or status';
	}

	$order            = new WC_Order( $parameters['orderId'] );
	$line_item_lookup = array();

	if ( ! isset( $order ) ) {
		return 'Invalid order';
	}

	if ( count( $parameters['lineItems'] ) < 1 ) {
		return 'Invalid order status';
	}

	$number_of_printess_dropship_items = 0;
	$recorded_items                    = array();

	foreach ( $parameters['lineItems'] as &$line_item ) {
		if ( ! array_key_exists( 'lineItemId', $line_item ) ) {
			return 'Invalid line item';
		}

		foreach ( $order->get_items() as &$order_line_item ) {
			$dropshipping = $order_line_item->get_meta( '_printess-dropshipping' );

			if ( null !== $dropshipping && ! empty( '' . $dropshipping ) && $dropshipping > -1 ) {
				if ( ! array_key_exists( $order_line_item->get_id(), $recorded_items ) ) {
					$recorded_items[ $order_line_item->get_id() ] = true;
					$number_of_printess_dropship_items            = ++$number_of_printess_dropship_items;
				}
			}

			if ( $order_line_item->get_id() === $line_item['lineItemId'] ) {
				$nonce = $order_line_item->get_meta( '_printess-dropship-nonce' );

				if ( $nonce !== $line_item['nonce'] ) {
					return 'Invalid line item ' . $line_item['lineItemId'];
				}

				$line_item_lookup[ $line_item['lineItemId'] ] = $order_line_item;

				$has_new_meta_data = false;

				if ( array_key_exists( 'trackingId', $line_item ) && ! empty( $line_item['trackingId'] ) ) {
					$order_line_item->update_meta_data( '_printess-dropship-tracking-id', $line_item['trackingId'] );
					$has_new_meta_data = true;
				}

				if ( array_key_exists( 'trackingUrl', $line_item ) && ! empty( $line_item['trackingUrl'] ) ) {
					$order_line_item->update_meta_data( '_printess-dropship-tracking-url', $line_item['trackingUrl'] );
					$has_new_meta_data = true;
				}

				$order_line_item->update_meta_data( '_printess_shipping-status', $parameters['orderStatus'] );
				$has_new_meta_data = true;

				if ( $has_new_meta_data ) {
					$order_line_item->save_meta_data();
				}
			}
		}
	}

	$number_of_shipped_dropship_items = 0;

	foreach ( $order->get_items() as &$order_line_item ) {
		if ( $order_line_item->get_meta( '_printess_shipping-status' ) === 'shipped' ) {
			$number_of_shipped_dropship_items = ++$number_of_shipped_dropship_items;
		}
	}

	if ( $number_of_printess_dropship_items === $number_of_shipped_dropship_items ) {
		$order->update_status( 'completed' );
	}

	return $parameters;
}

/**
 * Sets a new expiration date on printess template via printess api
 *
 * @param string $save_token The save token containing the latest changes.
 * @param string $expires_at_utc The new expiration date.
 */
function printess_unexpire_save_token( $save_token, $expires_at_utc ) {
	$expiration_date_string = null === $expires_at_utc ? null : str_replace( ' ', 'T', $expires_at_utc->format( 'Y-m-d H:i:s' ) ) . 'Z';
	$printess_host          = PrintessAdminSettings::get_host();

	$payload = array(
		'id'          => $save_token,
		'expiresOn'   => $expiration_date_string,
		'dataOnly'    => true,
		'buyerImages' => true,
	);

	$result = PrintessApi::send_post_request( "$printess_host/shop/template/unexpire", PrintessAdminSettings::get_service_token(), $payload );
}

/**
 * Creates a new expiration date using the admin settings for expiration days
 *
 * @param bool $use_order_date Use the ordered design expiration instead.
 * @return DateTime The new expiration date
 */
function printess_create_new_unexpiration_date( $use_order_date = false ) {
	$setting = get_option( 'printess_saved_design_lifetime', 30 );

	if ( true === $use_order_date ) {
		$setting = get_option( 'printess_ordered_design_lifetime', 30 );
	}

	if ( ! isset( $setting ) || empty( $setting ) ) {
		$setting = 30;
	}

	$setting = intval( $setting );

	if ( $setting < 1 ) {
		return null;
	}

	$dt = new DateTime();
	$dt->add( new DateInterval( 'P' . $setting . 'D' ) );
	$dt->setTimezone( new DateTimeZone( 'UTC' ) );

	return $dt;
}

/**
 * Incoming api call to create a new saved design database entry
 *
 * @param mixed $request The incoming web request.
 */
function printess_post_add_design( $request ) {
	$parameters = $request->get_json_params();
	$ret        = array();

	$customer_id = printess_get_current_user_id();

	if ( null === $customer_id || $customer_id < 1 ) {
		$ret['error'] = 'Not logged in';
		return $ret;
	}

	if ( ! isset( $parameters )
		|| ! array_key_exists( 'saveToken', $parameters )
		|| ! array_key_exists( 'thumbnailUrl', $parameters )
		|| ! array_key_exists( 'productId', $parameters )
		|| ! array_key_exists( 'displayName', $parameters )
		|| empty( '' . $parameters['saveToken'] )
		|| empty( '' . $parameters['thumbnailUrl'] )
		|| empty( '' . $parameters['productId'] )
	) {
		$ret['error'] = 'Invalid request';
		return $ret;
	}

	if ( ! ( array_key_exists( 'designId', $parameters ) && ! empty( $parameters['designId'] ) && intval( $parameters['designId'] ) > 0 ) && empty( '' . $parameters['displayName'] ) ) {
		$ret['error'] = 'Invalid request';
		return $ret;
	}

	$product = wc_get_product( intval( $parameters['productId'] ) );

	if ( ! isset( $product ) || false === $product ) {
		$ret['error'] = 'Unknown product';
		return $ret;
	}

	include_once 'includes/printess-saved-design-repository.php';

	$repo = new Printess_Saved_Design_Repository();

	if ( array_key_exists( 'designId', $parameters ) && ! empty( '' . $parameters['designId'] ) && intval( $parameters['designId'] ) > 0 ) {
		$design_id = intval( $parameters['designId'] );
		$designs   = $repo->get_designs( $customer_id, '', 1, 1, $design_id );

		if ( ! isset( $designs ) || count( $designs ) < 1 ) {
			$ret['error'] = 'Invalid design id';
			return $ret;
		} else {
			printess_unexpire_save_token( $parameters['saveToken'], printess_create_new_unexpiration_date() );

			if ( array_key_exists( 'displayName', $parameters ) && null !== $parameters['displayName'] && '' !== $parameters['displayName'] && $parameters['displayName'] === $designs[0]['displayName'] ) {
				$options = null;

				if ( array_key_exists( 'options', $parameters ) ) {
					$options = json_decode( $parameters['options'], true );
				}

				$repo->update_design( $customer_id, $design_id, $parameters['saveToken'], $parameters['thumbnailUrl'], $options );
				return $design_id;
			} else {
					return $repo->add_design( $customer_id, $parameters['saveToken'], $parameters['thumbnailUrl'], $designs[0]['productId'], $designs[0]['productName'], $parameters['displayName'], $designs[0]['options'] );
			}
		}
	} else {
		printess_unexpire_save_token( $parameters['saveToken'], printess_create_new_unexpiration_date() );

		return $repo->add_design( $customer_id, $parameters['saveToken'], $parameters['thumbnailUrl'], intval( '' . $parameters['productId'] ), $product->get_data()['name'], $parameters['displayName'], json_decode( $parameters['options'], true ) );
	}
}


	/**
	 * Hide Printess specific meta entries.
	 *
	 * @param mixed $fields The meta data fields.
	 */
function printess_hide_order_item_meta_fields( $fields ) {
	if ( ! PrintessAdminSettings::get_debug() ) {
		$fields[] = '_printess-save-token';
		$fields[] = '_printess-thumbnail-url';
		$fields[] = '_printess-job-id';
		$fields[] = '_printess-result';
		$fields[] = '_printess-dropshipping';
		$fields[] = '_printess-design-id';
		$fields[] = '_printess-design-name';
		$fields[] = '_printess-valid-until';
		$fields[] = '_printess-dropship-nonce';
		$fields[] = '_printess-original-save-token';
		$fields[] = '_printess-form-fields';
	}

	return $fields;
}

	/**
	 * Shows order line item specific thumbnail.
	 *
	 * @param mixed $image   The default image for this product.
	 * @param mixed $item_id The order line item id.
	 * @param mixed $item    The order line item.
	 */
function printess_admin_order_item_thumbnail( $image, $item_id, $item ) {
	$printess_thumbnail_url = $item->get_meta( '_printess-thumbnail-url', true );

	if ( ! empty( $printess_thumbnail_url ) ) {
		return '<a href="' . esc_attr( $printess_thumbnail_url ) . '" target=_blank title="' . esc_attr__( 'View thumbnail in new window.', 'printess-editor' ) . '"><img src="' . esc_attr( $printess_thumbnail_url ) . '" /></a>';
	}

	return $image;
}

/**
 * Renders the drop shipping table that is put under each line item insiode the admin order details page
 *
 * @param mixed $printess_tracking_id the tracking id of the shipping provider that can be used to track the package.
 * @param mixed $printess_tracking_url   the tracking urkl that can be used to track this package.
 * @param mixed $printess_shipping_status the current shipping status.
 * @param mixed $classes White space seperated string list of additional class names that should be added to the table.
 */
function printess_render_dropship_table( $printess_tracking_id, $printess_tracking_url, $printess_shipping_status, $classes = '' ) {
	if ( ( ! isset( $printess_tracking_id ) || empty( '' . $printess_tracking_id ) )
		&& ( ! isset( $printess_tracking_url ) || empty( '' . $printess_tracking_url ) )
		&& ( ! isset( $printess_shipping_status ) || empty( '' . $printess_shipping_status ) )
	) {
		return;
	}

	$link_text = esc_html__( 'Tracking link', 'printess-editor' );

	if ( isset( $printess_tracking_id ) && ! empty( '' . $printess_tracking_id ) ) {
		$link_text = esc_html( $printess_tracking_id );
	}

	?>
	<table cellspacing="0" class="display_meta <?php echo $classes; ?>">
		<tbody>
				<?php
				if ( ( isset( $printess_tracking_id ) && ! empty( '' . $printess_tracking_id ) )
					|| ( isset( $printess_tracking_url ) && ! empty( '' . $printess_tracking_url ) )
				) {
					?>
						<tr>
							<th>
								<span><?php echo esc_html__( 'Tracking', 'printess-editor' ); ?></span>
							</th>;
							<td>
					<?php
					if ( isset( $printess_tracking_url ) && ! empty( '' . $printess_tracking_url ) ) {
						?>
										<a target=_blank href="<?php echo esc_url( $printess_tracking_url ); ?>"><?php echo $link_text; ?></a>
						<?php
					} else {
						?>
										<span><?php echo esc_html( $printess_tracking_id ); ?></span>
						<?php
					}
					?>
							</td>
					<?php
				}

				if ( isset( $printess_shipping_status ) && ! empty( '' . $printess_shipping_status ) ) {
					?>
							<tr>
								<th><?php echo esc_html__( 'Shipping status', 'printess-editor' ); ?></th>
								<td>
									<span><?php echo esc_html( $printess_shipping_status ); ?></span>
								</td>
							</tr>
					<?php
				}
				?>
		</tbody>
	</table>
	<?php
}

/**
 * Checks an order item for the valid until flag and decides if the edit button for printess products should be hidden.
 *
 * @param mixed $order_item the order item that should be checked.
 */
function printess_do_render_edit_link( $order_item ) {
	$ret = true;

	$valid_until = $order_item->get_meta( '_printess-valid-until', true );

	if ( null !== $valid_until && ! empty( $valid_until ) ) {
		try {
			$dt  = new \DateTime( $valid_until );
			$now = new \DateTime();
			$now->setTimezone( new DateTimeZone( 'UTC' ) );
			$now = $now->format( 'Y-m-d H:i:s' );

			return $valid_until > $now;

		} catch ( \Exception $ex ) {
			$valid_until = null;
		}
	}

	return true;
}

/**
 * Renders the html table to display personalized products, their thumbnail and their shipping status inside the order view.
 *
 * @param mixed $order The order that contains the line items that might be personalized.
 */
function printess_render_personalized_products_table( $order ) {
	$has_tracking        = false;
	$has_shipping_status = false;
	$has_thumbnail       = false;
	$has_save_token      = false;
	$has_design_name = false;
	$render_line_item_id = 'on' === get_option( 'printess_displaylineitemid', 'wpadminbar, page' );

	foreach ( $order->get_items() as &$item ) {
		$tracking_id     = $item->get_meta( '_printess-dropship-tracking-id' );
		$tracking_url    = $item->get_meta( '_printess-dropship-tracking-url' );
		$shipping_status = $item->get_meta( '_printess_shipping-status' );
		$thumbnail_url   = $item->get_meta( '_printess-thumbnail-url' );
		$save_token      = $item->get_meta( '_printess-save-token' );
		$design_name     = $item->get_meta( '_printess-design-name' );

		if ( isset( $tracking_id ) && ! empty( $tracking_id ) ) {
			$has_tracking = true;
		}

		if ( isset( $shipping_status ) && ! empty( $shipping_status ) ) {
			$has_shipping_status = true;
		}

		if ( isset( $thumbnail_url ) && ! empty( $thumbnail_url ) ) {
			$has_thumbnail = true;
		}

		if ( isset( $save_token ) && ! empty( $save_token ) ) {
			$has_save_token = true;
		}

		if ( isset( $design_name ) && ! empty( $design_name ) ) {
			$has_design_name = true;
		}
	}

	if ( ! $has_save_token ) {
		return;
	}

	?>
	<section>
		<h2 class="woocommerce-column__title"><?php echo esc_html__( 'Personalized products', 'printess-editor' ); ?></h2>

	<?php
		include_once 'includes/printess-table.php';

		// Render css.
		wp_enqueue_style( 'printess-editor' );

		$settings = array( 'tableClasses' => 'bottom_table_padding' );
		$table    = new PrintessTable( $settings );

	if ( $has_thumbnail ) {
		$table->add_column( '' );
	}

		$table->add_column( esc_html__( 'Product', 'printess-editor' ) );

	if ( $has_tracking ) {
		$table->add_column( esc_html__( 'Tracking', 'printess-editor' ) );
	}

	if ( $has_shipping_status ) {
		$table->add_column( esc_html__( 'Shipping status', 'printess-editor' ) );
	}

	if ( $has_save_token ) {
		$table->add_column( esc_html__( 'Open design', 'printess-editor' ) );
	}

	if ( $has_design_name ) {
		$table->add_column( esc_html__( 'Design Name', 'printess-editor' ) );
	}

	if ( $render_line_item_id ) {
		$table->add_column( esc_html__( 'Line item id', 'printess-editor' ) );
	}

	foreach ( $order->get_items() as &$item ) {
		$tracking_id     = $item->get_meta( '_printess-dropship-tracking-id' );
		$tracking_url    = $item->get_meta( '_printess-dropship-tracking-url' );
		$shipping_status = $item->get_meta( '_printess_shipping-status' );
		$link_text       = esc_html__( 'Tracking link', 'printess-editor' );
		$thumbnail_url   = $item->get_meta( '_printess-thumbnail-url' );
		$save_token      = $item->get_meta( '_printess-save-token' );
		$design_name     = $item->get_meta( '_printess-design-name' );
		$product         = wc_get_product( intval( $item->get_data()['variation_id'] < 1 ? $item->get_data() ['product_id'] : $item->get_data() ['variation_id'] ) );
		$permalink       = $product->get_permalink();
		$product_url     = add_query_arg( 'printess-save-token', $save_token, $permalink );
		$attributes      = $product->get_attributes();
		$design_name     = $item->get_meta( '_printess-design-name' );


		foreach ( $attributes as $key => $value ) {
			$product_url = add_query_arg( 'attribute_' . $key, $item->get_meta( $key ), $product_url );
		}

		if ( isset( $design_name ) && ! empty( $design_name ) ) {
			$product_url = add_query_arg( 'design_name' , $design_name, $product_url );
		}

		if ( isset( $tracking_id ) && ! empty( $tracking_id ) ) {
			$has_tracking = true;
		}

		if ( isset( $shipping_status ) && ! empty( $shipping_status ) ) {
			$has_shipping_status = true;
		}

		if ( $has_tracking ) {
			$link_text = esc_html( $tracking_id );
		}

		if ( null === $save_token || empty( $save_token ) ) {
			continue;
		}

		$content = array();

		if ( $has_thumbnail ) {
			if ( isset( $thumbnail_url ) && ! empty( '' . $thumbnail_url ) ) {
				$content[] = array(
					'thumbnail' => $thumbnail_url,
					'alt'       => esc_attr__( 'Product thumbnail', 'printess-editor' ),
				);
			} else {
				$content[] = '';
			}
		}

		$content[] = esc_html( $item->get_name() );

		if ( $has_tracking ) {
			if ( isset( $tracking_url ) && ! empty( '' . $tracking_url ) ) {
				$content[] = array(
					'url'   => $tracking_url,
					'label' => $link_text,
				);
			} else {
				$content[] = $tracking_id;
			}
		}

		if ( $has_shipping_status ) {
			$content[] = $shipping_status;
		}

		if ( $has_save_token && printess_do_render_edit_link( $item ) ) {
			$content[] = array(
				'url'   => $product_url,
				'label' => esc_html__( 'Open', 'printess-editor' ),
			);
		}

		if($has_design_name) {
			$content[] = $design_name;
		}

		if ( $render_line_item_id ) {
			$content[] = $item->get_id();
		}

		$table->add_row( $content );
	}

		echo $table->render( 'css_grid' );
	?>
	</section>
	<?php
}


/**
 * Hook that is called after the order items list is rendered on the customers account order details page
 * Will render the personalized items table to the order view
 *
 * @param mixed $order The order that is opened inside the customers order detail view.
 */
function printess_order_customer_meta_customized_display( $order ) {
	$has_tracking        = false;
	$has_shipping_status = false;

	foreach ( $order->get_items() as &$item ) {
		$tracking_id     = $item->get_meta( '_printess-dropship-tracking-id' );
		$tracking_url    = $item->get_meta( '_printess-dropship-tracking-url' );
		$shipping_status = $item->get_meta( '_printess_shipping-status' );

		if ( isset( $tracking_id ) && ! empty( $tracking_id ) ) {
			$has_tracking = true;
		}

		if ( isset( $shipping_status ) && ! empty( $shipping_status ) ) {
			$has_shipping_status = true;
		}
	}

	if ( ! $has_tracking && ! $has_shipping_status ) {
		return;
	}

	?>

	<section>
		<style>
			.no_top_and_bottom {
				border-bottom: 0px !important;
				border-top: 0px !important;
			}
		</style>
		<h2 class="woocommerce-column__title"><?php echo esc_html__( 'Shipping', 'printess-editor' ); ?></h2>

		<table class="woocommerce-table woocommerce-table--order-details">
			<thead>
				<tr>
					<th class="no_top_and_bottom woocommerce-table__product-name product-name"><span><?php echo esc_html__( 'Product', 'printess-editor' ); ?></span></th>
					<?php if ( $has_tracking ) { ?>
						<th class="no_top_and_bottom"><span><?php echo esc_html__( 'Tracking', 'printess-editor' ); ?></span></th>
					<?php } ?>
					<?php if ( $has_shipping_status ) { ?>
						<th class="no_top_and_bottom"><span><?php echo esc_html__( 'Shipping status', 'printess-editor' ); ?></span></th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $order->get_items() as &$item ) {
					$tracking_id     = $item->get_meta( '_printess-dropship-tracking-id' );
					$tracking_url    = $item->get_meta( '_printess-dropship-tracking-url' );
					$shipping_status = $item->get_meta( '_printess_shipping-status' );
					$link_text       = esc_html__( 'Tracking link', 'printess-editor' );

					if ( isset( $tracking_id ) && ! empty( $tracking_id ) ) {
						$has_tracking = true;
					}

					if ( isset( $shipping_status ) && ! empty( $shipping_status ) ) {
						$has_shipping_status = true;
					}

					if ( $has_tracking ) {
						$link_text = esc_html( $tracking_id );
					}

					?>
							<tr>
								<td class="no_top_and_bottom">
								<?php echo esc_html( $item->get_name() ); ?>
								</td>
					<?php if ( $has_tracking ) { ?>
									<td class="no_top_and_bottom">
						<?php
						if ( isset( $tracking_url ) && ! empty( '' . $tracking_url ) ) {
							?>
												<a target=_blank href="<?php echo esc_url( $tracking_url ); ?>"><?php echo $link_text; ?></a>
							<?php
						} else {
							?>
												<span><?php echo esc_html( $tracking_id ); ?></span>
							<?php
						}
						?>
									</td>
					<?php } ?>
								<?php if ( $has_shipping_status ) { ?>
									<td class="no_top_and_bottom"><span><?php echo $shipping_status; ?></span></td>
								<?php } ?>
							</tr>
					<?php
				}
				?>
			</tbody>
		</table>

	</section>

	<?php
}


	/**
	 * Shows Printess specific order line items details.
	 *
	 * @param mixed $item_id The order item id.
	 * @param mixed $item    The order item.
	 */
function printess_order_meta_customized_display( $item_id, $item ) {
	$printess_save_token      = $item->get_meta( '_printess-save-token' );
	$printess_original_save_token = $item->get_meta("_printess-original-save-token");
	$printess_result          = $item->get_meta( '_printess-result' );
	$printess_job_id          = $item->get_meta( '_printess-job-id' );
	$printess_tracking_id     = $item->get_meta( '_printess-dropship-tracking-id' );
	$printess_tracking_url    = $item->get_meta( '_printess-dropship-tracking-url' );
	$printess_shipping_status = $item->get_meta( '_printess_shipping-status' );
	$display_name = $item->get_meta( '_printess-design-name' );
	$printess_design_id = $item->get_meta( '_printess-design-id' );
	$form_fields = $item->get_meta( '_printess-form-fields' );

	if(null !== $display_name && !empty($display_name)) {
		echo '<table cellspacing="0" class="display_meta"><tbody>';
		echo '<tr><th>' . esc_html__("Design Name", "printess-editor")  . '</th><td>' . esc_html($display_name) . '</td></tr>';
		echo '</tbody></table>';
	}

	if ( ! empty( $printess_save_token ) ) {
		echo '<hr />';

		$order_id = $item->get_order_id();

		if($order_id === 0) {
			$order_id = $item->get_data()["order_id"];
		}

		$url = add_query_arg(
			array(
				'action'              => 'printess_edit_order_line_item',
				'order_id'            => $order_id,
				'printess_save_token' => $printess_save_token,
				'item_id'             => $item_id,
				'nonce'               => wp_create_nonce( 'printess_edit_order_line_item' ),
			),
			home_url()
		);

		if(null !== $printess_original_save_token && !empty($printess_original_save_token)) {
			$editUrl = "https://editor.printess.com?name=" . $printess_original_save_token;

			echo ' <div style="white-space:nowrap">' . esc_html__( 'Original Save token:', 'printess-editor' ) . "&nbsp;<a href=\"" . esc_url($edit_url) . "\" target=\"_blank\">" . esc_html($printess_original_save_token) . '</a></div>';
		}

		if(null !== $printess_save_token && !empty($printess_save_token)) {
			$edit_url = "https://editor.printess.com?name=" . $printess_save_token;
			$update_url = $url = add_query_arg(
				array(
					'action'   => 'printess_update_save_token',
					'order_id' => $order_id,
					'item_id'  => $item_id,
					'pst'      => "_NEW_SAVE_TOKEN_",
					'nonce'    => wp_create_nonce("printess_update_save_token" . $order_id . "_" . $item->get_id() ),
				),
				home_url()
			);

			echo ' <div><div style="white-space:nowrap">' . esc_html__( 'Save token:', 'printess-editor' ) . "&nbsp;<a href=\"" . esc_url($edit_url) . "\" target=\"_blank\"')\">" . esc_html($printess_save_token) . '</a></div>';
			echo "<script>
				if(!window['printessUpdateSaveToken']) {
					window['printessUpdateSaveToken'] = function(saveToken) {
						let newSaveToken = window.prompt(\"New save token\", saveToken);

						if(!newSaveToken) {
							return;
						} else {
							newSaveToken = newSaveToken.trim();
						}

						if(!newSaveToken || newSaveToken.indexOf('st:') !== 0 || newSaveToken.length !== 89) {
							alert(\"" . esc_html__( 'Invalid save token', 'printess-editor' ) . "\");
							setTimeout(function() {
								window['printessUpdateSaveToken'](saveToken);
							}, 0)
							return;
						}

						window.location.href = (\"" . $update_url . "\").replace(\"_NEW_SAVE_TOKEN_\", newSaveToken);
						return;
					};
				}
			</script>";
			echo ' <div data-save-token="' . esc_html($printess_save_token) . '"><a href="#" onclick="window.printessUpdateSaveToken(\'' . esc_html($printess_save_token) . '\')" >' . esc_html__( 'Edit save token', 'printess-editor' ) . "</a></div>";
			echo '</div>';
		}


		echo ' <div>' . esc_html__( 'Line item id:', 'printess-editor' ) . "&nbsp;" . $item->get_id() . '</div>';

		if ( printess_do_render_edit_link( $item ) ) {
			echo ' <a target=_blank href="' . esc_url( $url ) . '">' . esc_html__( 'Edit Customer Design', 'printess-editor' ) . '</a>';
		}
	}

	printess_render_dropship_table( $printess_tracking_id, $printess_tracking_url, $printess_shipping_status );

	if ( ! empty( $printess_result ) ) {
		$json_result = json_decode( $printess_result, true );

		if ( $json_result['isFailure'] ) {
			echo '<p>' . esc_html__( 'Printess Error Details', 'printess-editor' ) . ': ' . esc_html( $json_result['failureDetails'] ) . '</p>';
		} else {
			$document_links = $json_result['result']['r'];
			$image_links    = $json_result['result']['p'];

			if ( isset( $document_links ) && ! empty( $document_links ) ) {
				echo '<p>' . esc_html__( 'Printess Production Files', 'printess-editor' ) . ':</p>';

				foreach ( $document_links as $document_name => $download_url ) {
					echo '<a href=' . esc_attr( $download_url ) . ' target=_blank>' . esc_html( $document_name ) . '</a>&nbsp;&nbsp;';
				}
			}

			if ( isset( $image_links ) && ! empty( $image_links ) ) {
				echo '<p>' . esc_html__( 'Printess Production Files', 'printess-editor' ) . ':</p>';

				foreach ( $image_links as $item ) {
					echo '<a href=' . esc_attr( $item['u'] ) . ' target=_blank>' . esc_html( $item['d'] ) . ' - Page ' . esc_html( $item['i'] + 1 ) . '</a>&nbsp;&nbsp;';
				}
			}
		}
	} elseif ( ! empty( $printess_save_token ) ) {
		echo '<p>' . esc_html__( 'Printess Production Files', 'printess-editor' ) . ':</p>';

		$approval_mode = PrintessAdminSettings::get_approval_mode();

		if ( 'manual' === $approval_mode && empty( $printess_job_id ) ) {
			$url = add_query_arg(
				array(
					'action'   => 'printess_approve_order_line_item',
					'order_id' => $order_id,
					'item_id'  => $item_id,
					'pst'      => $printess_save_token,
					'nonce'    => wp_create_nonce( 'printess_approve_order_line_item' ),
				),
				home_url()
			);

			echo '<a href="' . esc_url( $url ) . '">' . esc_attr__( 'Approve and send to production.', 'printess-editor' ) . '</a>';
		} else {
			echo '<span>' . esc_attr__( 'Processing.', 'printess-editor' ) . '</span>';
		}
	}

	if(null !== $form_fields && !empty($form_fields)) {
		$form_fields = json_decode($form_fields, true);

		if(null !== $form_fields && is_array($form_fields) && count($form_fields) > 0) {
			echo '<p>' . esc_html__( 'Printess Form Fields', 'printess-editor' ) . ':</p>';
			try {
?>
				<table cellspacing="0" class="display_meta">
					<thead>
						<tr>
							<th><?php echo esc_html__( 'Form field', 'printess-editor' ) ?></th>
							<th><?php echo esc_html__( 'Value', 'printess-editor' ) ?></th>
						</tr>
					</thead>
					<tbody>
<?php
					foreach($form_fields as $key => $value ) {
?>
						<tr>
							<td><?php echo esc_html($key) ?></td>
							<td><?php echo esc_html($value) ?></td>
						</tr>
<?php
					}
?>
					</tbody>
				</table>
<?php
			} catch(\Exception $ex) {
			}
		}
	}
}

	/**
	 * Renders the Printess options page.
	 */
function printess_options_page_html() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<div>
	<?php echo esc_html__( 'Please copy & paste the shop and service tokens from your', 'printess-editor' ); ?>
			<a href="https://account.printess.com/#account" target=_blank><?php echo esc_html__( 'account page', 'printess-editor' ); ?></a>.
		</div>
		<form action="options.php" method="post">
	<?php
	settings_fields( 'printess-settings' );
	do_settings_sections( 'printess-settings' );
	submit_button( __( 'Save Settings', 'printess-editor' ) );
	?>
		</form>
	</div>
	<?php
}

// TODOS:
// add edit buyer/admin link (also entweder als buyer öffnen oder als admin) .


/**
 * Adds the edit link in the cart.
 *
 * @param mixed $cart_item     The cart item.
 * @param mixed $cart_item_key The cart item key.
 */
function printess_after_cart_item_name( $cart_item, $cart_item_key ) {
	if ( empty( $cart_item['printess-save-token'] ) ) {
		return;
	}

	$product             = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
	$printess_save_token = $cart_item['printess-save-token'];

	if ( $product && $product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
		$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
		$product_permalink = add_query_arg( 'qty', $cart_item['quantity'], add_query_arg( 'printess-save-token', $printess_save_token, $product_permalink ) );

		if(array_key_exists("design_name", $cart_item) && null !== $cart_item['design_name'] && !empty($cart_item['design_name'])){
			$product_permalink = add_query_arg("design_name", $cart_item['design_name'], $product_permalink);
		} else if(array_key_exists("printess-design-name", $cart_item) && null !== $cart_item["printess-design-name"] && !empty($cart_item["printess-design-name"])){
			$product_permalink = add_query_arg("design_name", $cart_item["printess-design-name"], $product_permalink);
		}

		?>
		<span> - </span><a class="printess-edit-link" href="<?php echo esc_url( $product_permalink ); ?>"><?php echo esc_html__( 'Edit', 'printess-editor' ); ?></a>
		<?php
	}
}

/**
	* Sets the marker for an edited Printess product in the cart.
	*
	* @param mixed $cart The cart.
*/
function printess_cart_loaded_from_session( $cart ) {
	$cart_contents             = $cart->cart_contents;
	$edited_save_tokens        = array();
	$needle                    = 'printess-save-token-to-remove-from-cart';
	$update_cart               = false;
	$remove_option_value       = get_option( 'printess_show_original_product_in_basket', true );
	$sort_order                = array();

	$index = 0;
	foreach ( $cart_contents as $key => &$value ) {
		if ( isset( $value[ $needle ] ) && ! empty( $value[ $needle ] ) ) {
			$edited_save_tokens[] = $value[ $needle ];
		}

		$sort_order[ $key ] = $index;
		++$index;
	}

	foreach ( $cart_contents as $cart_item_id => $cart_item ) {
		if ( ! isset( $cart_item['printess-was-edited'] ) && isset( $cart_item['printess-save-token'] ) && in_array( $cart_item['printess-save-token'], $edited_save_tokens, true ) ) {
			$cart_item['printess-was-edited']          = 1;
			WC()->cart->cart_contents[ $cart_item_id ] = $cart_item;
			$update_cart                               = true;
		}
	}

	// Sort the cart items to newest item first.
	$sorted_cart_contents = $cart_contents; // $b will be a different array.
	usort(
		$sorted_cart_contents,
		function ( $a, $b ) {
			if ( array_key_exists( 'printess_date_added', $a ) && array_key_exists( 'printess_date_added', $b ) ) {
				return strcmp( $a['printess_date_added'], $b['printess_date_added'] ) * -1;
			} elseif ( array_key_exists( 'printess_date_added', $a ) && ! array_key_exists( 'printess_date_added', $b ) ) {
				return -1;
			} elseif ( ! array_key_exists( 'printess_date_added', $a ) && array_key_exists( 'printess_date_added', $b ) ) {
				return 1;
			} else {
				return $sort_order[ $a->id ] < $sort_order[ $b->id ] ? -1 : 1;
			}
		}
	);

	$index              = 0;
	$sort_order_changed = false;

	foreach ( $sorted_cart_contents as $cart_item_id => $cart_item ) {
		if ( $sort_order[ $cart_item['key'] ] !== $index ) {
			$sort_order_changed = true;
		}

		++$index;
	}

	if ( true === $sort_order_changed ) {
		$update_cart   = true;
		$cart_contents = array();

		foreach ( $sorted_cart_contents as $cart_item ) {
			$cart_contents[ $cart_item['key'] ] = $cart_item;
		}

		$cart->cart_contents = $cart_contents;
	}

	if ( $update_cart ) {
		WC()->cart->set_session();
	}
}

/**
 * Returns an associative array containing all available internal page names and their assoziated title
 *
 * @param bool $get_id_mappings If set to true, the page id's are returned instead of names.
 */
function printess_get_available_pages( bool $get_id_mappings = false ) {
	$pages_lookup = array();

	$page_query = array(
		'sort_order'   => 'asc',
		'sort_column'  => 'post_title',
		'hierarchical' => 1,
		'exclude'      => '',
		'include'      => '',
		'meta_key'     => '',
		'meta_value'   => '',
		'authors'      => '',
		'child_of'     => 0,
		'parent'       => -1,
		'exclude_tree' => '',
		'number'       => '',
		'offset'       => 0,
		'post_type'    => 'page',
		'post_status'  => 'publish',
	);

	if ( ! $get_id_mappings ) {
		foreach ( get_pages( $page_query ) as &$post_page ) {
			$pages_lookup[ $post_page->post_name ] = $post_page->post_title;
		}
	} else {
		foreach ( get_pages( $page_query ) as &$post_page ) {
			$pages_lookup[ $post_page->post_name ] = $post_page->ID;
		}
	}

	return $pages_lookup;
}

/**
 * Callback that is called after items have been added to cart. Will redirect to different page if configured in product.
 *
 * @param string $redirect_url The redirect url.
 * @param mixed  $product The product.
 *
 * @return string The redirect url.
 */
function printess_add_to_cart_redirect( $redirect_url, $product ) {
	if ( isset( $product ) && method_exists( $product, 'get_meta' ) && !array_key_exists('printess_ignore_redirect', $_REQUEST) ) {
		$redirect_page = $product->get_meta( 'printess_cart_redirect_page', true );

		if ( null !== $redirect_page && '' !== $redirect_page ) {
			$pages = printess_get_available_pages( true );

			if ( array_key_exists( $redirect_page, $pages ) ) {
				$result = get_permalink( $pages[ $redirect_page ] );

				if ( false === $result ) {
					$redirect_url = site_url( $redirect_page );
				} else {
					$redirect_url = $result;
				}
			} else {
				$redirect_url = site_url( $redirect_page );
			}
		}
	}

	return $redirect_url;
}

	/**
	 * Add message to cart in case it's an edited Printess product.
	 * Will only show up if the customer edited a cart item and then added it to the cart.
	 *
	 * @param mixed $item_data The cart item data.
	 * @param mixed $cart_item The cart item.
	 */
function printess_get_item_data( $item_data, $cart_item ) {
	if ( isset( $cart_item['printess-design-name'] ) ) {
        $item_data[] = array(
            'key'     => __('Design Name', 'printess-editor'),
            'value'   => wc_clean( $cart_item['printess-design-name'] ),
            'display' => ''
        );
    }

	if ( ! isset( $cart_item['printess-was-edited'] ) ) {
		return $item_data;
	}

	?>
	<dl class="variation">
		<?php echo esc_html__( 'This item was edited. Consider removing it from your cart.', 'printess-editor' ); ?>
	</dl>
	<?php

	return $item_data;
}

	/**
	 * Show Printess product settings tab.
	 *
	 * @param mixed $tabs The tabs item.
	 */
function printess_product_settings_tabs( $tabs ) {
	$tabs['printess'] = array(
		'label'    => 'Printess',
		'target'   => 'printess_product_data',
		'class'    => array( 'show_if_simple', 'show_if_variable' ),
		'priority' => 21,
	);

	return $tabs;
}

	/**
	 * Configure the form field mappings that define which variant option values map to which form field values.
	 * and allow searching for one to set it.
	 */
function printess_render_form_field_mappings() {
	$attributes = array();

	$template_name = get_post_meta( get_the_ID(), 'printess_template', true );
	$product       = wc_get_product( get_the_ID() );

	foreach ( $product->get_attributes() as &$attribute ) {
		$attributes[ $product->get_name() ] = $attribute->get_options();
	}

	?>
	<style>
		.printess_autocomplete {
			box-sizing: border-box;
			font: 16px Arial;
			position: relative;
			display: inline-block;
			width: 300px;
			max-width: 95%;
			height: 30px;
		}

		.printess_autocomplete input {
			border: 1px solid transparent;
			background-color: #f1f1f1;
			padding: 10px;
			font-size: 16px;
			height: 30px;
		}

		.printess_autocomplete input[type=text] {
			background-color: #f1f1f1;
			width: 100%;
		}

		.printess_autocomplete_items {
			position: absolute;
			border: 1px solid #d4d4d4;
			border-bottom: none;
			border-top: none;
			z-index: 99;

			top: 100%;
			left: 0;
			right: 0;
		}


		.printess_autocomplete_items div {
			padding: 10px;
			cursor: pointer;
			background-color: #fff;
			border-bottom: 1px solid #d4d4d4;
		}

		.printess_autocomplete_items div:hover {
			background-color: #e9e9e9;
		}

		.printess_autocomplete_active {
			background-color: DodgerBlue !important;
			color: #ffffff;
		}
	</style>
	<div id="printess_form_field_mappings">
	</div>
	<script>
		function autocomplete(input, autocomplete) {
			var currentFocus;
			const fillList = function(parent, val) {
				a = document.createElement("DIV");
				a.setAttribute("id", this.id + "printess_autocomplete_list");
				a.setAttribute("class", "printess_autocomplete_items");

				parent.appendChild(a);

				for (i = 0; i < autocomplete.length; i++) {
					if(!val) {
						b = document.createElement("DIV");
						b.innerHTML += autocomplete[i];

						b.innerHTML += "<input type='hidden' value='" + autocomplete[i] + "'>";
						b.addEventListener("click", function(e) {
							input.value = this.getElementsByTagName("input")[0].value;
							closeAllLists();
						});

						a.appendChild(b);
					} else if (autocomplete[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {

						b = document.createElement("DIV");

						b.innerHTML = "<strong>" + autocomplete[i].substr(0, val.length) + "</strong>";
						b.innerHTML += autocomplete[i].substr(val.length);

						b.innerHTML += "<input type='hidden' value='" + autocomplete[i] + "'>";
						b.addEventListener("click", function(e) {
							input.value = this.getElementsByTagName("input")[0].value;
							closeAllLists();
						});

						a.appendChild(b);
					}
				}
			};

			input.addEventListener("input", function(e) {
				var a, b, i, val = this.value;

				closeAllLists();

			currentFocus = -1;

				fillList(this.parentNode, val);
			});

			input.addEventListener("keydown", function(e) {
					var x = document.getElementById(this.id + "printess_autocomplete_list");

					if (x) {
						x = x.getElementsByTagName("div");
					}

					if (e.keyCode == 40) {
						currentFocus++;
						addActive(x);
					} else if (e.keyCode == 38) {
						currentFocus--;
						addActive(x);
					} else if (e.keyCode == 13) {
						e.preventDefault();
						if (currentFocus > -1) {
							if (x) x[currentFocus].click();
						}
					}
			});

			input.addEventListener("click", function(e) {
				closeAllLists();

			currentFocus = -1;

				fillList(this.parentNode, "");
			});

			function addActive(x) {
				if (!x) {
					return false;
				}

				removeActive(x);
				if (currentFocus >= x.length) {
					currentFocus = 0;
				}

				if (currentFocus < 0) {
					currentFocus = (x.length - 1);
				}


				x[currentFocus].classList.add("printess_autocomplete_active");
			}

			function removeActive(x) {
				for (var i = 0; i < x.length; i++) {
					x[i].classList.remove("printess_autocomplete_active");
				}
			}

			function closeAllLists(elmnt) {
				var x = document.getElementsByClassName("printess_autocomplete_items");
				for (var i = 0; i < x.length; i++) {
					if (elmnt != x[i] && elmnt != input) {
						x[i].parentNode.removeChild(x[i]);
					}
				}
			}

			document.addEventListener("click", function (e) {
					closeAllLists(e.target);
			});
		}

		function printess_create_edit_control(id, key, placeHolder, autocompleteValues) {
			const productAttributes = <?php echo wp_json_encode( $attributes ); ?>;
			const wrapper = document.createElement("div");
			const input = document.createElement("input");


			wrapper.classList.add("printess_autocomplete");
			wrapper.appendChild(input);

			input.setAttribute("type", "text");
			input.setAttribute("id", "printess_autocomplete_id_" + id);
			input.setAttribute("data-key", key);
			input.setAttribute("name", "printess_ffmapping_" + id);
			input.setAttribute("placeholder", placeHolder || "");
			input.classList.add("short");


			if(autocompleteValues && autocompleteValues.length > 0) {
				autocomplete(input, autocompleteValues);
			}

			return wrapper;
		}

		const printessFFMappings = document.getElementById("printess_form_field_mappings");
		debugger;
		printessFFMappings.appendChild(printess_create_edit_control("field1", "key1", "placeholder", ["Value1", "Value2", "Value3", "Value4", "Value5", "Value6", "Value7"]));
	</script>
	<?php
}

	/**
	 * Show the Printess template name
	 * and allow searching for one to set it.
	 */
function printess_product_data_panels() {
	PrintessProductHelpers::render_product_option_pane();
}

/**
 * Updates the meta fields for the Printess settings.
 *
 * @param mixed $post_id The product id.
 */
function printess_process_product_meta( $post_id ) {
	$keys        = array( 'printess_template', 'printess_dropshipping', 'printess_merge_template_1', 'printess_merge_template_2', 'printess_merge_template_3', 'printess_output_type', 'printess_dpi', 'printess_cart_redirect_page', 'printess_custom_formfield_mappings', 'printess_jpg_compression', "printess_ui_version", "printess_output_files", "printess_item_usage", "printess_editor_theme", "printess_page_count_option" );
	$number_keys = array( 'printess_dpi' );

	foreach ( $keys as $key ) {
		$value = filter_input( INPUT_POST, $key );

		if ( isset( $value ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( $value ) );
		}
	}

	foreach ( $number_keys as $key ) {
		$value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_NUMBER_INT );

		if ( isset( $value ) ) {
			update_post_meta( $post_id, $key, $value );
		}
	}
}

/**
 * Adjusts the icon for Printess on the product configuration page.
 */
function printess_admin_head() {
		echo '<style> #woocommerce-product-data ul.wc-tabs li.printess_options.printess_tab a:before{ content: "\f487"; } </style>';
}

/**
 * Adjusts the overview pages to show "customize" instead of "add to cart"
 * for Printess products.
 * Changes the link to point to the product permalink.
 *
 * @param mixed $link    The link to the product.
 * @param mixed $product The WC product.
 * @param mixed $args    Arguments coming from the hook.
 */
function printess_adjust_add_to_cart( $link, $product, $args = null ) {
		global $product;

	if ( empty( $product->get_meta( 'printess_template', true ) ) ) {
		return $link;
	}

	if ( ! PrintessAdminSettings::get_show_customize_on_archive_page() ) {
		return null;
	}

		$class      = PrintessAdminSettings::get_customize_button_class() . ' button';
		$attributes = '';

	if (null !== $args && isset( $args['attributes'] ) ) {
		$attributes = wc_implode_html_attributes( $args['attributes'] );
	}

		/*
		Class könnte man übernehmen.. aber da steht dann auch ajax submit und so drinne, was es in den cart packt.
		if ( isset( $args['class'] ) ) {
		$class = esc_attr( $args['class'] );
		}
		*/

		return sprintf(
			'<a href="%s" class="%s" %s>%s</a>',
			esc_url( $product->get_permalink() ),
			$class,
			$attributes,
			__( 'Customize', 'printess-editor' )
		);
}

/**
 * Request handler to edit an Printess order line item.
 */
function printess_edit_order_line_item() {
	$action = filter_input( INPUT_GET, 'action' );
	$nonce  = filter_input( INPUT_GET, 'nonce' );
	$theme = PrintessAdminSettings::get_default_theme();

	if ( isset( $action ) && isset( $nonce ) && 'printess_edit_order_line_item' === $action && wp_verify_nonce( $nonce, 'printess_edit_order_line_item' ) ) {
		$order_id            = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
		$item_id             = filter_input( INPUT_GET, 'item_id', FILTER_SANITIZE_NUMBER_INT );
		$printess_save_token = filter_input( INPUT_GET, 'printess_save_token', FILTER_SANITIZE_SPECIAL_CHARS );
		$order               = new WC_Order( $order_id );
		$product             = null;

		foreach ( $order->get_items() as $item ) {
			if ( $item->get_id() === intval( $item_id ) ) {
				$product = $item->get_product();
				break;
			}
		}

		if ( is_null( $product ) || ! isset( $product ) ) {
			echo esc_html__( 'Could not find line item or product...', 'printess-editor' );
			die;
		}

		$productTheme = $product->get_meta( 'printess_editor_theme', true );

		if(null !== $productTheme && !empty($productTheme)) {
			$theme = $productTheme;
		}

		$product_json = printess_get_product_json( $product );

		wp_enqueue_style( 'printess-editor' );
		printess_load_externalscripts();

		$url = add_query_arg(
			array(
				'action'   => 'printess_save_edited_order_line_item',
				'order_id' => $order_id,
				'item_id'  => $item_id,
				'nonce'    => wp_create_nonce( 'printess_save_edited_order_line_item' ),
			),
			home_url()
		);

		$printess_ui_version = $product->get_meta( 'printess_ui_version', true);

		if(!isset($printess_ui_version) || empty($printess_ui_version)) {
			$printess_ui_version = "classical";
		}
		?>
	<html>
		<head>
		<script src="<?php echo plugins_url( 'includes/js/printessEditor.js', __FILE__ ); ?>"></script>
		<script src="<?php echo plugins_url( 'includes/js/printessWoocommerce.js', __FILE__ ); ?>"></script>
		<link rel="stylesheet" href="<?php echo plugins_url( 'printess.css', __FILE__ ); ?>">
		</head>
		<body>
			<script id="printess-integration">
				let showPrintessEditor = function() {
					const idsToHide = (<?php echo wp_json_encode( PrintessAdminSettings::get_ids_to_hide() ); ?> || "").split(",").map( (x) => x.trim());
					const classesToHide = (<?php echo wp_json_encode( PrintessAdminSettings::get_class_names_to_hide() ); ?> || "").split(",").map( (x) => x.trim());
					const product =  <?php echo wp_json_encode( $product_json ); ?>;
					const editor = typeof initPrintessWCEditor !== "undefined" ? initPrintessWCEditor({
																																																			"apiDomain:": <?php echo wp_json_encode( PrintessAdminSettings::get_domain() ); ?>,
																																																			"uiSettings": {
																																																				"startupLogoUrl": "",
																																																				"showStartupAnimation": true,
																																																				"editorUrl": <?php echo wp_json_encode( $printess_ui_version ); ?>,
																																																				"theme": <?php echo wp_json_encode( $theme ); ?>
																																																			},
																																																			"editorUrl": <?php echo wp_json_encode( PrintessAdminSettings::get_embed_html_url() ); ?>,
																																																			"shopToken": <?php echo wp_json_encode( PrintessAdminSettings::get_shop_token() ); ?>,
																																																			"hidePricesInEditor": true,
																																																			"editorVersion": "",
																																																			"shopMoneyFormat": "{{ shop.money_format }}",
																																																			"idsToHide": idsToHide,
																																																			"classesToHide": classesToHide,
																																																			"addToCartAfterCustomization": false,
																																																			"editorMode": "admin",
																																																			"userIsLoggedIn": <?php echo wp_json_encode( is_user_logged_in() ); ?>,
																																																			"nonce": <?php echo wp_json_encode( wp_create_nonce( 'wp_rest' ) ); ?>,
																																																			"customizeButtonClasses": <?php echo wp_json_encode( PrintessAdminSettings::get_customize_button_class() ); ?>,
																																																			"showPricesInEditor": false,
																																																			"cartUrl": <?php echo wp_json_encode( wc_get_cart_url() ) ?>
																																																		}) : null;

					if(!editor) {
						console.warn("Unable to initialize printess editor.");
						return;
					}

					const settings = {
						templateNameOrSaveToken: <?php echo wp_json_encode( $printess_save_token ); ?>,
						product: product,
						basektId: <?php echo wp_json_encode( uniqid( '', true ) ); ?>,
						userId: <?php echo wp_json_encode( $order->get_user_id() ); ?>,
						optionValueMappings: <?php echo wp_json_encode( $product->get_meta( 'printess_custom_formfield_mappings', true ) ); ?>,
						legalText: <?php echo wp_json_encode( PrintessAdminSettings::get_legal_text() ); ?>
					};

					editor.show(settings);
				};

				document.addEventListener("DOMContentLoaded", showPrintessEditor);
			</script>
			<span id="printess-saving-message" style="display:none;"><?php echo esc_html__( 'Saving design and redirecting back to order detail page...', 'printess-editor' ); ?></span>
			<span id="printess-loading-message"><?php echo esc_html__( 'Loading editor...', 'printess-editor' ); ?></span>
			<a style="display: none;" id="printess-admin-save" href="<?php echo esc_url( $url ); ?>"></a>
		</body>
	</html>
		<?php
			die;
	}
}

				/**
				 * Request handler to edit an Printess order line item.
				 */
function printess_save_edited_order_line_item() {
	$action = filter_input( INPUT_GET, 'action' );
	$nonce  = filter_input( INPUT_GET, 'nonce' );

	if ( isset( $action ) && isset( $nonce )
		&& 'printess_save_edited_order_line_item' === $action
		&& wp_verify_nonce( $nonce, 'printess_save_edited_order_line_item' )
	) {

			$order_id               = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
			$line_item_id           = filter_input( INPUT_GET, 'item_id', FILTER_SANITIZE_NUMBER_INT );
			$printess_save_token    = filter_input( INPUT_GET, 'pst', FILTER_SANITIZE_SPECIAL_CHARS );
			$printess_thumbnail_url = filter_input( INPUT_GET, 'ptu', FILTER_SANITIZE_URL );

			$order         = new WC_Order( $order_id );
			$item          = $order->get_item( $line_item_id );
			$approval_mode = PrintessAdminSettings::get_approval_mode();

		if ( 'manual' === $approval_mode ) {
			$item->delete_meta_data( '_printess-job-id' );
		} else {
			$order_items = array( $line_item_id => $item );
			printess_handle_order_items( $order, $order_items );
		}

			$item->delete_meta_data( '_printess-result' );
			$item->update_meta_data( '_printess-save-token', $printess_save_token, true );
			$item->update_meta_data( '_printess-thumbnail-url', $printess_thumbnail_url, true );
			$item->save_meta_data();

		$query_string = http_build_query(
			array(
				'post'   => $order_id,
				'action' => 'edit',
			)
		);

			$redirect = admin_url( 'post.php?' . $query_string );
			wp_safe_redirect( $redirect );
			die;
	}
}

/**
 * Request handler to approve an Printess order line item.
 */
function printess_approve_order_line_item() {
	$action = filter_input( INPUT_GET, 'action' );
	$nonce  = filter_input( INPUT_GET, 'nonce' );

	if ( isset( $action ) && isset( $nonce ) && 'printess_approve_order_line_item' === $action && wp_verify_nonce( $nonce, 'printess_approve_order_line_item' ) ) {
									$order_id            = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
									$line_item_id        = filter_input( INPUT_GET, 'item_id', FILTER_SANITIZE_NUMBER_INT );
									$printess_save_token = filter_input( INPUT_GET, 'pst', FILTER_SANITIZE_SPECIAL_CHARS );
									$order               = new WC_Order( $order_id );
									$item                = $order->get_item( $line_item_id );

									$order_items = array( $line_item_id => $item );
									printess_handle_order_items( $order, $order_items );

									$item->delete_meta_data( '_printess-result' );
									$item->save_meta_data();

									$query_string = http_build_query(
										array(
											'post'   => $order_id,
											'action' => 'edit',
										)
									);

			$redirect = admin_url( 'post.php?' . $query_string );
			wp_safe_redirect( $redirect );
			die;
	}
}

function printess_update_save_token() {
	$action = filter_input( INPUT_GET, 'action' );
	$nonce  = filter_input( INPUT_GET, 'nonce' );
	$order_id = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
	$line_item_id = filter_input( INPUT_GET, 'item_id', FILTER_SANITIZE_NUMBER_INT );
	$printess_save_token = filter_input( INPUT_GET, 'pst', FILTER_SANITIZE_SPECIAL_CHARS );

	if (current_user_can('edit_shop_orders') && isset( $action ) && isset( $nonce ) && null !== $printess_save_token && !empty($printess_save_token) && 'printess_update_save_token' === $action && wp_verify_nonce( $nonce, 'printess_update_save_token' . $order_id . "_" . $line_item_id ) ) {
		$order = new WC_Order( $order_id );
		$item = $order->get_item( $line_item_id );

		$save_token = $item->get_meta('_printess-save-token');
		$original_save_token = $item->get_meta('_printess-original-save-token');

		if  ((null === $printess_save_token || empty($printess_save_token)) || (null !== $printess_save_token && !empty($printess_save_token) && null !== $original_save_token && !empty($original_save_token) && $printess_save_token === $original_save_token)) {
			$query_string = http_build_query(array(
				'post'   => $order_id,
				'action' => 'edit',
			));

			$redirect = admin_url( 'post.php?' . $query_string );
			wp_safe_redirect( $redirect );
			die;
		}

		if(null === $save_token || empty($save_token)) {
			$save_token = $printess_save_token;
		}

		if(null === $original_save_token || empty($original_save_token)) {
			wc_update_order_item_meta($item->get_id(), '_printess-original-save-token', $save_token);
		}

		wc_update_order_item_meta($item->get_id(), '_printess-save-token', $printess_save_token);

		$query_string = http_build_query(array(
											'post'   => $order_id,
											'action' => 'edit',
										));

		$redirect = admin_url( 'post.php?' . $query_string );
		wp_safe_redirect( $redirect );
		die;
	}
}


/**
 * Adds a custom field to the variations view of the admin side.
 * Used to assign a different Printess template to this variation.
 *
 * @param mixed $loop           .
 * @param mixed $variation_data .
 * @param mixed $variation      .
 */
function printess_add_custom_field_to_variations( $loop, $variation_data, $variation ) {
	woocommerce_wp_text_input(
		array(
			'id'    => 'printess_template_name[' . $loop . ']',
			'class' => 'short',
			'label' => __( 'Printess Template', 'printess-editor' ),
			'value' => get_post_meta( $variation->ID, 'printess_template_name', true ),
		)
	);
}

/**
 * Save the variation data for the custom field.
 *
 * @param mixed $variation_id .
 * @param mixed $i            .
 */
function printess_save_custom_field_variations( $variation_id, $i ) {
	$template_names = filter_input( INPUT_POST, 'printess_template_name', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

	if ( ! isset( $template_names ) ) {
										return;
	}

	$template_name = $template_names[ $i ];

	if ( isset( $template_name ) ) {
		update_post_meta( $variation_id, 'printess_template_name', esc_attr( $template_name ) );
	}
}


/**
 * This is the data written to the variations json on the WC product page.
 *
 * @param mixed $variations .
 */
function printess_add_custom_field_variation_data( $variations ) {
	$variations['printess_template_name'] = get_post_meta( $variations['variation_id'], 'printess_template_name', true );

	return $variations;
}

/**
 * Adds the new acccount menu entry for saved designs
 *
 * @param mixed $content The list of my account entries.
 * @return mixed new modified array of my account menu items.
 */
function printess_add_my_account_menu_item( $content ) {
	if ( get_option( 'printess_enable_design_save', false ) ) {
										$logout = $content['customer-logout'];

										unset( $content['customer-logout'] );

										$content['printess_saved_designs'] = esc_html__( 'Saved Designs', 'printess-editor' );

										$content['customer-logout'] = $logout;
	}

			return $content;
}

/**
 * Returns the valid query vars that are used by this plugin
 *
 * @param mixed $vars The already used valid query variables.
 * @return mixed A list of all valid query param names.
 */
function printess_item_query_vars( $vars ) {
	$vars[] = 'printess_saved_designs';
	$vars[] = 'printess_search';
	$vars[] = 'printess_page';
	$vars[] = 'printess_delete';
	$vars[] = 'redirect_url';

	return $vars;
}

/**
 * Returns the id of the current logged in user
 *
 * @return int the current user id.
 */
function printess_get_current_user_id() {
	if ( ! function_exists( 'wp_get_current_user' ) ) {
										return 0;
	}
			$user = wp_get_current_user();
			return ( isset( $user->ID ) ? (int) $user->ID : 0 );
}

/**
 * Creates a new encrypted url token
 *
 * @param int $product_id The product id.
 *
 * @return string The encrypted url token
 */
function printess_create_url_token( int $product_id ): string {
	$dt = new DateTime();
	$dt->add( new DateInterval( 'P1D' ) );
	$dt->setTimezone( new DateTimeZone( 'UTC' ) );
	$valid_until = $dt->format( 'Y-m-d H:i:s' );

	include_once 'includes/printess-tools.php';

	return PrintessTools::encrypt(
		wp_json_encode(
			array(
				'valid_until' => $valid_until,
				'product_id'  => $product_id,
			)
		)
	);
}

/**
 * Validates the given encrypted token
 *
 * @param string $encrypted The encrypted value.
 * @param int    $product_id The product id.
 *
 * @return bool true in case the token is valid
 */
function printess_validate_url_token( string $encrypted, int $product_id ): bool {
	try {
		include_once 'includes/printess-tools.php';

		$arr = json_decode( PrintessTools::decrypt( $encrypted ), true );

		if ( false !== $arr && array_key_exists( 'valid_until', $arr ) && ! empty( $arr['valid_until'] ) && array_key_exists( 'product_id', $arr ) && ! empty( $arr['product_id'] ) ) {
			if ( new DateTime( $arr['valid_until'] ) > new DateTime() && intval( $arr['product_id'] ) === intval( $product_id ) ) {
				return true;
			}
		}
	} catch ( Exception $ex ) {
		return false;
	}

	return false;
}

/**
 * Always returns a value when accessing assoz. array members that doe not exist without throwing error,
 *
 * @param mixed  $arr The array.
 * @param string $key The array key.
 * @param string $default_value The default value that should be returned.
 *
 * @return mixed A valeu even if the given key does not exists inside the array.
 */
function printess_get_value_from_array( $arr, $key, $default_value = '' ) {
	if ( isset( $arr ) && isset( $key ) && ! empty( $key ) && array_key_exists( $key, $arr ) ) {
										return $arr[ $key ];
	}

			return $default_value;
}

/***
 * Returns a lookup for product option names and the correponding slug.
 * @param mixed $product The woocmmerce product object (wp post).
 *
 * @return array Associative array that can be used as lookup for product option name -> slug
 */
function printess_get_product_options_and_slugs($product) {
	$productAttributes = printess_get_product_attributes($product);
	$ret = array();

	foreach ( $product->get_attributes() as $key => $attribute ) {
		if(is_string($attribute)) {
			if(array_key_exists($key, $productAttributes) ) {
				$ret[$attribute] = $key;
			}
		} else {
			if(method_exists($attribute, "get_data")) {
				$data = $attribute->get_data();

				if(array_key_exists("name", $data)) {
					$ret[$data["name"]] = $key;
				}
			}
		}
	}

	return $ret;
}

/**
 * Returns all attributes related to a product (parent product in case of variation) !!Important: does not map global product attribute keys / values
 */
function printess_get_product_attributes($product) {
	$ret = array();

	$parent_id = $product->get_data()["parent_id"];#

	if($parent_id > 0) {
		$product = wc_get_product( $parent_id );
	}

	if(isset($product) && false !== $product) {
		foreach ( $product->get_attributes() as $key => $value ) {
			$ret[ $key ] = array(
				'key'    => $key,
				'name'   => $value['name'],
				'values' => $value['options'],
			);
		}
	}

	return $ret;
}

/**
 * Saves a design after login in case the user has not been logged in before
 *
 * @param mixed $redirect The redirect url that should be called afterwards.
 * @param mixed $user The current logged in user.
 * @return string The new redirect url.
 */
function printess_redirect_after_login( $redirect, $user = null ) {

	try {
		$product_id    = printess_get_value_from_array( $_GET, 'productId' );
		$save_token    = printess_get_value_from_array( $_GET, 'saveToken' );
		$thumbnail_url = printess_get_value_from_array( $_GET, 'thumbnailUrl' );
		$variant_id    = printess_get_value_from_array( $_GET, 'variantId' );
		$options       = printess_get_value_from_array( $_GET, 'options' );
		$token         = printess_get_value_from_array( $_GET, 'token' );
		$display_name  = printess_get_value_from_array( $_GET, 'displayName' );
		$user_id       = null;

		if ( null !== $user ) {
			$user_id = isset( $user->ID ) ? (int) $user->ID : -1;
		}

		if ( $user_id < 1 ) {
			$user_id = printess_get_current_user_id();
		}

		if ( $user_id > 0 && null !== $save_token && ! empty( $save_token ) && null !== $thumbnail_url && ! empty( $thumbnail_url ) && null !== $display_name && ! empty( $display_name ) ) {
			if ( null !== $token && ! empty( $token ) && printess_validate_url_token( $token, intVal( $product_id ) ) ) {
				include_once 'includes/printess-saved-design-repository.php';

				$product = wc_get_product( intval( $product_id ) );

				if ( ! isset( $product ) || false === $product ) {
					return $redirect;
				}

				$repo = new Printess_Saved_Design_Repository();

				printess_unexpire_save_token( $save_token, printess_create_new_unexpiration_date() );

				$product_options = json_decode( stripslashes( $options ), true );
				$product_attributes = printess_get_product_options_and_slugs($product);
				$variant_options = array();

				if ( isset( $product_options ) && !empty($options) ) {
					foreach ( $product_options as $key => $value ) {
						if ( strpos( $key, 'attribute_' ) === 0 ) {
							$variant_options[$key] = $value;
						} else if(array_key_exists($key, $product_attributes)) {
							$variant_options["attribute_" . $product_attributes[$key]] = $value;
						}
					}
				}

				$design_id = $repo->add_design( $user_id, $save_token, $thumbnail_url, intval( '' . $product_id ), $product->get_data()['name'], $display_name, $variant_options);

				$design = $repo->get_design( $user_id, $design_id );

				$product_id  = $design['productId'];
				$product     = wc_get_product( intval( '' . $product_id ) );
				$permalink   = $product->get_permalink();
				$product_url = add_query_arg( 'printess-save-token', $design['saveToken'], $permalink );
				$product_url = add_query_arg( 'design_id', '' . $design['id'], $product_url );

				if(null !== $display_name && !empty($display_name)) {
					$product_url = add_query_arg( 'design_name', '' . $display_name, $product_url );
				}

				foreach ( $variant_options as $key => $value ) {
					$product_url = $product_url . '&' . rawurlencode( $key ) . '=' . rawurlencode( $value );
				}

				return $product_url;
			}
		}
	} catch ( Exception $ex ) {
		return $redirect;
	}

	return $redirect;
}

/**
 * Renders the account view for saved designs
 */
function printess_render_saved_designs() {
	global $wp;
	global $wp_query;

	$customer_id      = printess_get_current_user_id();
	$current_search   = '';
	$current_page     = 1;
	$per_page         = 20;
	$query_params     = array();
	$delete_design_id = -1;

	// Render css.
	wp_enqueue_style( 'printess-editor' );

	if ( null === $customer_id || $customer_id < 1 ) {
		?>
			<span class="printess_error"><?php echo esc_html__( 'Not logged in', 'printess-editor' ); ?></span>
		<?php
			return;
	}

	if ( null !== $wp_query && null !== $wp_query->query ) {
		$query_params = $wp_query->query;

		if ( array_key_exists( 'printess_page', $wp_query->query ) && '' !== $wp_query->query['printess_page'] ) {
				$current_page = max( intval( $wp_query->query['printess_page'] ), 1 );
		}

		if ( array_key_exists( 'printess_search', $wp_query->query ) && '' !== $wp_query->query['printess_search'] ) {
			$current_search = '' . $wp_query->query['printess_search'];
		}

		if ( array_key_exists( 'printess_delete', $wp_query->query ) && '' !== $wp_query->query['printess_delete'] ) {
			$delete_design_id = max( intval( $wp_query->query['printess_delete'] ), 0 );

			unset( $wp_query->query['printess_delete'] );
		}
	}

	if ( array_key_exists( 'printess_delete', $wp->query_vars ) ) {
		unset( $wp->query_vars['printess_delete'] );
	}

	$current_url = add_query_arg( $wp->query_vars, home_url( $wp->request ) );

	include_once 'includes/printess-saved-design-repository.php';

	$repo = new Printess_Saved_Design_Repository();

	if ( $delete_design_id > 0 ) {
		$repo->delete_design( $customer_id, $delete_design_id );
	}

	$saved_designs = $repo->get_designs( $customer_id, $current_search, $current_page, $per_page );

	if ( null === $saved_designs || ( count( $saved_designs ) < 1 && empty( $current_search ) && $current_page < 2 ) ) {
		?>
										<span class="printess_error"><?php echo esc_html__( 'No saved designs yet', 'printess-editor' ); ?></span>
			<?php
				return;
	}

	?>
	<form action="<?php echo esc_attr( $current_url ); ?>" method="get" name="printess_search_form">
		<?php
		foreach ( $query_params as $key => $value ) {
			if ( 'printess_search' !== $key ) {
				?>
						<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>">
					<?php
			}
		}
		?>

		<div class="printess_search_bar">
			<div class="printess_search_input">
				<label for="printess_search" class=""><?php esc_html__( 'Saved Designs', 'printess-editor' ); ?></label>
				<span class="woocommerce-input-wrapper"><input type="text" class="input-text " name="printess_search" placeholder="<?php echo esc_html__( 'Search for name', 'printess-editor' ); ?>" value="<?php echo esc_attr( $current_search ); ?>" autocomplete="given-name" ></span>
			</div>
			<button type="submit" class="button wp-element-button" name="apply_search" value="Search"><?php echo esc_html__( 'Search', 'printess-editor' ); ?></button>
		</div>
	</form>

	<?php
	include_once 'includes/printess-table.php';

	$table = new PrintessTable();
	$table->add_column( array( esc_html__( 'Thumbnail', 'printess-editor' ), esc_html__( 'Display name', 'printess-editor' ), esc_html__( 'Product name', 'printess-editor' ), esc_html__( 'Available until', 'printess-editor' ), esc_html__( 'Edit', 'printess-editor' ), esc_html__( 'Delete', 'printess-editor' ) ) );

	foreach ( $saved_designs as &$design ) {
		$product_id  = $design['productId'];
		$product     = wc_get_product( intval( '' . $product_id ) );
		$permalink   = $product->get_permalink();
		$product_url = add_query_arg( 'printess-save-token', $design['saveToken'], $permalink );
		$product_url = add_query_arg( 'design_id', '' . $design['id'], $product_url );
		$product_url = add_query_arg( 'design_name', '' . $design['displayName'], $product_url );

		if ( array_key_exists( 'options', $design ) && null !== $design['options'] && '' !== $design['options'] ) {
			try {
				foreach ( $design['options'] as $key => $value ) {
					if ( strpos( $key, 'attribute_' ) === 0 ) {
						$product_url = $product_url . '&' . rawurlencode( $key ) . '=' . rawurlencode( $value );
					}
				}
			} catch ( \Exception $ex ) {
				// just ignore on error.
			}
		}

		$content = array(
			array(
				'thumbnail' => $design['thumbnailUrl'],
				'alt'       => 'Saved design thumbnail',
			),
			$design['displayName'],
			$design['productName'],
			( new \DateTime( $design['validUntil'] ) )->format( wc_date_format() ),
			array(
				'url'   => $product_url,
				'label' => esc_html__( 'Edit', 'printess-editor' ),
			),
			array(
				'url'   => add_query_arg( 'printess_search', $current_search, add_query_arg( 'printess_page', $current_page, add_query_arg( 'printess_delete', $design['id'], $current_url ) ) ),
				'label' => esc_html__( 'Delete', 'printess-editor' ),
			),
		);

		$table->add_row( $content );
	}

	echo $table->render( 'css_grid' );

	?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination printess-Pager">
			<?php
				$next_url     = add_query_arg( 'printess_search', $current_search, add_query_arg( 'printess_page', $current_page + 1, $current_url ) );
				$previous_url = add_query_arg( 'printess_search', $current_search, add_query_arg( 'printess_page', max( $current_page - 1, 1 ), $current_url ) );
			?>

			<?php if ( $current_page > 1 ) { ?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button wp-element-button printess-Button-previous" href="<?php echo esc_attr( $previous_url ); ?>"><?php echo esc_html__( 'Previous', 'printess-editor' ); ?></a>
			<?php } ?>

			<?php if ( 1 === $current_page && count( $saved_designs ) === $per_page ) { ?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button wp-element-button printess-Button-next" href="<?php echo esc_attr( $next_url ); ?>"><?php echo esc_html__( 'Next', 'printess-editor' ); ?></a>
			<?php } ?>
		</div>
	<?php
}

/**
 * Renders an Edit link inside the mini basket
 *
 * @param string $html The html content of the mioni basket item.
 * @param mixed  $cart_item The cart item that might contain a printess save token.
 *
 * @return string the modified html that now might contain a printess edit button.
 */
function printess_render_edit_button_before_mini_basket_buttons( $html, $cart_item ) {
	if ( array_key_exists( 'printess-save-token', $cart_item ) && ! empty( $cart_item['printess-save-token'] ) ) {
		$product_id   = $cart_item['product_id'];
		$variation_id = $cart_item['variation_id'];
		$permalink_id = is_null( $variation_id ) || $variation_id <= 0 ? $product_id : $variation_id;

		$edit_link = get_permalink( $permalink_id );
		$edit_link = add_query_arg( 'printess-save-token', $cart_item['printess-save-token'], $edit_link );

		if(array_key_exists("design_name", $cart_item) && null !== $cart_item['design_name'] && !empty($cart_item['design_name'])){
			$edit_link = add_query_arg("design_name", $cart_item['design_name'], $edit_link);
		} else if(array_key_exists("printess-design-name", $cart_item) && null !== $cart_item["printess-design-name"] && !empty($cart_item["printess-design-name"])){
			$product_permalink = add_query_arg("design_name", $cart_item["printess-design-name"], $product_permalink);
		}

		return $html . '<a class="printess-edit-link" href="' . esc_attr( $edit_link ) . '" style="z-index: 99; background: transparent;" >' . esc_html__( 'Edit', 'printess-editor' ) . '</a>';
	}

	return $html;
}

/**
 * Renders a script that is moving url query params into the form submit action. (Required for woodman theme)
 */
function printess_render_login_script() {
	?>
	<script>
		function printess_modify_login_form_paramters() {
			try {
				const urlParams = new URLSearchParams(window.location.search);
				const saveToken = urlParams.get('saveToken');

				if(saveToken) {
					let form = document.querySelector(".login.woocommerce-form.woocommerce-form-login");
					let query = "saveToken=" + encodeURIComponent(saveToken);

					if(urlParams.get('thumbnailUrl')) {
						query += "&thumbnailUrl=" + encodeURIComponent(urlParams.get('thumbnailUrl'));
					}

					if(urlParams.get('token')) {
						query += "&token=" + encodeURIComponent(urlParams.get('token'));
					}

					if(urlParams.get('displayName')) {
						query += "&displayName=" + encodeURIComponent(urlParams.get('displayName'));
					}

					if(urlParams.get('productId')) {
						query += "&productId=" + encodeURIComponent(urlParams.get('productId'));
					}

					if(urlParams.get('variantId')) {
						query += "&variantId=" + encodeURIComponent(urlParams.get('variantId'));
					}

					if(urlParams.get('options')) {
						query += "&options=" + encodeURIComponent(urlParams.get('options'));
					}

					if(form && form.length > 0) {
						const formUrl = form.getAttribute("action");

						form.setAttribute("action", formUrl.indexOf("?") > -1 ? (formUrl + "&" + query) : (formUrl + "?" + query));
					}

					let retries = 0;
					const applyRegisterForm = function() {
						form = document.querySelector(".woocommerce-form.woocommerce-form-register.register");

						if(form && form.length > 0) {
							const formUrl = form.getAttribute("action");

							form.setAttribute("action", formUrl.indexOf("?") > -1 ? (formUrl + "&" + query) : (formUrl + "?" + query));
						} else {
							if(retries < 20) {
								retries ++;

								setTimeout(() => {
									applyRegisterForm();
								}, 500);
							}
						}
					};

					applyRegisterForm();
				}
			} catch(e) {

			}
		}


		printess_modify_login_form_paramters();
	</script>
	<?php
}

/**
 * Renders a script that is moving url query params into the form submit action. (Required for woodman theme)
 */
function printess_on_after_login_form() {
	ob_start();

	printess_render_login_script();

	$output = ob_get_contents();

	ob_end_clean();

	echo str_replace( "\n", '', str_replace( "\r\n", '', $output ) );
}

/**
 * Inputs helper scripts / css in front of the woodmart mini basket
 */
function printess_insert_helper_script_before_minibasket() {
	?>
	<style>
		.woocommerce-mini-cart-item.mini_cart_item > .wd-fill {
		inset: 0 0 40px 0;
		}
	</style>
	<?php
}

/**
 * WooCommerce callback for price recalculations in case of itemUsage params
 */
function printess_recalculate_basketitem_price($cart_object) {
	foreach ( $cart_object->get_cart() as $id => $basket_item ) {
		if(array_key_exists("printess-item-usage", $basket_item) && !empty($basket_item["printess-item-usage"])) {
			try {
				$used_items = json_decode($basket_item["printess-item-usage"], true);

				if(null === $used_items || !is_array($used_items)) {
					continue;
				}
				$product = wc_get_product($basket_item["product_id"] );
				$variant = null;
				$price = 0;

				if(null === $product) {
					continue;
				}

				if(0 < $basket_item["variation_id"]) {
					$variant = wc_get_product($basket_item["variation_id"]);
					$price = $variant->get_price();
				} else {
					$price = $product->get_price();
				}

				$configuration = $product->get_meta( 'printess_item_usage', true );

				if(null !== $configuration && !empty($configuration)) {
					$configuration = json_decode($configuration, true);

					if(null === $used_items || !is_array($used_items)) {
						continue;
					}
				}

				$priceLookup = array();
				foreach($configuration as $key => $value) {
					if(is_array($value) && array_key_exists("formFieldName", $value) && array_key_exists("pricePerUsage", $value)) {
						$priceLookup[$value["formFieldName"]] = $value["pricePerUsage"];
					} else if (is_numeric($value)) {
						$priceLookup[$key] = $value;
					}
				}

				foreach($used_items as $key => $value) {
					if(array_key_exists($key, $priceLookup)) {
						$price = $price + (intval($value) * $priceLookup[$key]);
					}
				}

				$basket_item["data"]->set_price($price);
			}
			catch(\Exception $ex) {

			}
		}
	}
}

/**
 * Enqueues external javascripts
 */
function printess_load_externalscripts() {
	wp_enqueue_script( 'printess_editor_core', plugins_url( 'includes/js/printessEditor.js', __FILE__ ), array(), 1, array( 'in_footer' => true ) );
	wp_enqueue_script( 'printess_editor_woo', plugins_url( 'includes/js/printessWoocommerce.js', __FILE__ ), array(), 1, array( 'in_footer' => true ) );
}

/**
 * Add quantity url parameetr support to product page
 */
function printess_woocommerce_quantity_input_args($args) {
    if ( isset( $_GET['qty'] ) && is_numeric($_GET['qty']) ) {
        $args['input_value']    = $_GET['qty'];
    }
    return $args;
}

/**
 * Adds the design name to the order email
 */
function printess_woocommerce_email_order_line_item_meta_fields($item_id, $item, $order) {
	if ( ! is_wc_endpoint_url() && $item->is_type('line_item') ) {
		if ( isset( $item['printess-design-name'] ) ) {
			?>
			<div><?php echo __( "Design Name", "printess-editor" ) ?>: <?php echo __($item['printess-design-name']) ?></div>

			<?php
			// $fields[] = array(
			// 	'key'     => __('Design Name', 'printess-editor'),
			// 	'value'   => wc_clean( $order['printess-design-name'] ),
			// 	'display' => ''
			// );
		}
	}
}

function printess_add_helper_scripts_after_cart() {
	echo "<script>
		document.addEventListener('DOMContentLoaded', function() {
			alert('Du held');
		});
	</script>";
}

/*
	Make sure that all Printess products that are added to the cart are having a save token.
*/
function printess_validate_cart_item($validation, $product_id, $dummy1 = 1, $dummy2 = 2, $dummy3 = 3) {
	$variation_id = $_REQUEST['variation_id'];
	$helper = new PrintessProductHelpers(null === $variation_id || empty($variation_id) ? $product_id : intval($variation_id) );
	$product_template_name = $helper->get_template_name(true);

	if($product_template_name) {
		$cart_save_token = filter_input( INPUT_POST, 'printess-save-token', FILTER_SANITIZE_SPECIAL_CHARS );

		if(null === $cart_save_token || empty($cart_save_token)) {
			wc_add_notice( __( 'You just added a personalized product without any personalization. Please personalize the product first.', 'printess-editor' ), 'error' );
    		$validation = false;
		}
	}

	return $validation;
}

/**
 * Registers the plugin hooks used for the Printess integration.
 */
function printess_register_hooks() {
	add_action( 'woocommerce_before_single_product', 'printess_show_printess_view_if_printess_product' );

	add_filter( 'woocommerce_loop_add_to_cart_link', 'printess_adjust_add_to_cart', 10, 3 );

	add_action( 'woocommerce_checkout_create_order_line_item', 'printess_add_save_token_to_order_items', 10, 3 );
	add_filter( 'woocommerce_get_item_data', 'printess_get_item_data', 10, 2 );

	// ORDERS.
	add_action( 'woocommerce_order_status_processing', 'printess_send_to_printess_api' );
	add_filter( 'woocommerce_hidden_order_itemmeta', 'printess_hide_order_item_meta_fields' );
	add_filter( 'woocommerce_admin_order_item_thumbnail', 'printess_admin_order_item_thumbnail', 10, 3 );
	add_action( 'woocommerce_after_order_itemmeta', 'printess_order_meta_customized_display', 10, 2 );
	add_action( 'woocommerce_order_details_before_order_table', 'printess_render_personalized_products_table', 10, 2 );
	//add_filter( 'woocommerce_email_order_meta_fields', 'printess_woocommerce_email_order_meta_fields', 10, 3 );
	add_action( 'woocommerce_order_item_meta_start', 'printess_woocommerce_email_order_line_item_meta_fields', 10, 3 );

	add_action( 'init', 'printess_edit_order_line_item' );
	add_action( 'init', 'printess_save_edited_order_line_item' );
	add_action( 'init', 'printess_approve_order_line_item' );
	add_action( 'init', 'printess_update_save_token' );

	// CART.
	add_filter( 'woocommerce_add_cart_item_data', 'printess_add_cart_item_data', 10, 1 );
	add_filter( 'woocommerce_cart_item_thumbnail', 'printess_get_thumbnail', 10, 3 );
	add_action( 'woocommerce_after_cart_item_name', 'printess_after_cart_item_name', 10, 2 );
	add_action( 'woocommerce_cart_loaded_from_session', 'printess_cart_loaded_from_session', 10, 1 );
	add_filter( 'woocommerce_add_to_cart_redirect', 'printess_add_to_cart_redirect', 10, 2 );
	add_action( 'woocommerce_after_cart', 'printess_add_helper_scripts_after_cart' );
	add_filter('woocommerce_add_to_cart_validation', 'printess_validate_cart_item', 10, 2);

	// PRODUCT.
	add_action( 'admin_head', 'printess_admin_head' );
	add_filter( 'woocommerce_product_data_tabs', 'printess_product_settings_tabs' );
	add_action( 'woocommerce_product_data_panels', 'printess_product_data_panels' );
	add_action( 'woocommerce_process_product_meta', 'printess_process_product_meta' );

	// VARIATIONS.
	add_action( 'woocommerce_variation_options_pricing', 'printess_add_custom_field_to_variations', 10, 3 );
	add_action( 'woocommerce_save_product_variation', 'printess_save_custom_field_variations', 10, 2 );
	add_filter( 'woocommerce_available_variation', 'printess_add_custom_field_variation_data' );

	add_filter( 'wc_product_has_unique_sku', '__return_false', PHP_INT_MAX );

	// My account page.
	add_filter( 'woocommerce_account_menu_items', 'printess_add_my_account_menu_item', 99, 1 );
	add_filter( 'query_vars', 'printess_item_query_vars' );
	add_rewrite_endpoint( 'printess_saved_designs', EP_ROOT | EP_PAGES );
	add_action( 'woocommerce_account_printess_saved_designs_endpoint', 'printess_render_saved_designs' );

	// Login redirect.
	add_filter( 'woocommerce_login_redirect', 'printess_redirect_after_login', 10, 2 );
	add_filter( 'woocommerce_registration_redirect', 'printess_redirect_after_login', 10, 2 );
	add_action( 'woocommerce_login_form_end', 'printess_on_after_login_form' );

	// Woodmart Mini Basket.
	add_filter( 'woocommerce_widget_cart_item_quantity', 'printess_render_edit_button_before_mini_basket_buttons', 10, 2 );
	add_action( 'woocommerce_before_mini_cart_contents', 'printess_insert_helper_script_before_minibasket' );

	//Support for quantity parameter on product page
	add_filter( 'woocommerce_quantity_input_args', 'printess_woocommerce_quantity_input_args' ); // Simple products
	add_filter( 'woocommerce_available_variation', 'printess_woocommerce_quantity_input_args' ); // Variations

	//Recalculate basket item prices
	add_action( 'woocommerce_before_calculate_totals', 'printess_recalculate_basketitem_price' );

	//New theme block supports
	include_once('includes/printessBlockIntegrations.php');
	printess_register_block_hooks();

	// //Loading of template parts (Required for mini cart template :( )
	// add_filter("render_block_data", "printess_modify_template_code", 10, 2);
	// add_filter("render_block", "printess_modify_template_code_1", 10, 2);

	// CALLBACKS.
	add_action(
		'rest_api_init',
		function () {
					// POST https://woo:8890/wp-json/printess/v1/job/finished .
				register_rest_route(
					'printess/v1',
					'job/finished',
					array(
						'methods'             => 'POST',
						'callback'            => 'printess_post_custom_method',
						'permission_callback' => '__return_true',
					)
				);
		}
	);

	add_action(
		'rest_api_init',
		function () {
					// POST https://woo:8890/wp-json/printess/v1/order/status/changed .
				register_rest_route(
					'printess/v1',
					'order/status/changed',
					array(
						'methods'             => 'POST',
						'callback'            => 'printess_post_status_changed',
						'permission_callback' => '__return_true',
					)
				);
		}
	);

	add_action(
		'rest_api_init',
		function () {
					// POST https://woo:8890/wp-json/printess/v1/order/item/approve .
				register_rest_route(
					'printess/v1',
					'order/item/approve',
					array(
						'methods'             => 'POST',
						'callback'            => 'printess_post_order_item_approve',
						'permission_callback' => '__return_true',
					)
				);
		}
	);

	// Saved Design api methods.
	add_action(
		'rest_api_init',
		function () {
					// POST https://woo:8890/wp-json/printess/v1/design/add .
				register_rest_route(
					'printess/v1',
					'design/add',
					array(
						'methods'             => 'POST',
						'callback'            => 'printess_post_add_design',
						'permission_callback' => '__return_true',
					)
				);
		}
	);

	// add_filter( 'woocommerce_order_item_thumbnail', function($x, $y) {
	// 	$debug = 10;
	// }, 10, 2 );

	// add_filter( 'woocommerce_order_item_name', function( $output_html, $item, $bool = false ) {
	// 	$debug = 10;
	// }, 10, 2 ); // Product name

	// Register style.
	wp_register_style( 'printess-editor', plugins_url( 'printess.css', __FILE__ ), array(), '1' );
	add_action( 'wp_enqueue_scripts', 'printess_load_externalscripts' );
}

/**
 * Initializes the plugin. Initializes the translations and makes sure the table for saved designs is created / updated
 */
function printess_init_plugin() {
	load_plugin_textdomain( 'printess-editor', false, 'printess-editor/languages/' );

	require_once 'includes/printess-saved-design-repository.php';

	$repo = new Printess_Saved_Design_Repository();
	$repo->install_or_update_db_table();
}

if ( in_array( $printess_global_plugin_path, wp_get_active_and_valid_plugins(), true ) || in_array( $printess_global_plugin_path, wp_get_active_network_plugins(), true ) ) {
	add_action( 'woocommerce_init', 'printess_register_hooks' );

	add_action( 'admin_menu', function() { PrintessAdminSettings::register_settings();} );

	add_action( 'plugins_loaded', 'printess_init_plugin' );

	add_action(
		'before_woocommerce_init',
		function () {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}
	);
}

function printess_variable_replacer($inputString, &$data, $replaceVariableCallback) {
	$ret = "";
	$isInVar = false;
	$currentToken = "";

	for($i = 0; $i < strlen($inputString); ++$i) {
		if($isInVar) {
			if($inputString[$i] == "]") {
				if($i < strlen($inputString) - 1 && $inputString[$i + 1] == "]") {
					$isInVar = false;
					++$i;
					$ret .= $replaceVariableCallback($currentToken, $data);
					$currentToken = "";
				} else {
					$ret .= "]";
				}
			} else {
				$currentToken .= $inputString[$i];
			}
		} else {
			if($inputString[$i] == "[") {
				if($i < strlen($inputString) - 1 && $inputString[$i + 1] == "[") {
					$isInVar = true;
					++$i;
				} else {
					$ret .= "[";
				}
			} else {
				$ret .= $inputString[$i];
			}
		}
	}

	if(strlen($currentToken) > 0) {
		$ret .= $currentToken;
	}

	return $ret;
}
?>