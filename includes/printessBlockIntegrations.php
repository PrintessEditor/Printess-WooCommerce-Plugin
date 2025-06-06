<?php
//https://www.nomar.dev/extending-the-woocommerce-checkout-block-with-jquery-and-php/
//https://woocommerce.com/document/woocommerce-store-editing/customizing-cart-and-checkout/
//https://www.liip.ch/en/blog/how-to-extend-existing-gutenberg-blocks-in-wordpress

use \Automattic\WooCommerce\Blocks\StoreApi\Schemas\CartItemSchema;

function printess_on_add_printess_cart_item_data($cart_item)
{
    if(array_key_exists("printess-save-token", $cart_item) && null !== $cart_item["printess-save-token"] && !empty($cart_item["printess-save-token"]))
    {
        $product = $product = wc_get_product( $cart_item["variation_id"] > 0 ?  $cart_item["variation_id"] : $cart_item["product_id"]);// apply_filters( 'woocommerce_cart_item_product', $cart_item['id'], $cart_item, $cart_item["key"] );
        $printess_save_token = $cart_item['printess-save-token'];
        $editLink = "";
    
        if ( $product && $product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item["key"] ) ) {
            $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink( $cart_item ) : '', $cart_item, $cart_item["key"] );
            $editLink = add_query_arg( 'printess-save-token', $printess_save_token, $product_permalink );

            if(array_key_exists("key", $cart_item)) {
                $id = $cart_item["key"];

                if(null !== $id && !empty($id)) {
                    $editLink = add_query_arg( 'printess_item_key', $id, $editLink );
                }
            }

            if(array_key_exists("printess-display-name", $cart_item)) {
                $editLink = add_query_arg( 'display_name', $cart_item["printess-display-name"], $editLink );
            }

            if(array_key_exists("printess-design-name", $cart_item)) {
                $editLink = add_query_arg( 'design_name', $cart_item["printess-design-name"], $editLink );
            }
        }

        $ret = array(
            "saveToken" => array_key_exists("printess-save-token", $cart_item) ? $cart_item["printess-save-token"] : "",
            "thumbnailUrl" => array_key_exists("printess-thumbnail-url", $cart_item) ? $cart_item["printess-thumbnail-url"] : "",
            "dateAdded" => array_key_exists("printess_date_added", $cart_item) ? $cart_item["printess_date_added"] : "",
            "designName" => array_key_exists("printess-design-name", $cart_item) ? $cart_item["printess-design-name"] : "",
            "designId" => array_key_exists("printess-design-id", $cart_item) ? $cart_item["printess-design-id"] : "",
            "editLink" => $editLink
        );

        return $ret;
    }


    return array();
}

function printess_register_block_hooks() {

    woocommerce_store_api_register_endpoint_data(
        [
            'endpoint'      => CartItemSchema::IDENTIFIER,
            'namespace'     => 'printess-editor',
            'data_callback' => function($cart_item) { return printess_on_add_printess_cart_item_data($cart_item); },
        ]
    );
}

?>