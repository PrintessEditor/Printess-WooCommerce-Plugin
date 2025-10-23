<?php

if ( class_exists( 'PrintessProductHelpers', false ) ) return;

class PrintessProductHelpers {
    private $_product = null;
    private $_base_product = null;

    function __construct(int $product_id) {
        $this->_product = wc_get_product($product_id);

        if ( ! isset( $this->_product ) || false === $this->_product ) {
		    throw new \Exception("Unknown product id " + $product_id);
	    }

        $parent_id = $this->_product->get_data()["parent_id"];

        if($parent_id > 0) {
            $this->_base_product = wc_get_product( $parent_id );
        } else {
            $this->_base_product = $this->_product;
        }        
    }

    /**
     * Returns the current product. In case of a variant, returns the variants base product
    */    
    public function get_base_product() {
        return $this->_base_product;
    }

    /**
     * Returns the current product.
    */    
    public function get_product() {
        return $this->_product;
    }

    /**
     * Returns all attributes related to a product (parent product in case of variation)
    */
    public function get_attributes() {
        $product = $this->get_base_product();

        return PrintessProductHelpers::get_product_attributes($product);
    }

    private function get_attribute_definition($attribute_name, ?array $attrbutes = null) {
        if(null === $attrbutes) {
            $attributes = $this->get_attributes();
        }

        if(array_key_exists($attribute_name, $attributes)) {
            return $attributes[$attribute_name];
        }

        foreach($attrbutes as $key => $value) {
            if($value["name"] === $attribute_name) {
                return $value;
            }
        }

        return null;
    }

    public function map_attribute_name_and_value($name, $value) {
        $ret = array(
            "name" => $name,
            "value" => $value
        );

        $attrbute = $this->get_attribute_definition($name);

        if(null === $attrbute) {
            return $ret;
        }

        $ret["name"] = $attrbute["name"];

        if(null !== $attrbute["valueKeys"]) {
            for($i = 0; $i < count($attrbute["valueKeys"]); ++$i) {
                if($attrbute["valueKeys"][$i] === $value && $i < count($attrbute["values"])) {
                    $ret["value"] = $attrbute["values"][$i]; 
                    break;
                }
            }
        }

        return $ret;
    }



    public static function get_product_attributes($product) {
        $ret = array();

        if(isset($product) && false !== $product) {
            foreach ( $product->get_attributes() as $key => $value ) {
                $terms = $value->get_terms();
                $options = $value['options'];
                $optionsKeys = null;
                $name = $value['name'];

                if(null !== $terms) {
                    $name = wc_attribute_label($name);
                    $options = array();

                    foreach($terms as $term_slug => $term) {
                        $options[] = $term->name;

                        if(null === $optionsKeys) {
                            $optionsKeys = array();
                        }

                        $optionsKeys[] = $term->slug;
                    }
                }

                $ret[ $key ] = array(
                    'key'    => $key,
                    'name'   => $name,
                    'values' => $options,
                    'valueKeys' => $optionsKeys,
                    'usedForVariants' => $value["variation"] === true
                );
            }
        }

        return $ret;
    }

    /** 
     * Test if the given object is an array, check if the array has a key, produce a warning if not
     */
    function transform_meta_value_to_template_name(mixed $item, string $key): string {
        if(isset($item)) {
            if(is_array($item)) {
                if(array_key_exists($key, $item)) {
                    $name = $item[$key];

                    if(isset($name) && is_string($name) && !empty($name)) {
                        return $name;
                    }
                }
            } else if(is_string($item) && !empty($item)) {
                return $item;
            }
        }

        if(isset($item) && !is_string($item)) {
            try {
                $logger = wc_get_logger();
                $logger->error( 'Printess Editor Integration: Unexpected data type in printess template name meta data: ' . json_encode( $item ), array( 'source' => 'printess-editor' ) );
            } catch(\Exception $e) {
                //Intentionally left blank, nothing to do here
            }
        }

        return "";
    }

    public function get_template_name($check_base_product = false): string {
      $template_name = $this->transform_meta_value_to_template_name($this->_product->get_meta( 'printess_template', true ), "printess_template");

      $template_name = $template_name === null || empty($template_name) ? "" : $template_name;

      if($check_base_product && $this->_product !== $this->_base_product && empty($template_name) ) {
            //We can only return the base product template name if no variation has set a template name. In this case the whole product and its variations is treated as printess products.
            //If at least one variant has a template name set, then the current variation (which has no template name set) should not be treated as printess product.

            if ($this->_base_product->is_type( 'variable' )) {
                $variant_has_template_name = false;

                foreach ($this->_base_product->get_available_variations() as $key => $variation) { 
                    $variation_template_name = "";

                    if(is_array($variation)) {
                        if(array_key_exists("printess_template_name", $variation)) {
                            $variation_template_name = $variation["printess_template_name"];
                        }
                    } else {
                        $variation_template_name = $this->transform_meta_value_to_template_name($variation->get_meta( 'printess_template_name', true ), "printess_template_name");
                    }

                    if($variation_template_name !== null && !empty($variation_template_name)) {
                        $variant_has_template_name = true;
                        break;
                    }
                }

                if(!$variant_has_template_name) {
                    $template_name = $this->transform_meta_value_to_template_name($this->_base_product->get_meta( 'printess_template', true ), "printess_template");
                    $template_name = $template_name === null || empty($template_name) ? "" : $template_name;
                }
            }
      }
      

      return $template_name;
    }

    public function get_formfield_mappings(): string {
        $product = $this->get_base_product();
        $ret = $product->get_meta( 'printess_custom_formfield_mappings', true );

      return $ret === null || empty($ret) ? "" : $ret;
    }

    public function get_page_count_option(): string {
      $product = $this->get_base_product();
      $ret = $product->get_meta( 'printess_page_count_option', true );

      return $ret === null || empty($ret) ? "" : $ret;
    }

    //$product->get_meta( 'printess_item_usage', true );


    /**
     * Renders the html for the product specific options that are available inside the product configuration
     */
    public static function render_product_option_pane() {
        require_once(plugin_dir_path(__DIR__) . "includes/printess-api.php");
        require_once(plugin_dir_path(__DIR__) . "includes/printess-html-helpers.php");
        require_once(plugin_dir_path(__DIR__) . "includes/printess-admin-settings.php");

        $product_helpers = new PrintessProductHelpers(get_the_ID());

        $dropshipping = PrintessApi::get_dropshipping_info();

        echo '<div id="printess_product_data" class="panel woocommerce_options_panel wc-metaboxes-wrapper hidden">';

        woocommerce_wp_text_input(
            array(
                'id'          => 'printess_template',
                'value'       => $product_helpers->get_template_name(),
                'label'       => 'Template',
                'description' => 'The name of the template within Printess',
            )
        );

        $selected_dropshipping_id = get_post_meta( get_the_ID(), 'printess_dropshipping', true );

        PrintessHtmlHelpers::render_select_with_option_groups( 'printess_dropshipping', __( 'Dropshipping', 'printess-editor' ), $dropshipping, $selected_dropshipping_id );

        woocommerce_wp_textarea_input(
            array(
                'id'          => 'printess_custom_formfield_mappings',
                'value'       => $product_helpers->get_formfield_mappings(),
                'label'       => 'Formfield mappings',
                'description' => 'Map variant attributes to Printess form fields',
            )
        );

        $printess_load_user_templates_url = 'https://' . PrintessAdminSettings::get_domain() . '/templates/user/load';
        $printess_authorization           = 'Bearer ' . PrintessAdminSettings::get_service_token();

        ?>
        <hr />
        <h2>Merge templates</h2>
        <?php
        woocommerce_wp_text_input(
            array(
                'id'          => 'printess_merge_template_1',
                'value'       => get_post_meta( get_the_ID(), 'printess_merge_template_1', true ),
                'label'       => __( 'Merge Template 1', 'printess-editor' ),
                'description' => __( 'The name of the optional 1st merge template within Printess', 'printess-editor' ),
            )
        );
        woocommerce_wp_text_input(
            array(
                'id'          => 'printess_merge_template_2',
                'value'       => get_post_meta( get_the_ID(), 'printess_merge_template_2', true ),
                'label'       => __( 'Merge Template 2', 'printess-editor' ),
                'description' => __( 'The name of the optional 2nd merge template within Printess', 'printess-editor' ),
            )
        );
        woocommerce_wp_text_input(
            array(
                'id'          => 'printess_merge_template_3',
                'value'       => get_post_meta( get_the_ID(), 'printess_merge_template_3', true ),
                'label'       => __( 'Merge Template 3', 'printess-editor' ),
                'description' => __( 'The name of the optional 3rd merge template within Printess', 'printess-editor' ),
            )
        );
        ?>

        <hr />
        <?php
        woocommerce_wp_select(
            array(
                'id'          => 'printess_output_type',
                'value'       => get_post_meta( get_the_ID(), 'printess_output_type', true ),
                'label'       => __( 'Output Type', 'printess-editor' ),
                'description' => __( 'The output file type. Defaults to output a pdf file.', 'printess-editor' ),
                'options'     => array(
                    ''    => 'Use global setting',
                    'pdf' => 'Pdf File',
                    'png' => 'Png File',
                    'jpg' => 'Jpg File',
                    'tif' => 'Tif File',
                ),
            )
        );
        woocommerce_wp_text_input(
            array(
                'id'          => 'printess_dpi',
                'value'       => get_post_meta( get_the_ID(), 'printess_dpi', true ),
                'label'       => __( 'Output DPI', 'printess-editor' ),
                'description' => __( 'The used output dpi. Defaults to 300', 'printess-editor' ),
            )
        );

        $compression_ratio = get_post_meta( get_the_ID(), 'printess_jpg_compression', true );

        if ( ! isset( $compression_ratio ) || empty( $compression_ratio ) ) {
            $compression_ratio = '0';
        }

        woocommerce_wp_text_input(
            array(
                'id'                => 'printess_jpg_compression',
                'value'             => $compression_ratio,
                'label'             => __( 'JPG compression', 'printess-editor' ),
                'description'       => __( 'The jpg compression ratio. Defaults to 90. 0 = Use system setting', 'printess-editor' ),
                'type'              => 'number',
                'custom_attributes' => array(
                    'min' => '0',
                    'max' => '100',
                ),
            )
        );


        $printess_output_files = get_post_meta( get_the_ID(), 'printess_output_files', true );

        if ( ! isset( $printess_output_files ) || empty( $printess_output_files ) ) {
            $printess_output_files = "";
        }

        woocommerce_wp_text_input(
            array(
                'id'                => 'printess_output_files',
                'value'             => $printess_output_files,
                'label'             => __( 'Document based output settings', 'printess-editor' ),
                'description'       => __( 'Document specific output settings (e.g. different file format for different documents)', 'printess-editor' ),
                'custom_attributes' => array(
                    'min' => '0',
                    'max' => '100',
                ),
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'                => 'printess_item_usage',
                'value'             => get_post_meta( get_the_ID(), 'printess_item_usage', true ),
                'label'             => __( 'Item Usage', 'printess-editor' ),
                'description'       => __( 'Json configuration for item based usage pricing', 'printess-editor' ),
                'custom_attributes' => array(
                    'min' => '0',
                    'max' => '100',
                ),
            )
        );
        ?>

    <hr />
        <?php
            woocommerce_wp_select(
                array(
                    'id'          => 'printess_cart_redirect_page',
                    'value'       => get_post_meta( get_the_ID(), 'printess_cart_redirect_page', true ),
                    'label'       => __( 'Redirect page', 'printess-editor' ),
                    'description' => __( 'The page that should be opened after the product has been added to the cart.', 'printess-editor' ),
                    'options'     => array_merge( array( '' => __( 'Default', 'printess-editor' ) ), printess_get_available_pages() ),
                )
            );
        ?>

        <?php
            $values = array('' => __( 'Do not use', 'printess-editor' ));

            $attributes = $product_helpers->get_attributes();
            foreach($attributes as $key => $attribute) {
                $values[$attribute["name"]] = $attribute["name"];
            }

            woocommerce_wp_select(
                array(
                    'id'          => 'printess_page_count_option',
                    'value'       => $product_helpers->get_page_count_option(),
                    'label'       => __( 'Page Count Option', 'printess-editor' ),
                    'description' => __( 'The option that contains the page count for configurable book templates.', 'printess-editor' ),
                    'options'     => $values,
                )
            );
        ?>

    <hr />

        <hr />
        <h2>Search for published templates</h2>
        <p class="form-field">
            <label for="printess-template-name">Template Name</label>
            <input type="text" id="printess-template-name" />
        </p>

        <div class="toolbar">
            <button type="button" id="printess-load-templates" class="button button-primary">Search</button>
        </div>

        <p class="form-field">
            <label for="printess-templates">Available Templates</label>
            <select id="printess-templates">
            </select>
        </p>
        <div class="toolbar">
            <button type="button" style="display:none;" id="printess-use-as-template-name" class="button button-primary">Use as template name</button>
            <button type="button" style="display:none;" id="printess-use-as-merge-template-name-1" class="button button-primary">Use as merge template 1</button>
            <button type="button" style="display:none;" id="printess-use-as-merge-template-name-2" class="button button-primary">Use as merge template 2</button>
            <button type="button" style="display:none;" id="printess-use-as-merge-template-name-3" class="button button-primary">Use as merge template 3</button>
        </div>

        <hr />
        <?php
        woocommerce_wp_select(
            array(
                'id'          => 'printess_ui_version',
                'value'       => get_post_meta( get_the_ID(), 'printess_ui_version', true ),
                'label'       => __( 'Buyer side user interface', 'printess-editor' ),
                'description' => __( 'PanelUi or the old deprecated iframe intgeration', 'printess-editor' ),
                'options'     => array(
                    'bcui' => 'PanelUi',
                    'classical'    => 'Deprecated iframe integration',
                ),
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'          => 'printess_editor_theme',
                'value'       => get_post_meta( get_the_ID(), 'printess_editor_theme', true ),
                'label'       => 'Editor Theme',
                'description' => 'The name of the theme that should be used for this product',
            )
        );
        ?>
        <script>
        document.getElementById("printess-load-templates").addEventListener("click", () => {
            loadTemplates();
        });

        document.getElementById("printess-use-as-template-name").addEventListener("click", () => {
            document.getElementById("printess_template").value = document.getElementById("printess-templates").value;
        });

        for (let n = 1; n <= 3; n++) {
            document.getElementById("printess-use-as-merge-template-name-" + n).addEventListener("click", () => {
                document.getElementById("printess_merge_template_" + n).value = document.getElementById("printess-templates").value;
            });
        }

        /* document.getElementById("printess-templates").addEventListener("change", () => {
            document.getElementById("printess_template").value = document.getElementById("printess-templates").value;
        }); */

        async function loadTemplates() {
            const body = JSON.stringify({
            templateName: document.getElementById("printess-template-name").value,
            publishedOnly: true
            });

            const response = await fetch(<?php echo wp_json_encode( $printess_load_user_templates_url ); ?>, {
                method: "POST",
                mode: "cors",
                cache: "no-cache",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": <?php echo wp_json_encode( $printess_authorization ); ?>
                },
                redirect: "follow",
                referrerPolicy: "no-referrer",
                body: body
            });

            if (response.ok) {
                const selected = document.getElementById("printess_template").value;
                const json = await response.json();
                const select = document.getElementById("printess-templates");
                const length = select.options.length;

                for (i = length - 1; i >= 0; i--) {
                    select.options[i].remove();
                }

                const defaultOption = document.createElement("option");
                defaultOption.text = "Please select a template...";
                select.options.add(defaultOption)

                json.ts.sort((a, b) => a.n.localeCompare(b.n)).forEach(template => {
                    const option = document.createElement("option");
                    option.text = template.n;

                    if (template.n == selected) {
                    option.selected = true;
                    defaultOption.remove();
                    }

                    select.options.add(option)
                });

                document.getElementById("printess-use-as-template-name").style.display = null;

                for (let n = 1; n <= 3; n++) {
                    document.getElementById("printess-use-as-merge-template-name-" + n).style.display = null;
                }
            }
        }
        </script>

        <?php

        echo '</div>';
    }

    //$product->get_meta( 'printess_item_usage', true );   
}