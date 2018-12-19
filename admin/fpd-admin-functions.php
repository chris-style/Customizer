<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function fpd_admin_display_version_info( $display_shortcode=false ) {

	$shortcode_button = '';
	if($display_shortcode)
		$shortcode_button = '<a href="#" class="button-secondary" id="fpd-shortcode-builder">'.__('Shortcodes', 'radykal').'</a>';

	echo '<div class="fpd-header-right">'.$shortcode_button.'<a href="#" target="_blank" class="button-primary">'.__('Support Center', 'radykal').'</a></div>';

}

function fpd_admin_get_file_content( $file ) {

	$result = false;
	if( function_exists('curl_exec') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $file);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		$result = curl_exec($ch);
		curl_close($ch);
	}

	//if curl does not work, use file_get_contents
	if( $result == false && function_exists('file_get_contents') ) {
		$result = @file_get_contents($file);
	}

	if($result !== false) {
		return $result;
	}
	else {
		return false;
	}

}

function fpd_admin_upload_image_to_wp( $name, $base64_image, $add_to_library = true ) {

	//upload to wordpress
	$upload = wp_upload_bits( $name, null, base64_decode($base64_image) );

	//add to media library
	if( $add_to_library && isset($upload['url']) ) {
		media_sideload_image( $upload['url'], 0 );
	}

	return $upload['error'] === false ? $upload['url'] : false;

}

function fpd_admin_get_all_fancy_products() {

	global $wpdb;

	if( fpd_table_exists(FPD_PRODUCTS_TABLE) ) {

		$products = $wpdb->get_results("SELECT * FROM ".FPD_PRODUCTS_TABLE." ORDER BY ID ASC");
		$products_arr = array();

		foreach($products as $product) {
			$products_arr[$product->ID] = $product->title;
		}

		return $products_arr;

	}
	else
		return array();

}

function fpd_admin_get_all_fancy_product_categories() {

	global $wpdb;

	if( fpd_table_exists(FPD_CATEGORIES_TABLE) ) {

		$categories = $wpdb->get_results("SELECT * FROM ".FPD_CATEGORIES_TABLE." ORDER BY ID ASC");
		$categories_arr = array();

		foreach($categories as $category) {
			$categories_arr[$category->ID] = $category->title;
		}

		return $categories_arr;

	}
	else
		return array();

}

function fpd_admin_delete_directory( $dir ) {

	$iterator = new RecursiveDirectoryIterator($dir);
	foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $file) {

		if($file->getFilename() == '.' || $file->getFilename() == '..')
			continue;

		if ($file->isDir()) {
			@rmdir($file->getPathname());
	 	}
	 	else {
	    	@unlink($file->getPathname());
	 	}

   }

   @rmdir($dir);

}

function fpd_output_top_level_design_cat_options( $echoOutput=true, $idInValue=false ) {

	//get all created categories
	$categories = get_terms( 'fpd_design_category', array(
	 	'hide_empty' => false,
	 	'parent' => 0
	));

	$category_options = array();
	foreach($categories as $category) {

		$category_options[$category->name] = $category->name;
		$optionVal = $idInValue ? $category->term_id : $category->name;

		if($echoOutput)
			echo '<option value="'.$optionVal.'">'.$category->name.'</option>';

	}

	return $category_options;

}


function fpd_output_admin_notice( $type, $headline, $message, $condition=true, $name=null, $dismissable=false, $inline=false ) {

	$dismiss_option = null;
	if($name)
		$dismiss_option = get_option( 'fpd_notification_'.$name );

	$inline = $inline ? ' inline' : '';

	//if condition is true and dismiss is not available or not set, show notice
	if( $condition && empty($dismiss_option) ) {

		?>
		<div class="notice notice-<?php echo $type.$inline; ?> fpd-dismiss-notification">
			<?php if($dismissable) echo '<button class="notice-dismiss" value="' . $name . '"></button>'; ?>
			<?php if($headline) echo '<h4 style="margin-bottom: 5px;">' . $headline . ' </h4>'; ?>
	        <p><?php echo $message;?></p>
	    </div>
		<?php

	}

}

function fpd_admin_copy_file( $file_url, $destination_dir ) {

	if( empty( $file_url ) ) return false;

	$filename = basename( $file_url );

	if( function_exists('copy') ) {

		return copy( $file_url, $destination_dir . '/' . $filename ) ? $filename : false;

	}
	else {

		$content = file_get_contents( $file_url );
		$fp = fopen( $destination_dir . '/' . $filename, 'w' );
		$bytes = fwrite( $fp, $content );
		fclose( $fp );

		return $bytes !== false ? $filename : false;

	}

}

?>