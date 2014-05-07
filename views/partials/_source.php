<?php
/**
 * Template for showing source options
 *
 */
?>

<?php foreach( $sources as $source ) : ?>

  <div class="slidewizard-source">
    <div class="source-icon">
      <img src="<?php echo slidewizard_get_source_icon_url( $source ); ?>" alt="<?php echo $source->label; ?>">
      <a class="configure-source" href="#configure"><i class="arrow"></i>Configure</a>
      
      <div class="slidewizard-source-config popover bottom">
        <div class="arrow"></div>
        <h3 class="popover-title"><?php printf( __( "Configure your %s source", $namespace ), preg_replace( '/^your\s/i', '', $source->label ) ); ?></h3>
        <div class="popover-content">
          <?php do_action( "{$namespace}_form_content_source", $slidewizard, $source->name ); ?>
          <input type="hidden" name="source[]" value="<?php echo $source->name; ?>">
        </div>
        
        <div class="popover-footer clearfix">
          <a href="#apply" class="slidewizard-ajax-update button-primary apply"><?php _e( "Apply", $namespace ); ?></a>
        </div>
      </div>
      
    </div>
  </div>
  
<?php endforeach; ?>