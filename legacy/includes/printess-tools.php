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

if ( class_exists( 'PrintessTools', false ) ) {
	return;
}

/**
 * Encryption of URL tokens, and string helpers.
 */
class PrintessTools {

	/**
	 * Encrypts a string with the service token (AES-256-CBC) and signs it with the shop token.
	 *
	 * @param string $input The plain text.
	 * @return string Base64 of IV, cipher text and HMAC.
	 */
	public static function encrypt( string $input ) {
		$iv_length = openssl_cipher_iv_length( 'aes-256-cbc' );
		$iv        = openssl_random_pseudo_bytes( $iv_length );
		$a         = openssl_encrypt( $input, 'aes-256-cbc', get_option( 'printess_service_token', '' ), OPENSSL_RAW_DATA, $iv );
		$b         = hash_hmac( 'sha3-512', $a, get_option( 'printess_shop_token', '' ), true );

		return base64_encode( $iv . $a . $b );
	}

	/**
	 * Reverses encrypt().
	 *
	 * @param string $encrypted Base64 of IV, cipher text and HMAC.
	 * @return string|bool The plain text, or false when the signature does not match.
	 */
	public static function decrypt( string $encrypted ): string|bool {
		$mix       = base64_decode( $encrypted );
		$iv_length = openssl_cipher_iv_length( 'aes-256-cbc' );

		$iv = substr( $mix, 0, $iv_length );
		$a  = substr( $mix, $iv_length, 64 );
		$b  = substr( $mix, $iv_length + 64 );

		$data  = openssl_decrypt( $a, 'aes-256-cbc', get_option( 'printess_service_token', '' ), OPENSSL_RAW_DATA, $iv );
		$b_new = hash_hmac( 'sha3-512', $a, get_option( 'printess_shop_token', '' ), true );

		if ( hash_equals( $b, $b_new ) ) {
			return $data;
		}

		return false;
	}

	/**
	 * Replaces the last occurrence of a string.
	 *
	 * @param string $search  The string to find.
	 * @param string $replace The replacement.
	 * @param string $subject The string to search.
	 * @return string The result.
	 */
	public static function replace_last_occurance( $search, $replace, $subject ) {
		$pos = strrpos( $subject, $search );

		if ( false !== $pos ) {
			$subject = substr_replace( $subject, $replace, $pos, strlen( $search ) );
		}

		return $subject;
	}
}
