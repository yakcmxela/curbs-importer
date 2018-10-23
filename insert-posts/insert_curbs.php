<?php
$images_folder = plugin_dir_path( __FILE__ ) . 'product_drawings_web';
$files = array_values(array_diff(scandir($images_folder), array('.', '..')));
$split_files = [];
foreach ($files as $key => $filename) {
	$file_no_ext = str_replace('.jpg', '', $filename);
	$file_strip_commas = str_replace(',', '', $file_no_ext);
	$file_replace_spaces = str_replace(' ', '-', $file_strip_commas);
	$file = explode('-to-', $file_replace_spaces);
	$split_files[] = $file;
}

global $wpdb;
$query = "SELECT * FROM ccp_adapters";
$results = $wpdb->get_results($query);
$curbs = [];
foreach ($results as $result) {
	set_time_limit(0);
	$ccp_top = $result->ccp_top;
	$ccp_bottom = $result->ccp_bottom;
	$extension_name = $result->extension_name;
	$distrib_pricing = $result->distrib_pricing;
	$lennox_pricing = $result->lennox_pricing;
	$contractor_pricing = $result->contractor_pricing;
	$page_number = $result->page_number;
	$weight = $result->weight;
	$update = $result->update;
	$post_title = $ccp_top . ' to ' . $ccp_bottom;
	if($extension_name !== '') {
		$bottom_to_match = $ccp_bottom . '-' . str_replace(' ', '-', $extension_name);
	} else {
		$bottom_to_match = $ccp_bottom;
	}
	
	$post = array(
		'post_type' => 'curbs',
		'post_title' => $post_title,
		'post_status' => 'private',
	);

	$curbs[] = $post_title;
	$img_key = null;
	foreach($split_files as $key => $split_file) {
		$split_file_lower_1 = strtolower($split_file[0]);
		$ccp_top_lower = strtolower($ccp_top);
		$split_file_lower_2 = strtolower($split_file[1]);
		$ccp_bottom_lower = strtolower($bottom_to_match);
		if($split_file_lower_1 == $ccp_top_lower && $split_file_lower_2 == $ccp_bottom_lower) {
			$img_key = $key;
			// unset($files[$key]);
			// array_pop($curbs);
		}
	}

	$img_url = $images_folder . '/' . $files[$img_key];
	$img_name = $files[$img_key];
	$upload_dir = wp_upload_dir();
	$img_data = file_get_contents($img_url);
	$filename = basename( $img_name );

	if( wp_mkdir_p( $upload_dir['path'] ) ) {
		$file = $upload_dir['path'] . '/' . $filename;
	} else {
		$file = $upload_dir['basedir'] . '/' . $filename;
	}
	
	file_put_contents( $file, $img_data );

	$wp_filetype = wp_check_filetype( $filename, null );

	$attachment = array(
		'post_mime_type' => $wp_filetype['type'],
		'post_title' => sanitize_file_name( $filename ),
		'post_content' => '',
		'post_status' => 'inherit',
	);

	$post_id = wp_insert_post( $post );
	if($img_name !== null) {
		$attachment_id = wp_insert_attachment( $attachment, $file, $post_id );
		set_post_thumbnail( $post_id, $attachment_id );
	}
	update_field('ccp_top', $ccp_top, $post_id);
	update_field('ccp_bottom', $ccp_bottom, $post_id);
	update_field('extension_name', $extension_name, $post_id);
	update_field('distrib_pricing', $distrib_pricing, $post_id);
	update_field('lennox_pricing', $lennox_pricing, $post_id);
	update_field('contractor_pricing', $contractor_pricing, $post_id);
	update_field('page_number', $page_number, $post_id);
	update_field('weight', $weight, $post_id);
	update_field('update', $update, $post_id);
}
// foreach ($files as $file) {
// 	echo $file . '<br>';
// }
// foreach ($curbs as $curb) {
// 	echo $curb . '<br>';
// }
?>