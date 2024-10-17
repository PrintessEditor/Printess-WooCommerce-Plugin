<?php 

if ( class_exists( 'PrintessSavedDesignRepository', false ) ) return;


global $printess_saved_designs_db_version;
$printess_saved_designs_db_version = '1.2';

class Printess_Saved_Design_Repository {
  private function get_design_live_time_in_days($for_orders = false) {
    $setting = get_option( 'printess_saved_design_lifetime', 30 );

    if($for_orders === true) {
      $setting = get_option( 'printess_ordered_design_lifetime', 30 );
    }
  
    if ( ! isset( $setting ) || empty( $setting ) ) {
      $setting = 30;
    }
  
    $setting = intval( $setting );

    if($setting < 0) {
      return 30;
    }
    else {
      return $setting;
    }
  }

  private function install_db_table(): void {
    global $wpdb;
    global $printess_saved_designs_db_version;

    $table_name = $wpdb->prefix . "printess_saved_designs";

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
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    add_option( 'printess_saved_designs_db_version', $printess_saved_designs_db_version );
    add_option( 'printess_last_cleanup', gmdate("Y-m-d\TH:i:s\Z"));
  }

  private function update_db_table(): void {
    global $printess_saved_designs_db_version;
    global $wpdb;
    
    $installed_db_ver = get_option( "printess_saved_designs_db_version" );

    if( floatval($installed_db_ver) < floatval($printess_saved_designs_db_version) ) {
      $table_name = $wpdb->prefix . "printess_saved_designs";

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
        
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );
    }

    if( $installed_db_ver != $printess_saved_designs_db_version ) {
      update_option("printess_saved_designs_db_version", $printess_saved_designs_db_version);
    }
  }

  function install_or_update_db_table(): void {
    $installed_db_ver = get_option( "printess_saved_designs_db_version" );

    if($installed_db_ver == false || $installed_db_ver == "") {
      $this->install_db_table();
    } else {
      $this->update_db_table();
    }
  }

  function cleanup_db(): bool {
    $last_cleanup = get_option("printess_last_cleanup");
    $installed_db_ver = get_option( "printess_saved_designs_db_version" );
    global $wpdb;

    if($last_cleanup != false && $last_cleanup != "") {
      $last_cleanup = DateTime::createFromFormat('Y-m-d\TH:i:s+', $last_cleanup);
    } else {
      $last_cleanup = null;
    }

    $yesterday = new DateTime();
    $yesterday->sub(new DateInterval('P1D'));

    if($installed_db_ver == false || $installed_db_ver == "" || $installed_db_ver == false || $yesterday > $last_cleanup) {
      $table_name = $wpdb->prefix . "printess_saved_designs_db_version";
      $now = gmdate("Y-m-d H:i:s");

      $sql = "DELETE FROM $table_name WHERE valid_until < $now";
      
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );

      update_option("printess_last_cleanup", gmdate("Y-m-d\TH:i:s\Z"));

      return true;
    } else {
      return false;
    }
  }

  function add_serialized_design(array $design): int {
    return $this->add_design($design["customerId"], $design["saveToken"], $design["thumbnailUrl"], $design["productId"], $design["productName"], $design["displayName"], $design["productOptions"]);
  }

  function add_design(int $customer_id, string $save_token, string $thumbnail_url, int $product_id, string $product_name, string $display_name, array $product_options = null): int {
    global $wpdb;
    $table_name = $wpdb->prefix . "printess_saved_designs";
    $timeOut = new DateTime();
    $liveTimeInDays = get_option( 'printess_saved_design_lifetime', 30 );

    if(!isset($liveTimeInDays) || empty($liveTimeInDays)) {
      $liveTimeInDays = 30;
    } else if(intval($liveTimeInDays) < 1) {
      $liveTimeInDays = 10950;//30 years should be enough
    }


    $timeOut->add(new DateInterval('P' . intval($liveTimeInDays) . 'D'));

    $to_string = function($value): string {
      if($value != null) {
        if(is_array($value)) {
          return json_encode($value);
        } else if(is_a($value, 'DateTime')) {
          $dt = clone $value;
          $dt->setTimezone(new DateTimeZone('UTC'));
          return $dt->format('Y-m-d H:i:s');
        } else {
          return "" . $value;
        }
      }
      
      return "";
    };

    $wpdb->insert( 
      $table_name, 
      array( 
        'customer_id' => $customer_id,
        'save_token' => $to_string($save_token),
        'thumbnail_url' => $to_string($thumbnail_url),
        'product_id' => $product_id,
        'product_name' => $to_string($product_name),
        'display_name' => $to_string($display_name),
        'product_options' => $to_string($product_options),
        'created_at' => $to_string(new DateTime()),
        'valid_until' => $to_string($timeOut),
        'last_updated_at' => $to_string(new DateTime())
      ),
      array("%d", "%s", "%s", "%d", "%s", "%s", "%s", "%s", "%s", "%s")
    );

    return $wpdb->insert_id;
  }

  function get_designs(int $customer_id, string $display_name_filter, int $current_page = 1, int $results_per_page = 20, int $design_id = -1) {
    global $wpdb;
    $table_name = $wpdb->prefix . "printess_saved_designs";
    $sql_params = array($customer_id, gmdate("Y-m-d H:i:s"), );
    $ret = array();

    $current_page = max( 1, intval("" . $current_page));
    $results_per_page = min( 50, max( 1, intval("" . $results_per_page)));//enforce max 50 results per page

    if($design_id > 0) {
      $current_page = 1;
      $results_per_page = 1;
    }

    $sql = "SELECT * from $table_name WHERE customer_id = %d AND valid_until >= %s";

    if($design_id > 0) {
      $sql_params[] = intval($design_id);

      $sql = $sql . " AND id = %d";
    }

    if($display_name_filter != null && $display_name_filter != "") {
      $sql_params[] = "%" . $display_name_filter . "%";

      $sql = $sql . " AND display_name like %s";
    }

    $sql_params[] = $results_per_page;
    $sql_params[] = ($current_page - 1) * $results_per_page;
    $sql = $sql . " ORDER BY last_updated_at DESC, display_name ASC LIMIT %d OFFSET %d";

    $sql = $wpdb->prepare(...array_merge(array($sql), $sql_params));
    $saved_designs = $wpdb->get_results($sql);
    
    if ( $saved_designs ) {
      foreach ( $saved_designs as $pointer ) {
        $ret[] = array(
          "id" =>  intval($pointer->id),
          "validUntil" => $pointer->valid_until,
          "saveToken" => $pointer->save_token,
          "thumbnailUrl" => $pointer->thumbnail_url,
          "productId" => intval($pointer->product_id),
          "productName" => $pointer->product_name,
          "displayName" => $pointer->display_name,
          "options" => $pointer != null && $pointer != "" ? json_decode($pointer->product_options, true) : array()
        );
      }
    }

    return $ret;
  }

  function get_design(int $customer_id, int $design_id) {
    $designs = $this->get_designs($customer_id, "", 0, 0, $design_id);

    if($designs == null || count($designs) < 1) {
      return null;
    } else {
      return $designs[0];
    }
  }

  function update_design(int $customer_id, int $design_id, string $save_token, string $thumbnail_url, array $options = null): bool {
    global $wpdb;
    $table_name = $wpdb->prefix . "printess_saved_designs";
    $liveTimeInDays = $this->get_design_live_time_in_days();
    $timeOut = new DateTime();
    $now = new DateTime();
    $now->setTimezone(new DateTimeZone('UTC'));

    $timeOut->add(new DateInterval('P' . $liveTimeInDays . 'D'));
    $timeOut->setTimezone(new DateTimeZone('UTC'));

    $update_values = array(
      'save_token' => $save_token,
      'thumbnail_url' => $thumbnail_url,
      'valid_until' => $timeOut->format('Y-m-d H:i:s'),
      'last_updated_at' => $now->format('Y-m-d H:i:s')
    );

    $update_datatypes = array( '%s', '%s', '%s', '%s' );

    if(null != $options && count($options) > 0) {
      $update_values['product_options'] = wp_json_encode($options);
      $update_datatypes[] = '%s';
    }

    $result = $wpdb->update( $table_name, $update_values,
        array(
              'id' => $design_id,
              'customer_id' => $customer_id ),
        $update_datatypes,
        array( '%d', '%d' ) );

    return $result != false  && $result > 0;
  }

  function update_last_ordered(int $customer_id, int $design_id): bool {
    global $wpdb;
    $table_name = $wpdb->prefix . "printess_saved_designs";
    $now = new DateTime();
    $now->setTimezone(new DateTimeZone('UTC'));
    $liveTimeInDays = $this->get_design_live_time_in_days(true);
    $timeOut = new DateTime();
    $timeOut->add(new DateInterval('P' . $liveTimeInDays . 'D'));
    $timeOut->setTimezone(new DateTimeZone('UTC'));

    $result = $wpdb->update( $table_name, array(
                                                'last_ordered_at' => $now->format('Y-m-d H:i:s'),
                                                'valid_until' => $timeOut->format('Y-m-d H:i:s')),
                                          array(
                                                'id' => $design_id,
                                                'customer_id' => $customer_id ),
                                          array( '%s', "%s" ),
                                          array( '%d', '%d' ) );

    return $result != false  && $result > 0;
  }

  function delete_design(int $customer_id, int $design_id): bool {
    global $wpdb;
    $table_name = $wpdb->prefix . "printess_saved_designs";

    $result = $wpdb->delete( $table_name, array( 'id' => $design_id, 'customer_id' => $customer_id ), array( '%d', '%d' ) );

    return $result != false && $result > 0;
  }
}


?>