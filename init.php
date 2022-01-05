<?php
/**
 * Plugin Name: BRIZ Images gallery
 * Plugin URI:  http://www.yandex.ru
 * Description: BRIZ Images gallery
 * Version:     0.0.1
 * Author:      Ravil
 * Author URI:  http://www.tstudio.zzz.com.ua
 */

/*
 * Text Domain: briz_shortcodes_l10n
 * Domain Path: /lang
 */

namespace Briz_Images_gallery;

define( __NAMESPACE__ . '\PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( __NAMESPACE__ . '\PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require_once( PLUGIN_PATH . '/images_gallery.php' );

function briz_images_gallery_init() {
  new Images_gallery();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\briz_images_gallery_init', 99 );
