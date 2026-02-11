<?php

//class-yith-wapo-addon.php / class-yith-wapo-addon-premium.php

class PrintessYithWapoAddon
{
/*
		public function __construct( $args ) {
			global $wpdb;

			//
			// $id -> The add-on id.
			// $type -> The add-on type. Used for new add-ons (via $_REQUEST)
			//
			extract( $args );

			$this->type = $type ?? '';

			if ( is_numeric( $id ) && $id > 0 ) {

				$query = "SELECT * FROM {$wpdb->prefix}yith_wapo_addons WHERE id='$id'";
                $row   = $wpdb->get_row( $query ); // phpcs:ignore

				if ( isset( $row ) && $row->id === (string) $id ) {

					$this->id         = $row->id;
					$this->settings   = @unserialize( $row->settings ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize, WordPress.PHP.NoSilencedErrors.Discouraged
					$this->options    = @unserialize( $row->options ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize, WordPress.PHP.NoSilencedErrors.Discouraged
					$this->priority   = $row->priority;
					$this->visibility = $row->visibility;

					// Settings.
					$this->type  = isset( $this->settings['type'] ) ? $this->settings['type'] : 'html_text';
					$this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : '';

				}
			}
		}
*/
}