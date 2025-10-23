<?php

if ( class_exists( 'PrintessApi', false ) ) return;

class PrintessApi
{
	/**
	 * Sends a POST request to the Printess api.
	 *
	 * @param mixed $url   The url to send the request to.
	 * @param mixed $token The token to use.
	 * @param mixed $data  The data to send.
	 * @throws \Exception In case the posting of data failed.
	 */
    static function send_post_request( $url, $token, $data ) {
        require_once(plugin_dir_path(__DIR__) . "includes/printess-admin-settings.php");

        $ssl_verify = ! PrintessAdminSettings::get_debug();
        $args       = array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'blocking'    => true,
            'headers'     => array(
                'Authorization' => "Bearer $token",
                'Content-Type'  => 'application/json',
            ),
            'body'        => wp_json_encode( $data ),
            'sslverify'   => $ssl_verify,
            'data_format' => 'body',
        );

        $response = wp_remote_post( $url, $args );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            throw new \Exception( $error_message );
        }

        return json_decode( wp_remote_retrieve_body( $response ), true );
    }

    /**
	 * Downloads json content
	 *
	 * @param mixed $url   The url to send the request to.
	 * @throws \Exception In case the posting of data failed.
	 */
    static function send_get_request($url) {
        require_once(plugin_dir_path(__DIR__) . "includes/includes/printess-admin-settings.php");

        $ssl_verify = ! PrintessAdminSettings::get_debug();
        $args       = array(
            'method'      => 'GET',
            'timeout'     => 45,
            'redirection' => 5,
            'blocking'    => true,
            'headers'     => array(
                'Content-Type'  => 'application/json',
            ),
            'sslverify'   => $ssl_verify,
        );

        $response = wp_remote_get( $url, $args );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            throw new \Exception( $error_message );
        }

        return json_decode( wp_remote_retrieve_body( $response ), true );
    }

    /**
     * Loads a list of all available printess dropshippers and the correcsponding drop ship products
     */
    static function get_dropshipping_info() {
        require_once(plugin_dir_path(__DIR__) . "includes/printess-admin-settings.php");

        $printess_host = PrintessAdminSettings::get_host();

        $dropshippers = PrintessApi::send_post_request(
            "$printess_host/dropshippers/load",
            PrintessAdminSettings::get_service_token(),
            array(
                'default' => '',
            )
        );

        if ( ! isset( $dropshippers ) || count( $dropshippers ) < 1 ) {
            return array();
        }

        usort(
            $dropshippers,
            function ( $a, $b ) {
                return strcmp( $a['display'], $b['display'] );
            }
        );

        $products = PrintessApi::send_post_request(
            "$printess_host/productDefinitions/load",
            PrintessAdminSettings::get_service_token(),
            array(
                'default' => '',
            )
        );

        if ( isset( $dropshippers ) && count( $dropshippers ) > 0 ) {
            // Add product definitions to drop shippers.
            foreach ( $products['productDefinitions'] as &$value ) {
                foreach ( $dropshippers as &$dropshipper ) {
                    if ( $dropshipper['id'] === $value['dropshipperId'] ) {
                        if ( ! isset( $dropshipper['productDefinitions'] ) ) {
                            $dropshipper['productDefinitions'] = array();
                        }

                        $dropshipper['productDefinitions'][] = $value;
                    }
                }
            }
        }

        return $dropshippers;
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

    static function get_save_token_info($save_token) {
        require_once(plugin_dir_path(__DIR__) . "includes/printess-admin-settings.php");

        $printess_host = PrintessAdminSettings::get_host();
        $service_token = PrintessAdminSettings::get_service_token();

        return PrintessApi::send_post_request("$printess_host/shop/template/info/", $service_token, Array( "id" => $save_token));
    }

    static function get_expiration_date($save_token) {
        $info = PrintessApi::get_save_token_info($save_token);

        if(null !== $info && is_array($info) && array_key_exists("expiresOn", $info) && null !== $info["expiresOn"] && !empty($info["expiresOn"])) {
            return new DateTime($info["expiresOn"]);
        }

        return null;
    }
}



?>