<?php
	
	include( $_POST['site_path'] ."wp-config.php" );
	
	// Establish separate connection to wordpress db
	$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME , DB_USER, DB_PASSWORD);  
	$db->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
	
	$sql		= "SELECT blog_id,path AS blog_path FROM " . $table_prefix . "blogs";
	$stmt 	= $db->query($sql);
	$sites 	= $stmt->fetchAll();
	
	// remove root path from site path
	if($sites) {
		$root_path = $sites[0]['blog_path'];
		
		foreach($sites as $key => $site) {
			if($key == 0) {
				$sites[0]['blog_cmf_path'] = '/home/';
			} else {
				$sites[$key]['blog_cmf_path'] = str_replace($root_path,'/',$site['blog_path']);
			}
		}
	}
	
	// Create the Custom Media Folder if it doesn't exist
	$cmf_path = $_POST['site_path'] . $_POST['cmf_path'];
	if(!file_exists($cmf_path)) {
		mkdir($cmf_path);
	}
	
	header('Content-Type: application/json');
	echo json_encode(array('sites' => $sites));
?>