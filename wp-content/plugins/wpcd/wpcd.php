<?php
/*
 * Plugin Name: WPCD
 * Version: 1.0
 * Plugin URI: http://www.hughlashbrooke.com/
 * Description: Custom features for WPCD site.
 * Author: Hugh Lashbrooke
 * Author URI: http://www.hughlashbrooke.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: wpcd
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Hugh Lashbrooke
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-wpcd.php' );

// Load plugin libraries
require_once( 'includes/lib/class-wpcd-admin-api.php' );
require_once( 'includes/lib/class-wpcd-post-type.php' );
require_once( 'includes/lib/class-wpcd-taxonomy.php' );

/**
 * Returns the main instance of WPCD to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object WPCD
 */
function WPCD () {
	$instance = WPCD::instance( __FILE__, '1.0.0' );
	return $instance;
}

WPCD();
