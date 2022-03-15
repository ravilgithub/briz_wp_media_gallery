<?php
/**
 * Plugin Name: BRIZ Media gallery
 * Plugin URI:  http://www.yandex.ru
 * Description: BRIZ Media gallery
 * Version:     0.0.1
 * Author:      Ravil
 * Author URI:  http://www.tstudio.zzz.com.ua
 */

namespace Briz_Media_gallery;

define( __NAMESPACE__ . '\PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( __NAMESPACE__ . '\PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require_once( PLUGIN_PATH . '/media_gallery.php' );

function briz_media_gallery_init() {
	$media_props = [
		// 'title'    => 'Insert a media',
		'library'  => [
			'type' => [
				'image',
				'audio',
				'video'
			]
		],
		// 'library' => [ 'type' => 'image' ],
		// 'multiple' => true,
		// 'button'   => [ 'text' => 'Insert' ]
	];

	new Media_gallery( $media_props );
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\briz_media_gallery_init', 99 );
