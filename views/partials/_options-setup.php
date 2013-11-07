<div id="setup" class="tab-pane fade">
  <ul class="options-list form-horizontal">
    
    <?php if( isset( $options['Setup']['size'] ) ) : ?>
      <li class="control-group control-size">
        <label for="options-size" class="control-label">
          <span class="label-text">Size</span><span class="tooltip-help tooltips badge" title="<?php echo $options['Setup']['size']['description'];?>">?</span>
        </label>
        
        <div class="control-option control-option-radio">
          <div class="input-radio btn-group" data-toggle="buttons-radio">
            <?php 
              $stored_value = ( $slidewizard['options']['size'] ) ? $slidewizard['options']['size'] : $options['Setup']['size']['default'];
              foreach( $options['Setup']['size']['value'] as $k => $v ) {
                $is_current = ( $v == $stored_value ) ? ' active ' : '';
                echo '<button type="button" data-trigger="custom" data-target=".control-width,.control-height" data-value="'. $v .'" class="btn button-'. $v . $is_current .'">';
                echo $k;
                if( $v != 'custom' ) {
                  echo '<span class="size-label">'. $sizes[$v]['width'] .'&times;'. $sizes[$v]['height'] .'</span>';
                }
                echo '</button>';
              }
            ?>
          </div>
          
          <?php foreach( $options['Setup']['size']['value'] as $k => $v ) { 
            $is_current = ( $v == $stored_value ) ? 'checked="checked"' : '';
            echo '<label class="radio inline">'. $v .'<input type="radio" name="options[size]" value="'. $v .'" '. $is_current .'></label>';
          } ?>
        </div>
      </li><!-- .control-size -->
    <?php endif; ?>
  
    <?php 
      unset( $options['Setup']['size'] );
      echo slidewizard_render_input_html( $options['Setup'], $slidewizard['options'] );
    ?>
  </ul>
</div>