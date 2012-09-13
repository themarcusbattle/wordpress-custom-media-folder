<?php
	
	include( $_POST['site_path'] ."wp-config.php" );
	
	// Establish separate connection to wordpress db
	$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME , DB_USER, DB_PASSWORD);  
	$db->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
	
	$sql		= "SELECT blog_id,domain,path FROM " . $table_prefix . "blogs";
	$stmt 	= $db->query($sql);
	$sites 	= $stmt->fetchAll();
		
	// Define variables
	$cmf_path = $_POST['site_path'] . $_POST['cmf_path'];
	$blog_cmf_path = $cmf_path . '/' . str_replace('/','',$_POST['blog_cmf_path']);
	$no_year_month_folders = true; // change to a $_POST['year_month_folders'] value later
	
	// Check to see if Custom Media Folder exists for the blog. If not, create it
	if(!file_exists($blog_cmf_path)) {
		mkdir($blog_cmf_path);
		chdir($blog_cmf_path);
	} else {
		chdir($blog_cmf_path);
	}
	
	// Set current blog
	if($_POST['blog_id'] != 1) {
		$cur_prefix = $table_prefix . $_POST['blog_id'] . "_";
	} else {
		$cur_prefix = $table_prefix;
	}
	
	// Update file paths in wordpress to move files
	$sql = "SELECT * FROM " . $cur_prefix . "posts WHERE post_type = 'attachment'";
	$stmt 	= $db->query($sql);
	$media 	= $stmt->fetchAll();
	
	foreach($media as $m) {
		if($m['post_mime_type'] == 'image/jpeg') { $ext = '.jpg'; }	
		
		$new_media = $m['post_name'] . $ext;
		$media_url = site_url() . '/' . $_POST['cmf_path'] . $_POST['blog_cmf_path'] . $new_media;
		
		//echo $new_media;
		$media_data = file_get_contents($m['guid']);
		file_put_contents($new_media,$media_data);
		
		$sql = "UPDATE " . $cur_prefix . "posts 
			SET 
				guid = :new_guid
			WHERE ID = :post_id";
		
		$stmt = $db->prepare($sql);
		$stmt->execute(array(
			'post_id' => $m['ID'],
			'new_guid' => site_url() . '/' . $_POST['cmf_path'] . $_POST['blog_cmf_path'] . $new_media
		));
	
	}
	
	// Get the current upload path
	$sql = "SELECT option_value FROM " . $cur_prefix . "options WHERE option_name = 'upload_path'";
	$stmt	= $db->query($sql);
	$blog_upload_path = $stmt->fetch();
	$old_upload_path = $blog_upload_path['option_value'];
	
	$new_upload_path = substr($_POST['cmf_path'] . $_POST['blog_cmf_path'],0,-1);
	print_r($new_upload_path);
	
	
	// Update blog with new path (will update upload_path & fileupload_url)
	$sql = "UPDATE " . $cur_prefix . "options SET option_value = replace(option_value,:old_upload_path,:new_upload_path)";
	$stmt = $db->prepare($sql);
	$stmt->execute(array('old_upload_path' => $old_upload_path,'new_upload_path' => $new_upload_path));
	
	// Get all featured images in the blog
	$sql = "
		SELECT meta.*, posts.post_mime_type, posts.post_name FROM " . $cur_prefix . "postmeta AS meta
		LEFT JOIN " . $cur_prefix . "posts AS posts ON posts.ID = meta.post_id
		WHERE meta.meta_key = '_wp_attached_file'
	";
	$stmt	= $db->query($sql);
	$featured_images = $stmt->fetchAll();
	 
	
	// Update blog's definition of 'uploads_use_yearmonth_folders'
	if($no_year_month_folders) { 
		$sql = "UPDATE " . $cur_prefix . "options SET option_value = 0 WHERE option_name = 'uploads_use_yearmonth_folders'";
		$stmt = $db->query($sql);
		
		// Update post meta and remove dates from thumbnail references
		foreach($featured_images as $fi) { 
			
			$sql = "UPDATE " . $cur_prefix . "postmeta SET meta_value = :media_file WHERE meta_id = :meta_id";
			$stmt = $db->prepare($sql);
			$stmt->execute(array('media_file' => add_ext($fi), 'meta_id' => $fi['meta_id']));
		}
		
	} else {
		$sql = "UPDATE " . $cur_prefix . "options SET option_value = 1 WHERE option_name = 'uploads_use_yearmonth_folders'";
		$stmt = $db->query($sql);
		
		// Update post meta and add dates to thumbnail references
		foreach($featured_images as $fi) { }		
	}
	
	// Return the media filename with the appropiate extension
	function add_ext($obj) { 
		if($obj['post_mime_type'] == 'image/jpeg') { $ext = '.jpg'; }	
		
		return $obj['post_name'] . $ext;		
	}
?>