<?php
/**
 * Template for previewing SlideWizard within an iframe
 *
 */
?>

<!doctype html>
<html>
  
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo $slidewizard['title'];?></title>
    
    <?php
    // Print scripts in head
    foreach( $scripts as $script ) {
      if( $script == 'jquery' ) $script = 'jquery-core';
      $src = $wp_scripts->registered[$script]->src;
      if ( !preg_match( '|^https?://|', $src ) && !( $content_url && 0 === strpos( $src, $content_url ) ) ) {
        $src = $base_url . $src;
      }
      echo '<script type="text/javascript" src="' . $src . ( strpos( $src, "?" ) !== false ? "&" : "?" ) . "v=" . $wp_scripts->registered[$script]->ver . '"></script>' . "\n";
    }
    ?>
    
    <link rel="stylesheet" type="text/css" href="<?php echo $wp_styles->registered['slidewizard-base']->src . ( strpos( $wp_styles->registered['slidewizard-base']->src, "?" ) !== false ? "&" : "?" ) . "v=" . $wp_styles->registered['slidewizard-base']->ver; ?>" />
    
    <link rel="stylesheet" type="text/css" href="<?php echo $themes['url'];?>?v=<?php echo isset( $themes['meta']['version'] ) && !empty( $themes['meta']['version'] ) ? $themes['meta']['version'] : SLIDEWIZARD_VERSION; ?>">
    
    <?php do_action( "{$namespace}_iframe_header", $slidewizard ); ?>
    
    <style>
      body {
        margin: 0;
        padding: 0;
        overflow: hidden;
      }
      .slidewizard-wrapper {
        margin: 0 auto;
      }
    </style>  
  </head>
  
  <body>
    
    <div class="overlay-loader"></div>
    
    <?php
      $shortcode = "[slidewizard id={$slidewizard['id']}]";
      echo do_shortcode( $shortcode );
    ?>
    
    <?php $this->print_footer_scripts(); ?>
  </body>
  
</html>


