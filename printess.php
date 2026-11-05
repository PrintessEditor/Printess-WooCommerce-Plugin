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

/**
 * Plugin Name: Printess Editor
 * Description: Personalize anything! Friendship mugs, t-shirts, greeting cards. Limitless possibilities.
 * Plugin URI: https://printess.com/kb/integrations/woo-commerce/index.html
 * Developer: Bastian Kröger (support@printess.com); Alexander Oser (support@printess.com)
 * Version: 1.7.0
 * Author: Printess
 * Author URI: https://printess.com
 * Text Domain: printess-editor
 * Domain Path: /languages
 * Requires at least: 5.9
 * Requires PHP: 8.1
 * Requires Plugins: woocommerce
 * Tested up to: 7.1
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 *
 * WC requires at least: 5.8
 * WC tested up to: 11.1.2
 */


/*
 * This file must stay the plugin's main file: WordPress identifies an active plugin by
 * "printess-editor/printess.php", and an update that renamed it would deactivate the plugin on
 * every shop. The integration itself lives in legacy/.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( defined( 'PRINTESS_EDITOR_FILE' ) ) {
	return;
}

/** The plugin's main file, for APIs that identify a plugin by it (WooCommerce feature declarations). */
define( 'PRINTESS_EDITOR_FILE', __FILE__ );

/** The plugin version. */
define( 'PRINTESS_EDITOR_VERSION', '1.7.0' );

/** The plugin's base URL, with a trailing slash. Public assets are served from here. */
define( 'PRINTESS_LEGACY_URL', plugin_dir_url( __FILE__ ) );

require __DIR__ . '/legacy/printess-legacy.php';
