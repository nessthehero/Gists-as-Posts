<?php

	$url = "https://api.github.com/users/nessthehero/gists";

	//  Initiate curl
	$ch = curl_init();
	// Disable SSL verification
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// Will return the response, if false it print the response
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// Set the url
	curl_setopt($ch, CURLOPT_URL, $url);
	// Set a user-agent because Github is mean
	curl_setopt( $ch, CURLOPT_USERAGENT, 'Gist-as-Post' );
	// Execute
	$result = curl_exec($ch);
	// Closing
	curl_close($ch);

	$gists = json_decode($result, true);

	foreach ($gists as $key => $value) {

		$args = array(
			'numberposts' => -1,
			'post_type' => 'gist',
			'meta_key' => 'gist_id',
			'meta_value' => $value['id']
		);
		$the_query = get_posts( $args );

		if ( 0 === count($the_query) ) {

			$post = wp_insert_post(array(
				'post_title' => $value['description'],
				'post_name' => sanitize_title($value['description']),
				'post_status' => 'draft',
				'post_type' => 'gist',
				'post_author' => 1 // TODO: Add setting for this
			));

			if ($post !== false) {

				$meta = add_post_meta($post, 'gist_id', $value['id']);

			}

		}

	}
