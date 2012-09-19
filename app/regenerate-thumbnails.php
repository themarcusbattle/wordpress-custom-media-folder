<?php

	include( $_REQUEST['site_path'] ."wp-config.php" );
	require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	
	// Establish separate connection to wordpress db
	$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME , DB_USER, DB_PASSWORD);  
	$db->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
	
	$sql		= "SELECT * FROM " . $table_prefix . "posts WHERE post_type ='attachment'";
	$stmt 	= $db->query($sql);
	$images 	= $stmt->fetchAll();
	
	$upload_dir = wp_upload_dir();
	
	//print_r($images);
	
	foreach($images as $img) {
		set_time_limit(0);
		$file_name = add_ext($img);
		
		$attach_data = wp_generate_attachment_metadata( $img['ID'], $upload_dir['path'] . '/' . $file_name); // makes thumbnails	
		wp_update_attachment_metadata( $img['ID'],  $attach_data );
		update_post_meta($img['post_parent'],'_thumbnail_id',$img['ID']);
		
	}
	
	echo json_encode(array('success' => true, 'msg' => 'All of your thumbnails have been successfully regenerated'));

	// Return the media filename with the appropiate extension
	function add_ext($obj) { 
		if($obj['post_mime_type'] == 'image/jpeg') { $ext = '.jpg'; }	
		if($obj['post_mime_type'] == 'image/png') { $ext = '.png'; }	
		
		return $obj['post_name'] . $ext;		
	}
?>