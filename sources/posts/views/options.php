<?php
/**
 * Template for rendering options for posts source
 *
 */
?>

<div id="options-source-posts">
  <ul class="options-list form-horizontal">
    
    <?php echo slidewizard_render_input_single( 'image_source', $this->source_options['Setup']['image_source'], $slidewizard['options'] ); ?>
    <?php echo slidewizard_render_input_single( 'post_type', $this->source_options['Setup']['post_type'], $slidewizard['options'] ); ?>
    <?php echo slidewizard_render_input_single( 'sorting_method', $this->source_options['Setup']['sorting_method'], $slidewizard['options'] ); ?>
    <?php echo slidewizard_render_input_single( 'use_custom_excerpt', $this->source_options['Setup']['use_custom_excerpt'], $slidewizard['options'] ); ?>
    
  </ul>
</div><!-- #options-source-posts -->