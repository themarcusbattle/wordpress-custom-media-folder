<?php
	/*
	Plugin Name: Custom Media Folder & Move
	Plugin URI: http://marcusbattle.com/wordpress-custom-media-folder
	Description: Allows you to define a custom media and move existing images to new location
	Version: 1.0
	Author: Marcus Battle
	Author URI: http://marcusbattle.com
	License: Copyright 2012 DO NOT REDISTRIBUTE
	*/
	

	add_action( 'admin_menu', 'cmf_menu' );
	
	function cmf_menu() {
		add_options_page( 'Custom Media Folder', 'Custom Media Folder', 'manage_options', 'custom-media-folder', 'my_plugin_options' );
	}
	
	function my_plugin_options() {
		$plugin_dir_path = dirname(__FILE__);
		
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		
		include_once($plugin_dir_path . '/styles.css');
		
		if(is_multisite()) {
			
			include_once($plugin_dir_path . '/multisite-mover-admin.php');
		}
		
	}
	
	/*add_filter('upload_dir', 'ml_media_upload_dir');

	// Changes the upload directory to what we would like, instead of what WordPress likes.
	function ml_media_upload_dir($upload) {
		global $user_ID;
		echo "<pre>";
		print_r($upload);
		echo "</pre>";
	}*/
?>