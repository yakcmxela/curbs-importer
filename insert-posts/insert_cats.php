<?php 

global $wpdb;
$query = "SELECT DISTINCT manufacturer FROM hvac_unit_info";
$results = $wpdb->get_results($query);
foreach ($results as $result) {
	set_time_limit(0);
	$args = array(
		'cat_name' => $result->manufacturer,
		'taxonomy' => 'brand-names',
	);
	if(!term_exists($result->manufacturer, 'brand-names')) {
		wp_insert_category( $args );
	}
}

?>