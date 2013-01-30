<?php
/**
 * Template for creating and editing slides
 * 
 */
?>

<div class="slidewizard-container slidewizard-form">
  <p><a class="btn btn-small" href="<?php echo slidewizard_action();?>">&larr; <?php _e('Back to Dashboard', $this->namespace);?></a></p>
  
  <form method="post" id="slidewizard-slide-form" class="slidewizard-form">
    
    <div class="slidewizard-form-title">
      <input type="text" name="title" class="input-xxlarge" placeholder="My Slides" value="<?php echo $slidewizard['title']; ?>">
    </div>
  
    <div class="slidewizard-form-header">
      <input type="hidden" name="action" value="<?php echo $form_action; ?>" id="form_action">
      <input type="hidden" name="id" value="<?php echo $slidewizard['id']; ?>">
      <?php wp_nonce_field( "{$namespace}-{$form_action}-slidewizard" ); ?>
      
      <div id="slidewizard-source-control" class="clearfix">
        <div class="slidewizard-source-wrapper clearfix">
          <?php do_action( "{$namespace}_source_control", $slidewizard, $namespace ); ?>
        </div>
      </div>
      
    </div><!-- .slidewizard-form-header -->
    
    <div class="slidewizard-form-body">
      <div class="accordion" id="slidewizard-options-accordion">
        
        <div class="accordion-group">
          <div class="accordion-heading">
            <a class="accordion-toggle" data-toggle="collapse" href="#slidewizard-preview">Preview</a>
          </div>
          <div id="slidewizard-preview" class="accordion-body collapse in">
            <div class="accordion-inner">
              <iframe id="slidewizard-preview-iframe" src="<?php echo admin_url('admin-ajax.php');?>?action=slidewizard_preview-iframe&amp;id=<?php echo $slidewizard['id'];?>" width="<?php echo $slidewizard_dimensions['outer_width'];?>" height="<?php echo $slidewizard_dimensions['outer_height'];?>"></iframe>
            </div>
          </div>
        </div>
        
        <div class="accordion-group">
          <div class="accordion-heading">
            <a class="accordion-toggle" data-toggle="collapse" href="#slidewizard-options">Options</a>
          </div>
          <div id="slidewizard-options" class="accordion-body collapse in">
            <div class="accordion-inner">
              <?php include( SLIDEWIZARD_PLUGIN_DIR . "/views/partials/_options.php" ); ?>
            </div>
          </div>
        </div>
        
        <div class="save-buttons wells">
          <input type="submit" class="btn btn-primary" value="<?php _e('Save Slide', 'slidewizard');?>">
        </div>
        
      </div>
    </div><!-- .slidewizard-form-body -->
  
  </form><!-- #slidewizard-slide-form -->
  
</div><!-- .slidewizard-form -->