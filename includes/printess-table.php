<?php

if ( class_exists( 'PrintessTable', false ) ) return;

class PrintessTable
{
  private $columns = array();
  private $rows = array();
  private $settings = array();

  function __construct($settings = null) {
    if(isset($settings) && is_array($settings)) {
      $this->settings = $settings;
    }
}

  function add_column(mixed $title) {
    if(null === $title || empty($title))
    {
      $this->columns[] = "";
    }
    else if(is_array($title))
    {
      foreach($title as $label)
      {
        $this->columns[] = null === $label || empty($label) ? "" : $label;
      }
    }
    else
    {
      $this->columns[] = null !== $title && !empty($title) ? $title : "";
    }
  }

  function add_row(?array $values = null) {
    $new_row = array();

    if(null !== $values && is_array($values)) {
      $new_row = array_merge($values);
    }

    $this->rows[] = $new_row;
  }

  function add_value($value) {
    if(0 === count($this->rows)) {
      $this->rows[] = array();
    }

    $this->rows[count($this->rows) - 1] = array_merge($this->rows[count($this->rows) - 1], null !== $value && is_array($value) ? $value : array($value));
  }

  private function render_css_grid()
  {
    if( 0 < count($this->rows))
    {
      $additional_classes = array_key_exists("tableClasses", $this->settings) ? $this->settings["tableClasses"] : "";
    ?>
      <div class="printess_table columns_<?php echo esc_attr( count ( $this->columns ) ) ?> <?php echo esc_attr( $additional_classes ) ?>">
          <div class="header">
          <?php
            foreach($this->columns as $title)
            {
              ?>
                <div class="cell">
                  <span class="label">
                    <?php echo empty($title) ? "&nbsp;" : esc_html( $title ) ?>
                  </span>
                </div>
              <?php
            }
          ?>
          </div>
          <?php
            foreach($this->rows as $row)
            {
          ?>  
            <div class="item">
          <?php
                $column_index = -1;
                foreach($row as $column)
                {
                  $column_index += 1;
                  $column_name = count($this->columns) > $column_index ? $this->columns[$column_index] : "";

                  if( is_array( $column ) )
                  {
                    if( array_key_exists("url", $column) )
                    {
                      ?>
                        <a class="cell link" data-column="<?php echo esc_attr( $column_name ); ?>" href="<?php echo esc_attr( $column[ "url" ] ); ?>"><?php echo array_key_exists( "label", $column) && !empty( $column["label"] ) ?  esc_html( $column["label"] ) :  esc_html( $column["url"] )  ?></a>
                      <?php
                    }
                    else if( array_key_exists("thumbnail", $column) )
                    {
                      ?>
                        <img class="cell thumbnail" data-column="<?php echo esc_attr( $column_name ); ?>" src="<?php echo esc_attr( $column[ "thumbnail" ] ); ?>" alt="<?php echo array_key_exists("alt", $column) && !empty( $column[ "alt" ] ) ? esc_attr( $column[ "alt" ] ) : "#"; ?>" >
                      <?php
                    }
                    else
                    {
                        foreach($column as $content)
                        {
                      ?>
                        <span class="cell text_array" data-column="<?php echo esc_attr( $column_name ); ?>" ><?php echo esc_html( $content ) ?></span>
                      <?php
                        }
                    }
                  }
                  else if( is_callable( $column ) )
                  {
                    echo $column();
                  }
                  else
                  {
                    ?>
                      <span class="cell text" data-column="<?php echo esc_attr( $column_name ); ?>" ><?php echo $column !== null && !empty($column) ? esc_html( $column ) : "&nbsp;"; ?></span>
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

  function render(string $mode) {
    ob_start();

    if(null === $mode || empty($mode) || "css_grid" === $mode) {
      $this->render_css_grid();
    }

    $value = ob_get_contents();

    ob_end_clean();

    return $value;
  }
}


?>