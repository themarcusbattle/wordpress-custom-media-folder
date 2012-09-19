<?php
	// Establish separate connection to wordpress db
	$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME , DB_USER, DB_PASSWORD);  
	$db->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
	
?>

<div class="plugin">
	<h1>Welcome to the Custom Media Folder Plugin</h1>
	<h3>Written By: <a href="http://marcusbattle.com" target="_blank">Marcus Battle</a></h3>
	<p>This plugin will change the default folder for your uploaded images and move all of your existing images to the new folder.</p>
	<hr />
	<ul>
		<li>Multiste Enabled: <strong>true</strong></li>
		<?php if($db): ?>
		<li>Database Connection Established: <strong>true</strong></li>
		<?php else: ?>
		<li>Database Connection Established: <strong>false</strong></li>
		<?php endif; ?>
	</ul>
	<p>&nbsp;</p>
	<form action="">
		<ul>
			<li>
				<label>New Media Folder:</label>
				/<input type="text" name="cmf_path" />
			</li>
			<li>
				<input type="hidden" name="site_path" value="<?php echo ABSPATH; ?>" />
				<input type="submit" value="Change Media Folder" />
			</li>
		</ul>
		<p>(Yes it's that simple!)</p>
		<ul id="move-results">
		
		</ul>
	</form>
	<p>&nbsp;</p>
	<h3>Additional Options</h3>
	<ul>
		<li><a href="#">Restore Upload Folder To Default Settings</a></li>
		<li><a id="regenerate" href="<?php echo $plugin_dir_path ?>">Regenerate Thumbnails</a></li>
	</ul>
</div>

<script type="text/javascript">
	jQuery(document).ready(function($){
		var sites_refresh = Math.floor(Math.random()*10101);
		var site_path = '<?php echo ABSPATH; ?>';
		
		$('#regenerate').click(function(e){
			e.preventDefault();
			
			$.ajax({
				url: "<?php echo site_url() . '/wp-content/plugins/custom-media-folder/app/regenerate-thumbnails.php?refresh='; ?>" + sites_refresh,
				type: "GET",
				data: {'site_path':site_path},
				dataType: 'JSON'
			}).done(function(json) { 
				if(json.success) {
					alert(json.msg);
				}
			});
			
		});
		
		$('form').submit(function(e){
			e.preventDefault();
			$form = $(this);
			var sites_refresh = Math.floor(Math.random()*10101);
			//$form.find('input[type="submit"]').attr('disabled',true);
			
			$.ajax({
				url: "<?php echo site_url() . '/wp-content/plugins/custom-media-folder/app/get-sites.php?refresh='; ?>" + sites_refresh,
				type: "POST",
				data: $form.serialize()
			}).done(function(json){
				$.each(json.sites, function(i,site){
					
					var move_refresh = Math.floor(Math.random()*10101);
					$.ajax({
						url: "<?php echo site_url() . '/wp-content/plugins/custom-media-folder/app/multisite-move.php?refresh='; ?>" + move_refresh,
						type: "POST",
						data: { 
							'blog_id'				:site.blog_id,
							'blog_path'			:site.blog_path,
							'blog_cmf_path'	:site.blog_cmf_path, 
							'site_path' :$form.find('input[name="site_path"]').val(),
							'cmf_path'	:$form.find('input[name="cmf_path"]').val() }
					}).done(function(html){
						
						$('#move-results').append('<li>Images moved to ' + html + ' &#x2713;</li>');	
					});
					
				});
				//$form.find('input[type="submit"]').attr('disabled',false);
			});
			
		});
	
	});
</script>