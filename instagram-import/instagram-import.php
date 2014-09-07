<?php
/*
 *	Plugin Name: Instagram Import
 *	Plugin URI: http://mlitzinger.com
 *	Description: Imports Instagram photos as posts to your WordPress site
 *	Version: 1.0
 *	Author: Matt Litzinger
 *	Author URI: http://mlitzinger.com
 *	License: GPL2
 *
*/

function custom_posts_from_instagram() {

	$client_id = '7bb2aa2b9c3b4e679a6e9119034f55d1';
	$user_id = '467550789';


	// Get photos from Instagram

	$url = 'https://api.instagram.com/v1/users/'.$user_id.'/media/recent/?client_id='.$client_id;

	$args = stream_context_create(array('http'=>
	    array(
	        'timeout' => 2500,
	    )
	));

	$json_feed = file_get_contents( $url, false, $args );

	$json_feed = json_decode( $json_feed );


	// Import each photo as post

	foreach ($json_feed->data as $post):

		if( !slug_exists($post->id) ) :

			$new_post = wp_insert_post( array(
				'post_content'  => '<a href="'. esc_url( $post->link ) .'" target="_blank"><img src="'. esc_url( $post->images->standard_resolution->url ) .'" alt="'. $post->caption->text .'" /></a>',
				'post_date'     => date("Y-m-d H:i:s", $post->created_time),
				'post_date_gmt' => date("Y-m-d H:i:s", $post->created_time),
				'post_status'   => 'publish',
				'post_title'    => $post->id,
				'post_name'     => $post->id,
				'post_category' => array(2)
			), true );

		endif;

	endforeach;

	function slug_exists($post_name) {
	    global $wpdb;
	    if($wpdb->get_row("SELECT post_name FROM wp_posts WHERE post_name = '" . $post_name . "'", 'ARRAY_A')) :
	        return true;
	    else :
	        return false;
	    endif;
	}

}

add_action( 'instagram_auto_fetch', 'custom_posts_from_instagram' );

if ( ! wp_next_scheduled( 'instagram_auto_fetch' ) ) {
	wp_schedule_event( time(), 'daily', 'instagram_auto_fetch');
}


