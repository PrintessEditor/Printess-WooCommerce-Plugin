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

if ( class_exists( 'PrintessSavedDesignRepository', false ) ) {
	return;
}


global $printess_saved_designs_db_version;
$printess_saved_designs_db_version = '1.2';

/**
 * The saved designs table, {prefix}printess_saved_designs.
 */
class Printess_Saved_Design_Repository {
	/**
	 * The configured lifetime of saved designs, or of ordered designs, in days.
	 *
	 * @param bool $for_orders Whether to read the ordered design lifetime.
	 * @return int The days; 10950 (30 years) for a negative setting.
	 */
	public static function get_design_live_time_in_days( $for_orders = false ) {
		$setting = get_option( 'printess_saved_design_lifetime', 30 );

		if ( true === $for_orders ) {
			$setting = get_option( 'printess_ordered_design_lifetime', 30 );
		}

		if ( ! isset( $setting ) || empty( $setting ) ) {
			$setting = 30;
		}

		$setting = intval( $setting );

		if ( $setting < 0 ) {
			// 30 years should be enough. v1 assigned this and returned null, which its only caller,
			// create_new_unexpiration_date(), turned into the same 30 years.
			return 10950;
		}

		return $setting;
	}

	/**
	 * Creates a new expiration date using the admin settings for expiration days
	 *
	 * @param bool     $use_order_date Use the ordered design expiration instead.
	 * @param int|null $live_time      Days to use instead of the setting.
	 * @return DateTime The new expiration date
	 */
	public static function create_new_unexpiration_date( $use_order_date = false, ?int $live_time = null ) {
		$days = self::get_design_live_time_in_days( $use_order_date );

		if ( isset( $live_time ) ) {
			$days = $live_time;
		}

		if ( $days < 1 ) {
			$days = 10950;// 30 years should be enough
		}

		$dt = new DateTime();
		$dt->add( new DateInterval( 'P' . $days . 'D' ) );
		$dt->setTimezone( new DateTimeZone( 'UTC' ) );

		return $dt;
	}


	/**
	 * Creates the table and the version and cleanup options.
	 */
	private function install_db_table(): void {
		global $wpdb;
		global $printess_saved_designs_db_version;

		$table_name = $wpdb->prefix . 'printess_saved_designs';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
      id int(11) NOT NULL AUTO_INCREMENT,
      customer_id int(11) NOT NULL,
      created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      valid_until datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      save_token text NOT NULL,
      thumbnail_url text NOT NULL,
      product_name text NOT NULL,
      product_id int(11) NOT NULL,
      product_options mediumtext NOT NULL,
      display_name text NOT NULL,
      last_updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      last_ordered_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		add_option( 'printess_saved_designs_db_version', $printess_saved_designs_db_version );
		add_option( 'printess_last_cleanup', gmdate( 'Y-m-d\TH:i:s\Z' ) );
	}

	/**
	 * Brings the table up to the current schema version.
	 */
	private function update_db_table(): void {
		global $printess_saved_designs_db_version;
		global $wpdb;

		$installed_db_ver = get_option( 'printess_saved_designs_db_version' );

		if ( floatval( $installed_db_ver ) < floatval( $printess_saved_designs_db_version ) ) {
			$table_name = $wpdb->prefix . 'printess_saved_designs';

			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        customer_id int(11) NOT NULL,
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        valid_until datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        save_token text NOT NULL,
        thumbnail_url text NOT NULL,
        product_name text NOT NULL,
        product_id int(11) NOT NULL,
        product_options mediumtext NOT NULL,
        display_name text NOT NULL,
        last_updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        last_ordered_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
      ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		if ( $installed_db_ver != $printess_saved_designs_db_version ) {
			update_option( 'printess_saved_designs_db_version', $printess_saved_designs_db_version );
		}
	}

	/**
	 * Creates or updates the table, on every load.
	 */
	public function install_or_update_db_table(): void {
		$installed_db_ver = get_option( 'printess_saved_designs_db_version' );

		if ( false == $installed_db_ver || '' == $installed_db_ver ) {
			$this->install_db_table();
		} else {
			$this->update_db_table();
		}
	}

	/**
	 * Deletes the saved designs whose save token expired more than $grace_days ago.
	 *
	 * Expired designs are already hidden from customers (get_designs() returns only rows that are
	 * still valid); this removes them from the table. The grace period covers a valid_until that
	 * lags behind the save token's real lifetime at Printess. "Unlimited" designs are stored 30 years
	 * ahead and are never affected.
	 *
	 * @param int $grace_days Days an expired design is kept before it is deleted.
	 * @return int The number of deleted designs.
	 */
	public static function delete_expired_designs( int $grace_days = 30 ): int {
		global $wpdb;

		$table_name = $wpdb->prefix . 'printess_saved_designs';
		$cutoff     = gmdate( 'Y-m-d H:i:s', time() - max( 0, $grace_days ) * DAY_IN_SECONDS );

		// The table name is $wpdb->prefix plus a constant, and %i would need WordPress 6.2 (v1 supports 5.9).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- own table, name is prefix + constant (W110).
		$customer_ids = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT customer_id FROM `{$table_name}` WHERE valid_until < %s", $cutoff ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- own table, name is prefix + constant (W110).
		$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM `{$table_name}` WHERE valid_until < %s", $cutoff ) );

		foreach ( $customer_ids as $customer_id ) {
			wp_cache_delete( 'printess_designs_gen_' . $customer_id, 'printess_saved_designs' );
		}

		update_option( 'printess_last_cleanup', gmdate( 'Y-m-d\TH:i:s\Z' ) );

		return false === $deleted ? 0 : (int) $deleted;
	}

	/**
	 * Stores a new saved design.
	 *
	 * @param int           $customer_id     The customer.
	 * @param string        $save_token      The save token.
	 * @param string        $thumbnail_url   The thumbnail URL.
	 * @param int           $product_id      The product.
	 * @param string        $product_name    The product name.
	 * @param string        $display_name    The design name.
	 * @param array|null    $product_options The chosen options.
	 * @param DateTime|null $expiration_date The expiry; the configured lifetime when null.
	 * @return int The new design id.
	 */
	public function add_design( int $customer_id, string $save_token, string $thumbnail_url, int $product_id, string $product_name, string $display_name, ?array $product_options = null, ?DateTime $expiration_date = null ): int {
		global $wpdb;
		$table_name = $wpdb->prefix . 'printess_saved_designs';
		$time_out   = self::create_new_unexpiration_date( false );

		if ( isset( $expiration_date ) ) {
			$time_out = $expiration_date;
		}

		$to_string = function ( $value ): string {
			if ( null != $value ) {
				if ( is_array( $value ) ) {
					return json_encode( $value );
				} elseif ( is_a( $value, 'DateTime' ) ) {
					$dt = clone $value;
					$dt->setTimezone( new DateTimeZone( 'UTC' ) );
					return $dt->format( 'Y-m-d H:i:s' );
				} else {
					return '' . $value;
				}
			}

			return '';
		};

		$object_to_write = array(
			'customer_id'     => $customer_id,
			'save_token'      => $to_string( $save_token ),
			'thumbnail_url'   => $to_string( $thumbnail_url ),
			'product_id'      => $product_id,
			'product_name'    => $to_string( $product_name ),
			'display_name'    => $to_string( $display_name ),
			'product_options' => $to_string( $product_options ),
			'created_at'      => $to_string( new DateTime() ),
			'valid_until'     => $to_string( $time_out ),
			'last_updated_at' => $to_string( new DateTime() ),
		);

		$logger = wc_get_logger();

		if ( isset( $logger ) ) {
			$logger->notice( 'User with id ' . $customer_id . ' added a new saved design: ' . json_encode( $object_to_write ), array( 'source' => 'printress-saved-designs' ) );
		}

	  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			$table_name,
			$object_to_write,
			array( '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		$new_id = $wpdb->insert_id;
		wp_cache_delete( 'printess_designs_gen_' . $customer_id, 'printess_saved_designs' );
		return $new_id;
	}

	/**
	 * A customer's designs that are still valid, newest first, paged and filtered by name.
	 *
	 * @param int    $customer_id         The customer.
	 * @param string $display_name_filter Part of the design name, or empty.
	 * @param int    $current_page        The page, from 1.
	 * @param int    $results_per_page    Designs per page, at most 50.
	 * @param int    $design_id           Only this design, when positive.
	 * @return array The designs.
	 */
	public function get_designs( int $customer_id, string $display_name_filter, int $current_page = 1, int $results_per_page = 20, int $design_id = -1 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'printess_saved_designs';
		$sql_params = array( $customer_id, gmdate( 'Y-m-d H:i:s' ) );
		$ret        = array();

		$current_page     = max( 1, intval( '' . $current_page ) );
		$results_per_page = min( 50, max( 1, intval( '' . $results_per_page ) ) ); // Enforce max 50 results per page.

		if ( $design_id > 0 ) {
			$current_page     = 1;
			$results_per_page = 1;
		}

		$sql = "SELECT * from $table_name WHERE customer_id = %d AND valid_until >= %s";

		if ( $design_id > 0 ) {
			$sql_params[] = intval( $design_id );

			$sql = $sql . ' AND id = %d';
		}

		if ( null != $display_name_filter && '' != $display_name_filter ) {
			$sql_params[] = '%' . $display_name_filter . '%';

			$sql = $sql . ' AND display_name like %s';
		}

		$sql_params[] = $results_per_page;
		$sql_params[] = ( $current_page - 1 ) * $results_per_page;
		$sql          = $sql . ' ORDER BY last_updated_at DESC, display_name ASC LIMIT %d OFFSET %d';

	  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql is built with %d/%s placeholders above and passed through prepare()
		$sql = $wpdb->prepare( ...array_merge( array( $sql ), $sql_params ) );

		$cache_gen = wp_cache_get( 'printess_designs_gen_' . $customer_id, 'printess_saved_designs' );
		if ( false === $cache_gen ) {
			$cache_gen = 0;
		}
		$cache_key     = 'printess_designs_' . $customer_id . '_g' . $cache_gen . '_' . md5( $sql );
		$saved_designs = wp_cache_get( $cache_key, 'printess_saved_designs' );

		if ( false === $saved_designs ) {
		  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$saved_designs = $wpdb->get_results( $sql );
			wp_cache_set( $cache_key, null !== $saved_designs ? $saved_designs : array(), 'printess_saved_designs' );
		}

		if ( $saved_designs ) {
			foreach ( $saved_designs as $pointer ) {
				$ret[] = array(
					'id'           => intval( $pointer->id ),
					'validUntil'   => $pointer->valid_until,
					'saveToken'    => $pointer->save_token,
					'thumbnailUrl' => $pointer->thumbnail_url,
					'productId'    => intval( $pointer->product_id ),
					'productName'  => $pointer->product_name,
					'displayName'  => $pointer->display_name,
					'options'      => null != $pointer && '' != $pointer ? json_decode( $pointer->product_options, true ) : array(),
				);
			}
		}

		return $ret;
	}

	/**
	 * One of a customer's designs that is still valid.
	 *
	 * @param int $customer_id The customer.
	 * @param int $design_id   The design.
	 * @return array|null The design.
	 */
	public function get_design( int $customer_id, int $design_id ) {
		$designs = $this->get_designs( $customer_id, '', 0, 0, $design_id );

		if ( null == $designs || count( $designs ) < 1 ) {
			return null;
		} else {
			return $designs[0];
		}
	}

	/**
	 * Replaces a design's save token, thumbnail and options.
	 *
	 * @param int           $customer_id     The customer.
	 * @param int           $design_id       The design.
	 * @param string        $save_token      The new save token.
	 * @param string        $thumbnail_url   The new thumbnail URL.
	 * @param array|null    $options         The chosen options.
	 * @param DateTime|null $expiration_date The expiry; the configured lifetime when null.
	 * @return bool Whether a row changed.
	 */
	public function update_design( int $customer_id, int $design_id, string $save_token, string $thumbnail_url, ?array $options = null, ?DateTime $expiration_date = null ): bool {
		global $wpdb;
		$table_name = $wpdb->prefix . 'printess_saved_designs';
		$time_out   = self::create_new_unexpiration_date( false );
		$now        = new DateTime();
		$now->setTimezone( new DateTimeZone( 'UTC' ) );

		if ( isset( $expiration_date ) ) {
			$time_out = $expiration_date;
		}

		// The lifetime is always renewed. v1 tested an undefined $update_life_time here, which is
		// never false, so this was the only branch that ever ran.
		$update_values = array(
			'save_token'      => $save_token,
			'thumbnail_url'   => $thumbnail_url,
			'valid_until'     => $time_out->format( 'Y-m-d H:i:s' ),
			'last_updated_at' => $now->format( 'Y-m-d H:i:s' ),
		);

		$update_datatypes = array( '%s', '%s', '%s', '%s' );

		if ( null != $options && count( $options ) > 0 ) {
			$update_values['product_options'] = wp_json_encode( $options );
			$update_datatypes[]               = '%s';
		}

	  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->update(
			$table_name,
			$update_values,
			array(
				'id'          => $design_id,
				'customer_id' => $customer_id,
			),
			$update_datatypes,
			array( '%d', '%d' )
		);

		wp_cache_delete( 'printess_designs_gen_' . $customer_id, 'printess_saved_designs' );
		return false != $result && $result > 0;
	}

	/**
	 * Records that a design was ordered, and extends its lifetime.
	 *
	 * @param int           $customer_id        The customer.
	 * @param int           $design_id          The design.
	 * @param string        $ordered_save_token The save token that was ordered.
	 * @param DateTime|null $expiration_date    The new expiry; the ordered design lifetime when null.
	 * @return bool Whether a row changed.
	 */
	public function update_last_ordered( int $customer_id, int $design_id, string $ordered_save_token, ?DateTime $expiration_date = null ): bool {
		global $wpdb;
		$table_name = $wpdb->prefix . 'printess_saved_designs';
		$now        = new DateTime();
		$now->setTimezone( new DateTimeZone( 'UTC' ) );
		$time_out = self::create_new_unexpiration_date( true );

		if ( isset( $expiration_date ) ) {
			$time_out = $expiration_date;
		}

		$stored_design = $this->get_design( $customer_id, $design_id );

		if ( isset( $stored_design ) && $stored_design['saveToken'] !== $ordered_save_token ) {
			require_once plugin_dir_path( __DIR__ ) . 'includes/printess-api.php';

			PrintessApi::unexpire_save_token( $stored_design['saveToken'], $time_out );
		}

	  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->update(
			$table_name,
			array(
				'last_ordered_at' => $now->format( 'Y-m-d H:i:s' ),
				'valid_until'     => $time_out->format( 'Y-m-d H:i:s' ),
			),
			array(
				'id'          => $design_id,
				'customer_id' => $customer_id,
			),
			array( '%s', '%s' ),
			array( '%d', '%d' )
		);

		wp_cache_delete( 'printess_designs_gen_' . $customer_id, 'printess_saved_designs' );
		return false != $result && $result > 0;
	}

	/**
	 * Sets the expiry of every design of a customer that has this save token.
	 *
	 * @param int           $customer_id     The customer.
	 * @param string        $save_token      The save token.
	 * @param DateTime|null $expiration_date The expiry; the ordered design lifetime when null.
	 * @return bool Whether a row changed.
	 */
	public static function update_valid_until( int $customer_id, $save_token, ?DateTime $expiration_date = null ): bool {
		global $wpdb;
		$table_name = $wpdb->prefix . 'printess_saved_designs';
		$now        = new DateTime();
		$now->setTimezone( new DateTimeZone( 'UTC' ) );
		$time_out = self::create_new_unexpiration_date( true );

		if ( isset( $expiration_date ) ) {
			$time_out = $expiration_date;
		}

	  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->update(
			$table_name,
			array(
				'valid_until' => $time_out->format( 'Y-m-d H:i:s' ),
			),
			array(
				'save_token'  => $save_token,
				'customer_id' => $customer_id,
			),
			array( '%s' ),
			array( '%s', '%d' )
		);

		wp_cache_delete( 'printess_designs_gen_' . $customer_id, 'printess_saved_designs' );
		return false != $result && $result > 0;
	}

	/**
	 * Deletes one of a customer's designs.
	 *
	 * @param int $customer_id The customer.
	 * @param int $design_id   The design.
	 * @return bool Whether a row was deleted.
	 */
	public function delete_design( int $customer_id, int $design_id ): bool {
		global $wpdb;
		$table_name = $wpdb->prefix . 'printess_saved_designs';

	  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->delete(
			$table_name,
			array(
				'id'          => $design_id,
				'customer_id' => $customer_id,
			),
			array( '%d', '%d' )
		);

		wp_cache_delete( 'printess_designs_gen_' . $customer_id, 'printess_saved_designs' );
		return false != $result && $result > 0;
	}
}
