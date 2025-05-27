<?php

if ( class_exists( 'PrintessAdminSettings', false ) ) return;

include_once("includes/printess-html-helpers.php");

class PrintessAdminSettings
{
    /**
     * Gets the Printess service token which is used for the admin interface.
     * Never use this token on the buyer side.
     */
    static function get_service_token() {
        return get_option( 'printess_service_token', '' );
    }

    /**
     * Gets the Printes domain this plugin is connected to.
     *
     * @return string the used printess api domain
     */
    static function get_domain() {
        return get_option( 'printess_api_domain', 'api.printess.com' );
    }

    /**
     * Gets the Printess host.
     * Used for building the api endpoint urls.
     */
    static function get_host() {
        return 'https://' . PrintessAdminSettings::get_domain();
    }

    /**
     * Gets the Printess shop token.
     * This one is used on the buyer side.
     * It is restricted to loading templates,
     * uploading buyer side resources
     * and saving tokens.
     */
    static function get_shop_token() {
        return get_option( 'printess_shop_token', '' );
    }

    /**
     * Gets the Printess embed url.
     * This one is used to show the editor.
     * In case you want your own styling or use a fixed version,
     * you can change it.
     */
    static function get_embed_html_url() {
        return get_option( 'printess_embed_html_url', 'https://editor.printess.com/printess-editor/embed.html' );
    }

    /**
     * Gets the html ids to hide when showing the Printess editor.
     */
    static function get_ids_to_hide() {
        return get_option( 'printess_ids_to_hide', 'wpadminbar, page' );
    }

    /**
     * Gets the html class names to hide when showing the Printess editor.
     */
    static function get_class_names_to_hide() {
        return get_option( 'printess_class_names_to_hide', '' );
    }

    /**
     * Gets the access token.
     */
    static function get_access_token() {
        return get_option( 'printess_access_token', '' );
    }

    /**
     * Gets the Printess debug option.
     */
    static function get_debug() {
        $v = get_option( 'printess_debug', false );

        if ( $v ) {
            return true;
        }

        return false;
    }

    /**
     * Gets the Printess setting whether to show the customize button on the archive pages.
     */
    static function get_show_customize_on_archive_page() {
        $v = get_option( 'printess_show_customize_on_archive_page', true );

        if ( $v ) {
            return true;
        }

        return false;
    }

    /**
     * Gets the Printess setting whether to directly add item to cart when finishing customization.
     */
    static function get_add_to_cart_after_customization() {
        $v = get_option( 'printess_add_to_cart_after_customization', true );

        if ( $v ) {
            return true;
        }

        return false;
    }

    /**
     * Gets the Printess order approval setting.
     */
    static function get_approval_mode() {
        return get_option( 'printess_approval', 'auto' );
    }

    /**
     * Gets the Printess additional button classes.
     */
    static function get_customize_button_class() {
        return get_option( 'printess_customize_button_class', '' );
    }

    /**
     * Gets the Printess default editor theme.
     */
    static function get_default_theme() {
        return get_option( 'printess_default_theme', '' );
    }

    /**
     * Gets all user fields that should be pulled in as form fields
     */
    static function get_user_fields() {
        return get_option('printess_user_fields', "");
    }

    /**
     * Retrieves a valid setting for thumbnail width
     */
    static function get_thumbnail_width() {
        $setting = get_option( 'printess_thumbnail_width', '' );

        if($setting === null || empty($setting) || ctype_space($setting) || !is_numeric($setting)) {
            $setting = "0";
        }

        $setting = intval($setting);

        if($setting < 0) {
            $setting = 0;
        } else if($setting > 1000) {
            $setting = 1000;
        }

        return $setting;
    }

    /**
     * Retrieves a valid setting for thumbnail height
     */
    static function get_thumbnail_height() {
        $setting = get_option( 'printess_thumbnail_height', '' );

        if($setting === null || empty($setting) || ctype_space($setting) || !is_numeric($setting)) {
            $setting = "0";
        }

        $setting = intval($setting);

        if($setting < 0) {
            $setting = 0;
        } else if($setting > 1000) {
            $setting = 1000;
        }

        return $setting;
    }

    /**
     * Retrieves the configured legal text
     */
    static function get_legal_text() {
        $setting = get_option( 'printess_legal_notice', '' );

        return null !== $setting && !empty($setting) ? $setting : "";
    }

    /**
     * Returns true in case the integration should display an enter design name dialog during add to basket
     */
    static function get_enforce_design_name() {
        return get_option( 'printess_enforce_design_name', 'wpadminbar, page' );
    }

    /**
	 * Adds the custom Printess settings menu to the admin menu.
	 */
    static function register_settings() {
        include_once("printess-api.php");

        add_settings_section(
            'printess_settings_section', // section slug .
            __( 'Printess Settings', 'printess-editor' ),
            function() {},
            'printess-settings'
        );
    
        register_setting( 'printess-settings', 'printess_shop_token' );
    
        add_settings_field(
            'printess_shop_token', // setting slug .
            __( 'Shop Token', 'printess-editor' ),
            function() {
                $setting = PrintessAdminSettings::get_shop_token();

                ?><input type="text" style="min-width: 50%;" name="printess_shop_token" value="<?php echo esc_attr( $setting ); ?>"><?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting( 'printess-settings', 'printess_service_token' );
    
        add_settings_field(
            'printess_service_token',
            __( 'Service Token', 'printess-editor' ),
            function() {
                $setting = PrintessAdminSettings::get_service_token();

                ?><input type="text" style="min-width: 50%;" name="printess_service_token" value="<?php echo esc_attr( $setting ); ?>"><?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_api_domain',
            array(
                'type'    => 'string',
                'default' => 'api.printess.com',
            )
        );
    
        add_settings_field(
            'printess_api_domain',
            __( 'Api Domain', 'printess-editor' ),
            function() {
                $setting = PrintessAdminSettings::get_domain();

                ?><input type="text" style="min-width: 50%;" name="printess_api_domain" value="<?php echo esc_attr( $setting ); ?>"><?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_embed_html_url',
            array(
                'type'    => 'string',
                'default' => 'https://editor.printess.com/printess-editor/embed.html',
            )
        );
    
        add_settings_field(
            'printess_embed_html_url',
            __( 'Embed Html Url', 'printess-editor' ),
            function() {
                $setting = PrintessAdminSettings::get_embed_html_url();

                ?><input type="text" style="min-width: 50%;" name="printess_embed_html_url" value="<?php echo esc_attr( $setting ); ?>"><?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_customize_button_class',
            array(
                'type'    => 'string',
                'default' => '',
            )
        );
    
        add_settings_field(
            'printess_ids_to_hide',
            __( 'Ids to hide when showing editor', 'printess-editor' ),
            function() {
                $setting = PrintessAdminSettings::get_ids_to_hide();

                ?><input type="text" style="min-width: 50%;" name="printess_ids_to_hide" value="<?php echo esc_attr( $setting ); ?>"><?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_ids_to_hide',
            array(
                'type'    => 'string',
                'default' => 'wpadminbar, page',
            )
        );
    
        add_settings_field(
            'printess_class_names_to_hide',
            __( 'Class names to hide when showing editor', 'printess-editor' ),
            function() {
                $setting = PrintessAdminSettings::get_class_names_to_hide();

                ?><input type="text" style="min-width: 50%;" name="printess_class_names_to_hide" value="<?php echo esc_attr( $setting ); ?>"><?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_class_names_to_hide',
            array(
                'type'    => 'string',
                'default' => 'wp-site-blocks',
            )
        );
    
        add_settings_field(
            'printess_customize_button_class',
            __( 'Additional classes for customize button', 'printess-editor' ),
            function() {
                $setting = PrintessAdminSettings::get_customize_button_class();

                ?><input type="text" style="min-width: 50%;" name="printess_customize_button_class" value="<?php echo esc_attr( $setting ); ?>"><?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_approval',
            array(
                'type'    => 'string',
                'default' => 'auto',
            )
        );
    
        add_settings_field(
            'printess_approval',
            __( 'Order Approval Mode', 'printess-editor' ),
            function() {
                $setting         = PrintessAdminSettings::get_approval_mode();
                $auto_selected   = '';
                $manual_selected = '';
            
                if ( 'auto' === $setting ) {
                    $auto_selected = 'selected';
                }
            
                if ( 'manual' === $setting ) {
                    $manual_selected = 'selected';
                }
            
                ?>
                <select name="printess_approval">
                    <option value="auto" <?php echo esc_html( $auto_selected ); ?>><?php echo esc_html__( 'Automatic', 'printess-editor' ); ?></option>
                    <option value="manual" <?php echo esc_html( $manual_selected ); ?>><?php echo esc_html__( 'Manual', 'printess-editor' ); ?></option>
                </select>
                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_add_to_cart_after_customization',
            array(
                'type'    => 'boolean',
                'default' => true,
            )
        );
    
        add_settings_field(
            'printess_add_to_cart_after_customization',
            __( 'Add to cart after customization', 'printess-editor' ),
            function() {
                $setting = PrintessAdminSettings::get_add_to_cart_after_customization();
                $checked = '';
            
                if ( $setting ) {
                    $checked = 'checked';
                }
            
                ?><input type="checkbox" name="printess_add_to_cart_after_customization" <?php echo esc_html( $checked ); ?>><?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_show_customize_on_archive_page',
            array(
                'type'    => 'boolean',
                'default' => true,
            )
        );
    
        add_settings_field(
            'printess_show_customize_on_archive_page',
            __( 'Show customize button on archive page', 'printess-editor' ),
            function() {
                $setting = PrintessAdminSettings::get_show_customize_on_archive_page();
                $checked = '';
            
                if ( $setting ) {
                    $checked = 'checked';
                }
            
                ?><input type="checkbox" name="printess_show_customize_on_archive_page" <?php echo esc_html( $checked ); ?>><?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_show_prices_in_editor',
            array(
                'type'    => 'boolean',
                'default' => true,
            )
        );
    
        add_settings_field(
            'printess_show_prices_in_editor',
            __( 'Show prices inside editor', 'printess-editor' ),
            function() {
                $setting = get_option( 'printess_show_prices_in_editor', 'off' );
                $checked = '';
            
                if ( 'on' === $setting ) {
                    $checked = 'checked';
                }
            
                ?>
                
                <input type="checkbox" name="printess_show_prices_in_editor" <?php echo esc_html( $checked ); ?>>
            
                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_show_product_name_in_editor',
            array(
                'type'    => 'boolean',
                'default' => true,
            )
        );
    
        add_settings_field(
            'printess_show_product_name_in_editor',
            __( 'Show product name inside editor', 'printess-editor' ),
            function() {
                $setting = get_option( 'printess_show_product_name_in_editor', 'off' );
                $checked = '';
            
                if ( 'on' === $setting ) {
                    $checked = 'checked';
                }
            
                ?>
                
                <input type="checkbox" name="printess_show_product_name_in_editor" <?php echo esc_html( $checked ); ?>>
            
                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_legal_notice',
            array(
                'type'    => 'string',
                'default' => 'auto',
            )
        );
    
        add_settings_field(
            'printess_legal_notice',
            __( 'Display legal info in case prices are displayed inside editor', 'printess-editor' ),
            function() {
                $setting = PrintessAdminSettings::get_legal_text();

                ?>

                <input type="text" style="min-width: 50%;" name="printess_legal_notice" value="<?php echo esc_attr( $setting ); ?>">

                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_output_format',
            array(
                'type'    => 'string',
                'default' => 'auto',
            )
        );
    
        add_settings_field(
            'printess_output_format',
            __( 'Output Format', 'printess-editor' ),
            function() {
                $setting = get_option( 'printess_output_format', 'pdf' );

                if ( empty( $setting ) ) {
                    $setting = 'pdf';
                }
            
                ?>
                    <select name="printess_output_format">
                        <option value="pdf" <?php echo esc_html( 'pdf' === $setting ? 'selected' : '' ); ?>><?php echo esc_html__( 'PDF', 'printess-editor' ); ?></option>
                        <option value="png" <?php echo esc_html( 'png' === $setting ? 'selected' : '' ); ?>><?php echo esc_html__( 'PNG', 'printess-editor' ); ?></option>
                        <option value="jpg" <?php echo esc_html( 'jpg' === $setting ? 'selected' : '' ); ?>><?php echo esc_html__( 'JPG', 'printess-editor' ); ?></option>
                        <option value="tif" <?php echo esc_html( 'tif' === $setting ? 'selected' : '' ); ?>><?php echo esc_html__( 'TIF', 'printess-editor' ); ?></option>
                    </select>
                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_jpg_compression',
            array(
                'type'    => 'string',
                'default' => 'auto',
            )
        );
    
        add_settings_field(
            'printess_jpg_compression',
            __( 'JPEG Compression', 'printess-editor' ),
            function() {
                $setting = get_option( 'printess_jpg_compression', '90' );

                if ( empty( $setting ) ) {
                    $setting = '90';
                }
            
                try {
                    if ( intval( $setting ) < 0 ) {
                        $setting = '1';
                    } elseif ( intval( $setting ) > 100 ) {
                        $setting = '100';
                    }
                } catch ( \Exception $ex ) {
                    $setting = '90';
                }
            
                ?>
            
                <input type="number" style="min-width: 50%;" name="printess_jpg_compression" value="<?php echo esc_attr( $setting ); ?>" min="1" max="100">

                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_enable_design_save',
            array(
                'type'    => 'boolean',
                'default' => false,
            )
        );
    
        add_settings_field(
            'printess_enable_design_save',
            __( 'Enable saving of designs', 'printess-editor' ),
            function() {
                $setting = get_option( 'printess_enable_design_save', false );
                $checked = '';
            
                if ( $setting ) {
                    $checked = 'checked';
                }
            
                ?>
                    
                <input type="checkbox" name="printess_enable_design_save" <?php echo esc_html( $checked ); ?>>
            
                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_saved_design_lifetime',
            array(
                'type'    => 'string',
                'default' => '30',
            )
        );
    
        add_settings_field(
            'printess_saved_design_lifetime',
            __( 'Saved Design lifetime (Days)', 'printess-editor' ),
            function() {
                $setting = get_option( 'printess_saved_design_lifetime', 30 );

                if ( ! isset( $setting ) || empty( $setting ) ) {
                    $setting = 30;
                }
            
                $setting = intval( $setting );
            
                ?>
                    <input type="number" min="0" style="min-width: 50%;" id="printess_saved_design_lifetime_in" name="printess_saved_design_lifetime" value="<?php echo esc_attr( $setting ); ?>"><span id="printess_days"> <?php echo 0 === $setting ? esc_html__( 'Unlimited', 'printess-editor' ) : $setting; ?> </span><span><?php echo esc_html__( 'days', 'printess-editor' ); ?></span>
            
                    <script>
                        const lifeTimeInput = document.getElementById("printess_saved_design_lifetime_in");
            
                        if(lifeTimeInput) {
                            const updateDisplayText = () => {
                                let days = parseInt(lifeTimeInput.value);
                
                                if(days == 0) {
                                    days = "<?php echo esc_html__( 'Unlimited', 'printess-editor' ); ?>";
                                }
                                else if(days < 0) {
                                    days = 30;
                                }
                
                                const span = document.getElementById("printess_days");
                
                                if(span) {
                                    span.innerHTML = " " + days + " ";
                                }
                            };
            
                            lifeTimeInput.addEventListener("change", (event) => {
                                updateDisplayText();
                            });
        
                            lifeTimeInput.addEventListener("keydown", (event) => {
                                updateDisplayText();
                            });
                            }
                        </script>
                    <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_ordered_design_lifetime',
            array(
                'type'    => 'string',
                'default' => '30',
            )
        );
    
        add_settings_field(
            'printess_ordered_design_lifetime',
            __( 'Ordered Design lifetime (Days)', 'printess-editor' ),
            function() {
                $setting = get_option( 'printess_ordered_design_lifetime', 30 );

                if ( ! isset( $setting ) || empty( $setting ) ) {
                    $setting = 30;
                }
            
                $setting = intval( $setting );
            
                ?>
                    <input type="number" min="0" style="min-width: 50%;" id="printess_ordered_design_lifetime_in" name="printess_ordered_design_lifetime" value="<?php echo esc_attr( $setting ); ?>"><span id="printess_ordered_days"> <?php echo 0 === $setting ? esc_html__( 'Unlimited', 'printess-editor' ) : $setting; ?> </span><span><?php echo esc_html__( 'days', 'printess-editor' ); ?></span>
            
                    <script>
                        const orderedDesignLifeTimeInput = document.getElementById("printess_ordered_design_lifetime_in");
            
                        if(orderedDesignLifeTimeInput) {
                            const updateDisplayText = () => {
                                let days = parseInt(orderedDesignLifeTimeInput.value);
            
                                if(days == 0) {
                                    days = "<?php echo esc_html__( 'Unlimited', 'printess-editor' ); ?>";
                                }
                                else if(days < 0) {
                                    days = 30;
                                }
            
                                const span = document.getElementById("printess_ordered_days");
            
                                if(span) {
                                    span.innerHTML = " " + days + " ";
                                }                    
                            };
            
                            orderedDesignLifeTimeInput.addEventListener("change", (event) => {
                                updateDisplayText();
                            });
            
                            orderedDesignLifeTimeInput.addEventListener("keydown", (event) => {
                                updateDisplayText();
                            });
                        }
                    </script>
                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_thumbnail_width',
            array(
                'type'    => 'string',
                'default' => '0',
            )
        );
    
        add_settings_field(
            'printess_thumbnail_width',
            __( 'The width of the rendered thumbnail', 'printess-editor' ),
            function() {
                ?>

                <input type="text" style="min-width: 50%;" name="printess_thumbnail_width" value="<?php echo esc_attr( PrintessAdminSettings::get_thumbnail_width() ); ?>">

                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_thumbnail_height',
            array(
                'type'    => 'string',
                'default' => '0',
            )
        );
    
        add_settings_field(
            'printess_thumbnail_height',
            __( 'The height of the rendered thumbnail', 'printess-editor' ),
            function() {
                ?>

                <input type="text" style="min-width: 50%;" name="printess_thumbnail_height" value="<?php echo esc_attr( PrintessAdminSettings::get_thumbnail_height() ); ?>">

                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_access_token',
            array(
                'type'    => 'string',
                'default' => '',
            )
        );
    
        add_settings_field(
            'printess_access_token',
            __( 'Access Token', 'printess-editor' ),
            function() {
                $setting = PrintessAdminSettings::get_access_token();

                ?>

                <input type="text" style="min-width: 50%;" name="printess_access_token" value="<?php echo esc_attr( $setting ); ?>">

                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_system_default_dropshipping',
            array(
                'type'    => 'string',
                'default' => '-1',
            )
        );
    
        add_settings_field(
            'printess_system_default_dropshipping',
            __( 'Overwrite product drop shipper in case of template mode', 'printess-editor' ),
            function() {
                include_once("printess-api.php");

                $dropshipping = array();

                if ( ! empty( PrintessAdminSettings::get_service_token() ) ) {
                    try {
                        $dropshipping = PrintessApi::get_dropshipping_info();
                    } catch ( \Exception $ex ) {
                        $dropshipping[] = array(
                            'id'                 => 0,
                            'userId'             => '',
                            'type'               => 'error',
                            'display'            => 'Unable to load drop shippers: ' . $ex->getMessage(),
                            'productDefinitions' => array(),
                        );
                    }
                }
            
                $selected_dropshipping_id = get_option( 'printess_system_default_dropshipping', '' );
            
                if ( null === $selected_dropshipping_id || '' === $selected_dropshipping_id ) {
                    $selected_dropshipping_id = '-2';
                }
                
                PrintessHtmlHelpers::render_select_with_option_groups( 'printess_system_default_dropshipping', '', $dropshipping, $selected_dropshipping_id, true );
            
                ?>
                
                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_ask_for_name_on_resave',
            array(
                'type'    => 'boolean',
                'default' => false,
            )
        );
    
        add_settings_field(
            'printess_ask_for_name_on_resave',
            __( 'Provide input for design name on second save', 'printess-editor' ),
            function() {
                $setting = get_option( 'printess_ask_for_name_on_resave', 'wpadminbar, page' );
                $checked = '';
            
                if ( $setting ) {
                    $checked = 'checked';
                }
            
                ?>
                <input type="checkbox" name="printess_ask_for_name_on_resave" <?php echo esc_html( $checked ); ?>>
                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_show_original_product_in_basket',
            array(
                'type'    => 'string',
                'default' => 'auto',
            )
        );
    
        add_settings_field(
            'printess_show_original_product_in_basket',
            __( 'Show original product in basket', 'printess-editor' ),
            function() {
                $setting = get_option( 'printess_show_original_product_in_basket', true );
                $checked = '';
            
                if ( null === $setting || empty( $setting ) ) {
                    $setting = true;
                }
            
                if ( 'on' === $setting ) {
                    $checked = 'checked';
                }
            
                ?>
                    
                <input type="checkbox" name="printess_show_original_product_in_basket" <?php echo esc_html( $checked ); ?> >
            
                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_displaylineitemid',
            array(
                'type'    => 'boolean',
                'default' => false,
            )
        );
    
        add_settings_field(
            'printess_displaylineitemid',
            __( 'Display line item id in order view', 'printess-editor' ),
            function() {
                $setting = get_option( 'printess_displaylineitemid', 'wpadminbar, page' );
                $checked = '';
            
                if ( 'on' === $setting ) {
                    $checked = 'checked';
                }
            
                ?>

                <input type="checkbox" name="printess_displaylineitemid" <?php echo esc_html( $checked ); ?>>

                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        register_setting(
            'printess-settings',
            'printess_debug',
            array(
                'type'    => 'boolean',
                'default' => false,
            )
        );
    
        add_settings_field(
            'printess_debug',
            __( 'Enable Debug Mode', 'printess-editor' ),
            function() {
                $setting = PrintessAdminSettings::get_debug();
                $checked = '';
            
                if ( $setting ) {
                    $checked = 'checked';
                }
            
                ?>

                <input type="checkbox" name="printess_debug" <?php echo esc_html( $checked ); ?>>

                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );

        register_setting(
            'printess-settings',
            'printess_default_theme',
            array(
                'type'    => 'string',
                'default' => '',
            )
        );
    
        add_settings_field(
            'printess_default_theme',
            __( 'Default Editor Theme', 'printess-editor' ),
            function() {
                $setting = PrintessAdminSettings::get_default_theme();

                $setting = null !== $setting && !empty($setting) ? $setting : "";

                ?>

                <input type="text" style="min-width: 50%;" name="printess_default_theme" value="<?php echo esc_attr( $setting ); ?>">

                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );

        register_setting(
            'printess-settings',
            'printess_user_fields',
            array(
                'type'    => 'string',
                'default' => '',
            )
        );
    
        add_settings_field(
            'printess_user_fields',
            __( 'Push user fields', 'printess-editor' ),
            function() {
                $setting = PrintessAdminSettings::get_user_fields();

                $setting = null !== $setting && !empty($setting) ? $setting : "";

                ?>

                <textarea style="min-width: 50%; min-height: 200px:" name="printess_user_fields" ?><?php echo esc_attr( $setting ); ?></textarea>

                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    
        add_menu_page(
            'Printess',
            'Printess', // title .
            'manage_options',
            'printess-settings',
            'printess_options_page_html',
            plugin_dir_url( __FILE__ ) . 'images/icon.png',
            58
        );

        register_setting(
            'printess-settings',
            'printess_enforce_design_name',
            array(
                'type'    => 'boolean',
                'default' => false,
            )
        );
    
        add_settings_field(
            'printess_enforce_design_name',
            __( 'Enforce Design Name during add to basket', 'printess-editor' ),
            function() {
                $setting = get_option( 'printess_enforce_design_name', '' );

                if ( empty( $setting ) ) {
                    $setting = 'pdf';
                }
            
                ?>
                    <select name="printess_enforce_design_name">
                        <option value="" <?php echo esc_html( '' === $setting ? 'selected' : '' ); ?>><?php echo esc_html__( 'Do not use', 'printess-editor' ); ?></option>
                        <option value="optional" <?php echo esc_html( 'optional' === $setting ? 'selected' : '' ); ?>><?php echo esc_html__( 'optional', 'printess-editor' ); ?></option>
                        <option value="enforce" <?php echo esc_html( 'enforce' === $setting ? 'selected' : '' ); ?>><?php echo esc_html__( 'enforce', 'printess-editor' ); ?></option>
                    </select>
                <?php
            },
            'printess-settings',
            'printess_settings_section'
        );
    }
}

?>