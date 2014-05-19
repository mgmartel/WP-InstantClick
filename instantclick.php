<?php
/*
Plugin Name: InstantClick
Plugin URI: http://cacheglove.com/2014/05/speed-up-your-wordpress-site-with-instantclick/
Description: Dramatically speed up your WordPress site with InstantClick
Version: 1.0.1
Author: Mike Martel
Author URI: http://trenvo.com
*/

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

// Maybe InstantClick is already loaded by another plugin
if ( !class_exists( 'WP_InstantClick' ) )
    require plugin_dir_path( __FILE__ ) . 'class-wp-instantclick.php';

// But the other plugin may not have loaded the options class
if ( !class_exists( 'WP_InstantClick_Options' ) )
    require plugin_dir_path( __FILE__ ) . 'class-wp-instantclick-options.php';

WP_InstantClick::enable();
WP_InstantClick_Options::enable();