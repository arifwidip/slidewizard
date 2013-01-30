<?php
/**
 * Constants used by this plugin
 * 
 * @author colorlabs
 */
 
// Current Version of this plugin
if( !defined( 'SLIDEWIZARD_VERSION' ) ) define( 'SLIDEWIZARD_VERSION', self::$version );

// Define plugin directory and plugin URL
if( !defined( 'SLIDEWIZARD_PLUGIN_DIR' ) ) define('SLIDEWIZARD_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . SLIDEWIZARD_PLUGIN_NAME);
if( !defined( 'SLIDEWIZARD_PLUGIN_URL' ) ) define('SLIDEWIZARD_PLUGIN_URL', WP_PLUGIN_URL . '/' . SLIDEWIZARD_PLUGIN_NAME);

define( 'SLIDEWIZARD_POST_TYPE', 'slidewizard' );
define( 'SLIDEWIZARD_SLIDE_POST_TYPE', 'slidewizard_slide' );

define( 'SLIDEWIZARD_NEW_TITLE', 'My Slides' );
define( 'SLIDEWIZARD_DEFAULT_THEMES', 'default' );

// Environment - change to "development" to load .dev.js JavaScript files 
// (DON'T FORGET TO TURN IT BACK BEFORE USING IN PRODUCTION)
if( !defined( 'SLIDEWIZARD_ENV' ) ) define( 'SLIDEWIZARD_ENV', self::$env );