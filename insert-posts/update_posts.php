<?php 

$args = array(
	'post_type' => 'hvac-units',
	'posts_per_page' =>  -1,
);
$query = new WP_Query( $args );
foreach ($query->posts as $post) {
	$updates = array(
		'ID' => $post->ID,
		'post_status' => 'private',
	);
	wp_update_post($updates);
}

?>