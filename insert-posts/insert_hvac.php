<?php 
$images_folder = plugin_dir_path( __FILE__ ) . 'ccp_bottoms_new';
$files = array_values(array_diff(scandir($images_folder), array('.', '..')));
$files_to_import = [];
foreach ($files as $key => $filename) {
	$file_no_ext = str_replace('.jpg', '', $filename);
	$files_to_import[] = $file_no_ext;
}

$ccp_bottoms = [];
global $wpdb;
$query = "SELECT * FROM hvac_unit_info";
$results = $wpdb->get_results($query);
foreach ($results as $result) {
	set_time_limit(0);
	$img_key = null;

	$manufacturer = $result->manufacturer;
	$heat_type = $result->heat_type;
	$unit_model_no = $result->unit_model_no;
	$ccp_bottom = $result->ccp_bottom;
	$ccp_top = $result->ccp_top;
	$special_notes = $result->special_notes;
	$manufactures_curb = $result->manufactures_curb;
	$new_add = $result->new_add;
	$tonnage = $result->tonnage;
	$unit_net_weight = $result->unit_net_weight;
	$unit_height = $result->unit_height;
	$unit_length = $result->unit_length;
	$unit_width = $result->unit_width;

	if($ccp_bottom !== '') {
		$ccp_bottoms[] = $ccp_bottom;
	}
	
	foreach ($files_to_import as $key => $file) {
		if($file == $ccp_bottom) {
			$img_key = $key;
			// Used for finding unmatched imgs and units
			// unset($files_to_import[$key]); //Images
			// array_pop($ccp_bottoms); //HVAC Units
		}
	}

	if($img_key !== null) {
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

		$post = array(
			'post_type' => 'hvac-units',
			'post_title' => $unit_model_no,
			'post_category' => array( $manufacturer ),
			'post_status' => 'private',
		);

		$post_id = wp_insert_post( $post );
		$term = get_term_by( 'slug', strtolower($manufacturer), 'brand-names' );
		wp_set_post_terms( $post_id, $term->term_id, 'brand-names' );
		if($img_name !== null) {
			$attachment_id = wp_insert_attachment( $attachment, $file, $post_id );
			set_post_thumbnail( $post_id, $attachment_id );
		}

		update_field('heat_type', $heat_type, $post_id);
		update_field('ccp_bottom', $ccp_bottom, $post_id);
		update_field('ccp_top', $ccp_top, $post_id);
		update_field('special_notes', $special_notes, $post_id);
		update_field('manufactures_curb', $manufactures_curb, $post_id);
		update_field('new_add', $new_add, $post_id);
		update_field('tonnage', $tonnage, $post_id);
		update_field('unit_net_weight', $unit_net_weight, $post_id);
		update_field('unit_height', $unit_height, $post_id);
		update_field('unit_length', $unit_length, $post_id);
		update_field('unit_width', $unit_width, $post_id);
	}

}
// Used for outputting arrays of unmatched hvac units or ccp_bottom drawings
	// Images
	// foreach ($files_to_import as $file) {
	// 	echo $file . '<br>';
	// }
	// HVAC Units
	// $results = array_unique($ccp_bottoms);
	// foreach ($results as $result) {
	// 	echo $result . '<br>';
	// }
?>