<?php
/**
 * Template for rendering options for flickr source
 *
 */
?>

<div id="options-source-posts">
  <ul class="options-list form-horizontal">
    
    <?php echo slidewizard_render_input_single( 'flickr_user_or_group', $this->source_options['Setup']['flickr_user_or_group'], $slidewizard['options'] ); ?>
    <?php echo slidewizard_render_input_single( 'flickr_user_id', $this->source_options['Setup']['flickr_user_id'], $slidewizard['options'], true ); ?>
    
  </ul>
</div><!-- #options-source-posts -->