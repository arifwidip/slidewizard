<?php
/**
 * Template for managing Slides
 * 
 */
?>
<div class="slidewizard-container">
  <div class="slidewizard-topblock clearfix">
	<div class="logo-wrapper">
		<h1>
			<a href="http://colorlabsproject.com/plugins/slidewizard/" target="_blank" title="SlideWizard">
			<img src="<?php echo plugins_url();?>/slidewizard/images/logo.png">
		  SlideWizard 
		  </a>
		</h1>
		<sup class="slidewizard-version"><?php echo self::$version; ?></sup>
    </div>
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
  
  <div class="slidewizard-footnote">
    <ul>
      <li class="docs"><a title="Theme Documentation" href="http://colorlabsproject.com/documentation/slidewizard" target="_blank">View Documentation</a></li>
      <li class="forum"><a href="http://colorlabsproject.com/resolve/" target="_blank">Submit a Support Ticket</a></li>
    </ul>
  </div>

</div><!-- .slidewizard-container -->