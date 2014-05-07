<?php
/**
 * Template for managing Slides
 * 
 */
?>
<div class="wrap">
  <div class="slidewizard-container">
  	<div class="slidewizard-twitter-stream updated">
  		<div class="stream-label"><?php _e('News On Twitter:','colabsthemes');?></div>
  			<?php
  				$instance = array( 
  					'query'             => 'from:colorlabs',
  					'number'            =>  5,
  					'show_follow'       => 'false',
  					'show_avatar'       => 'false',
  					'show_account'      => 'false',
  					'consumer_key'      => 'tZC2RgSO04T7ctQQDIFw',
  					'consumer_secret'   => 'xB8YWcEYkzqnqGAgHia84YVWlGSZqRnZn0otis2Ho',
  					'list_before'       => '<li>',
  					'list_after'       	=> '</li>',
  				);		
  			?>
  			<ul class="tweets">
  				<?php echo slidewizard_get_tweets($instance); ?>
  			</ul>
  	</div>
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
      <a href="<?php echo slidewizard_action('&action=create');?>" class="button-primary" id="slidewizard-create-button">Create Slide</a>
      <div class="clear"></div>
    </div><!-- .slidewizard-topblock -->
    
    <div class="slidewizard-main">
      <?php include SLIDEWIZARD_PLUGIN_DIR . "/views/partials/_slides-list.php"; ?>
    </div><!-- .slidewizard-main -->
    
    <div class="slidewizard-footnote">
      <ul>
        <li class="docs"><a title="Theme Documentation" href="http://colorlabsproject.com/documentation/slidewizard" target="_blank">View Documentation</a></li>
        <li class="forum"><a href="http://colorlabsproject.com/resolve/" target="_blank">Submit a Support Ticket</a></li>
      </ul>
    </div>
  	<div class="slidewizard-footer">
  		<a class="cl-logo" href="http://colorlabsproject.com/" target="_blank">
        <img src="<?php echo SLIDEWIZARD_PLUGIN_URL . '/images/colorlabs.png' ;?>">
  		</a>
  	</div>
  </div><!-- .slidewizard-container -->
</div>
