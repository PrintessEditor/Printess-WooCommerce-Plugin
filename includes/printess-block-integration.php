<?php
/**
 * https://developer.woocommerce.com/docs/cart-and-checkout-handling-scripts-styles-and-data/
 * https://www.npmjs.com/package/@wordpress/dependency-extraction-webpack-pluginNeee...https://www.npmjs.com/package/@wordpress/dependency-extraction-webpack-pluginsfasffsafs
 */

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Class for integrating with WooCommerce Blocks
 */
class WooCommerce_Printess_Editor_Integration implements IntegrationInterface {
	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'woocommerce-printess-editor';
	}

    /**
	 * When called invokes any initialization/setup for the integration.
	*/
	public function initialize() {
        //Nothing to initialize yet, printess scripts are loaded by the plugin inside the wordpress footer
		// wp_enqueue_style('wc-blocks-integration',$style_url,[],$this->get_file_version( $style_path ));
        wp_register_script('wc-blocks-integration',$script_url,$script_asset['dependencies'],$script_asset['version'],true);
		//wp_set_script_translations('wc-blocks-integration','woocommerce-example-plugin',dirname( \WooCommerce_Example_Plugin_Assets::$plugin_file ) . '/languages');
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array( 'wc-blocks-integration' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array( 'wc-blocks-integration' );
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
	    $woocommerce_example_plugin_data = some_expensive_serverside_function();
	    return [
	        'expensive_data_calculation' => $woocommerce_example_plugin_data
        ];
	}

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}

		// As above, let's assume that WooCommerce_Example_Plugin_Assets::VERSION resolves to some versioning number our
		// extension uses.
		return \WooCommerce_Example_Plugin_Assets::VERSION;
	}
}