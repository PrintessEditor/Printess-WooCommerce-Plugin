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

if ( class_exists( 'PrintessApi', false ) ) {
	return;
}

/**
 * Calls to the Printess backend API.
 */
class PrintessApi {

	/**
	 * Sends a POST request to the Printess api.
	 *
	 * @param mixed $url   The url to send the request to.
	 * @param mixed $token The token to use.
	 * @param mixed $data  The data to send.
	 * @param bool  $expect_200 Treat any status but 200 as a failure.
	 * @throws \Exception In case the posting of data failed.
	 */
	public static function send_post_request( $url, $token, $data, bool $expect_200 = false ) {
		require_once plugin_dir_path( __DIR__ ) . 'includes/printess-admin-settings.php';

		// Never turned off: in debug mode it sent the service token over unverified TLS.
		$ssl_verify = true;
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
			throw new \Exception( esc_html( $error_message ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( $response_code < 200 || $response_code >= 300 || ( true === $expect_200 && 200 !== $response_code ) ) {
			throw new \Exception( esc_html( 'Unsuccessful HTTP response code: ' . $response_code . ' for request: ' . $url . ' and payload ' . json_encode( $data ) . '; Response: ' . wp_remote_retrieve_body( $response ) ) );
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Downloads json content
	 *
	 * @param mixed $url   The url to send the request to.
	 * @throws \Exception In case the posting of data failed.
	 */
	public static function send_get_request( $url ) {
		require_once plugin_dir_path( __DIR__ ) . 'includes/printess-admin-settings.php';

		// Never turned off: in debug mode it sent the service token over unverified TLS.
		$ssl_verify = true;
		$args       = array(
			'method'      => 'GET',
			'timeout'     => 45,
			'redirection' => 5,
			'blocking'    => true,
			'headers'     => array(
				'Content-Type' => 'application/json',
			),
			'sslverify'   => $ssl_verify,
		);

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			throw new \Exception( esc_html( $error_message ) );
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Loads a list of all available printess dropshippers and the correcsponding drop ship products
	 */
	public static function get_dropshipping_info() {
		try {
			require_once plugin_dir_path( __DIR__ ) . 'includes/printess-admin-settings.php';

			$printess_host = PrintessAdminSettings::get_host();

			$dropshippers = self::send_post_request(
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

			$products = self::send_post_request(
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
		} catch ( \Exception $ex ) {
			$logger = wc_get_logger();

			if ( isset( $logger ) ) {
				$logger->info( 'Unable to load dropshipping info: ' . json_encode( $ex ), array( 'source' => 'printress-saved-designs' ) );
			}

			return array();
		}
	}

	/**
	 * Creates a new drop shipping address via the printess api
	 *
	 * @param mixed $data The Address data that should be used.
	 */
	public function printess_create_dropshipping_address( $data ) {
		$printess_host = PrintessAdminSettings::get_host();

		return self::send_post_request(
			"$printess_host/dropshipData/save",
			PrintessAdminSettings::get_service_token(),
			$data
		);
	}

	/**
	 * Loads what Printess knows about a save token (its expiry among others).
	 *
	 * @param string $save_token The save token.
	 */
	public static function get_save_token_info( $save_token ) {
		require_once plugin_dir_path( __DIR__ ) . 'includes/printess-admin-settings.php';

		$printess_host = PrintessAdminSettings::get_host();
		$service_token = PrintessAdminSettings::get_service_token();

		return self::send_post_request( "$printess_host/shop/template/info/", $service_token, array( 'id' => $save_token ) );
	}

	/**
	 * The expiry date Printess reports for a save token.
	 *
	 * @param string $save_token The save token.
	 * @return DateTime|null Null when Printess reports none, or cannot be asked.
	 */
	public static function get_expiration_date( $save_token ) {
		// Only ever displayed (order screen, the customer's order view), so a failure must not take the
		// page down: no date is shown, and the first failure of the request is logged.
		static $logged = false;

		try {
			$info = self::get_save_token_info( $save_token );

			if ( null !== $info && is_array( $info ) && array_key_exists( 'expiresOn', $info ) && null !== $info['expiresOn'] && ! empty( $info['expiresOn'] ) ) {
				return new DateTime( $info['expiresOn'] );
			}
		} catch ( \Throwable $ex ) {
			if ( ! $logged && function_exists( 'wc_get_logger' ) ) {
				$logged = true;
				wc_get_logger()->warning( 'Unable to load the expiry date of a save token: ' . $ex->getMessage(), array( 'source' => 'printess-editor' ) );
			}
		}

		return null;
	}

	/**
	 * Sets a new expiration date on printess template via printess api
	 *
	 * @param string $save_token The save token containing the latest changes.
	 * @param string $expires_at_utc The new expiration date.
	 */
	public static function unexpire_save_token( $save_token, $expires_at_utc ) {
		require_once plugin_dir_path( __DIR__ ) . 'includes/printess-admin-settings.php';

		$expiration_date_string = null === $expires_at_utc ? null : str_replace( ' ', 'T', $expires_at_utc->format( 'Y-m-d H:i:s' ) ) . 'Z';
		$printess_host          = PrintessAdminSettings::get_host();

		$payload = array(
			'saveToken' => $save_token,
			'expiresOn' => $expiration_date_string,
		);

		try {
			self::send_post_request( "$printess_host/savetoken/lifetime/extend", PrintessAdminSettings::get_service_token(), $payload, true );
		} catch ( \Exception $ex ) {
			$logger = wc_get_logger();

			if ( isset( $logger ) ) {
				$logger->error( 'Unable to extend save token live time for save token: ' . json_encode( $save_token ) . '; Error: ' . json_encode( $ex ), array( 'source' => 'printress-saved-designs' ) );
				return false;
			}
		}

		return true;
	}
}
