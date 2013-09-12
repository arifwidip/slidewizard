<?php
/**
 * Template for rendering options for dribbble source
 *
 */
?>

<div id="options-source-posts">
  <ul class="options-list form-horizontal">
    
    <?php echo slidewizard_render_input_single( 'username', $this->source_options['Setup']['username'], $slidewizard['options'] ); ?>
    
  </ul>
</div><!-- #options-source-posts -->