<?php
/**
 * Template for rendering options for rss feed source
 *
 */
?>

<div id="options-source-posts">
  <ul class="options-list form-horizontal">
    
    <?php echo slidewizard_render_input_single( 'rss_feed_url', $this->source_options['Setup']['rss_feed_url'], $slidewizard['options'], true ); ?>
    
  </ul>
</div><!-- #options-source-posts -->