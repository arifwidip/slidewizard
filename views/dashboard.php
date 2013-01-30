<?php
/**
 * Template for managing Slides
 * 
 */
?>
<div class="slidewizard-container">
  <div class="slidewizard-topblock clearfix">
    <h1>
      SlideWizard
      <sup class="slidewizard-version"><?php echo self::$version; ?></sup>
    </h1>
    
    <a class="cl-logo" href="http://colorlabsproject.com/" target="_blank">
      <img src="<?php echo SLIDEWIZARD_PLUGIN_URL . '/images/logo-cl.png' ;?>">
    </a>
    <div class="clear"></div>
  </div><!-- .slidewizard-topblock -->
  
  <div class="navbar">
    <div class="navbar-inner">
      <a href="<?php echo slidewizard_action('&action=create');?>" class="btn btn-primary" id="slidewizard-create-button">Create Slide</a>
    </div>
  </div>
  
  <div class="slidewizard-main">
    <?php include SLIDEWIZARD_PLUGIN_DIR . "/views/partials/_slides-list.php"; ?>
  </div><!-- .slidewizard-main -->
</div><!-- .slidewizard-container -->