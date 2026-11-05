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

if ( class_exists( 'PrintessTable', false ) ) {
	return;
}

/**
 * A CSS grid table, used for the saved designs and the personalized products of an order.
 */
class PrintessTable {

	/**
	 * Column titles.
	 *
	 * @var array
	 */
	private $columns = array();

	/**
	 * Rows of cell values.
	 *
	 * @var array
	 */
	private $rows = array();

	/**
	 * Settings, such as tableClasses.
	 *
	 * @var array
	 */
	private $settings = array();

	/**
	 * Creates a table.
	 *
	 * @param array|null $settings Settings, such as tableClasses.
	 */
	public function __construct( $settings = null ) {
		if ( isset( $settings ) && is_array( $settings ) ) {
			$this->settings = $settings;
		}
	}

	/**
	 * Adds one column, or one per entry of an array.
	 *
	 * @param mixed $title The title or titles.
	 */
	public function add_column( mixed $title ) {
		if ( null === $title || empty( $title ) ) {
			$this->columns[] = '';
		} elseif ( is_array( $title ) ) {
			foreach ( $title as $label ) {
				$this->columns[] = null === $label || empty( $label ) ? '' : $label;
			}
		} else {
			$this->columns[] = null !== $title && ! empty( $title ) ? $title : '';
		}
	}

	/**
	 * Starts a new row.
	 *
	 * @param array|null $values The row's first cells.
	 */
	public function add_row( ?array $values = null ) {
		$new_row = array();

		if ( null !== $values && is_array( $values ) ) {
			$new_row = array_merge( $values );
		}

		$this->rows[] = $new_row;
	}

	/**
	 * Adds a cell, or several, to the last row.
	 *
	 * @param mixed $value The value or values.
	 */
	public function add_value( $value ) {
		if ( 0 === count( $this->rows ) ) {
			$this->rows[] = array();
		}

		$this->rows[ count( $this->rows ) - 1 ] = array_merge( $this->rows[ count( $this->rows ) - 1 ], null !== $value && is_array( $value ) ? $value : array( $value ) );
	}

	/**
	 * Prints the table as a CSS grid.
	 */
	private function render_css_grid() {
		if ( 0 < count( $this->rows ) ) {
			$additional_classes = array_key_exists( 'tableClasses', $this->settings ) ? $this->settings['tableClasses'] : '';
			?>
      <script>
        function printessShowConfirm(message, destinationUrl) {
          if(confirm(message) === true) {
            window.location.replace(destinationUrl);
          }
        }
      </script>
      <div class="printess_table columns_<?php echo esc_attr( count( $this->columns ) ); ?> <?php echo esc_attr( $additional_classes ); ?>">
          <div class="header">
          <?php
			foreach ( $this->columns as $title ) {
				?>
                <div class="cell">
                  <span class="label">
                    <?php echo empty( $title ) ? '&nbsp;' : esc_html( $title ); ?>
                  </span>
                </div>
              <?php
			}
			?>
          </div>
          <?php
			foreach ( $this->rows as $row ) {
				?>
  
            <div class="item">
          <?php
				$column_index = -1;
				foreach ( $row as $column ) {
					++$column_index;
					$column_name = count( $this->columns ) > $column_index ? $this->columns[ $column_index ] : '';

					if ( is_array( $column ) ) {
						if ( array_key_exists( 'url', $column ) ) {
							if ( ! array_key_exists( 'confirm', $column ) ) {
								?>
                          <a class="cell link" data-column="<?php echo esc_attr( $column_name ); ?>" href="<?php echo esc_attr( $column['url'] ); ?>"><?php echo array_key_exists( 'label', $column ) && ! empty( $column['label'] ) ? esc_html( $column['label'] ) : esc_html( $column['url'] ); ?></a>
                      <?php
							} else {
								?>
                          <a class="cell link" data-column="<?php echo esc_attr( $column_name ); ?>" href="#" onclick='printessShowConfirm("<?php echo esc_attr( $column['confirm'] ); ?>", "<?php echo esc_attr( $column['url'] ); ?>");'><?php echo array_key_exists( 'label', $column ) && ! empty( $column['label'] ) ? esc_html( $column['label'] ) : esc_html( $column['url'] ); ?></a>
                      <?php
							}
						} elseif ( array_key_exists( 'thumbnail', $column ) ) {
							?>
                        <img class="cell thumbnail" data-column="<?php echo esc_attr( $column_name ); ?>" src="<?php echo esc_attr( $column['thumbnail'] ); ?>" alt="<?php echo array_key_exists( 'alt', $column ) && ! empty( $column['alt'] ) ? esc_attr( $column['alt'] ) : '#'; ?>" >
                      <?php
						} else {
							foreach ( $column as $content ) {
								?>
                        <span class="cell text_array" data-column="<?php echo esc_attr( $column_name ); ?>" ><?php echo esc_html( $content ); ?></span>
                      <?php
							}
						}
					} elseif ( is_callable( $column ) ) {
						echo $column(); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
					} else {
						?>
                      <span class="cell text" data-column="<?php echo esc_attr( $column_name ); ?>" ><?php echo null !== $column && ! empty( $column ) ? esc_html( $column ) : '&nbsp;'; ?></span>
                    <?php
					}
				}
				$column_index = -1;
				?>
              </div>
          <?php
			}
			?>
      </div>
  <?php
		}
		?>

<?php
	}

	/**
	 * Prints the table.
	 *
	 * @param string $mode The rendering mode.
	 */
	public function render( string $mode ) {
		ob_start();

		if ( null === $mode || empty( $mode ) || 'css_grid' === $mode ) {
			$this->render_css_grid();
		}

		$value = ob_get_contents();

		ob_end_clean();

		return $value;
	}
}


?>