<?php
/**
 * Template for Source Modalbox
 *
 */
?>

<div class="modal hide fade slidewizard-modal" id="slidewizard-source-modal">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3><?php echo __($title, $namespace); ?></h3>
  </div>
  
  <div class="modal-body">
    <ul class="sources-list clearfix">
      <?php 
      $count = 1;
      foreach( $sources as &$source ) : 
      $classname = ( $count % 2 == 0 ) ? 'even' : 'odd'; ?>
      
        <li class="source-item <?php echo $classname; ?>">
          <span class="thumbnail">
            <img src="<?php echo slidewizard_get_source_icon_url( $source ); ?>">
          </span>
          <a href="<?php echo slidewizard_action('&action=create&source='. $source->name );?>"><?php echo $source->label; ?></a>
        </li>
        
      <?php $count++; endforeach; ?>
      
    </ul><!-- .sources-list -->
  </div><!-- .modal-body -->
</div><!-- #slidewizard-source-modal --> 