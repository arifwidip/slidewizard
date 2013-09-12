<?php
/**
 * Template for rendering options for instagram source
 *
 */
?>

<div id="options-source-posts">
  <ul class="options-list form-horizontal">
    
    <?php echo slidewizard_render_input_single( 'instagram_access_token', $this->source_options['Setup']['instagram_access_token'], $slidewizard['options'], true ); ?>
    <?php echo slidewizard_render_input_single( 'instagram_user_name', $this->source_options['Setup']['instagram_user_name'], $slidewizard['options'] ); ?>
    
  </ul>
</div><!-- #options-source-posts -->