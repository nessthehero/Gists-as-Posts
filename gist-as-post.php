<?php
/**
 * Plugin Name: Gist as Post
 * Plugin URI: http://www.ianmoffitt.co/gist-as-post/
 * Description: Adds "Gist" post type, and imports all gists from your github as drafts (or published).
 * Version: 0.5.0
 * Author: Ian Moffitt
 * Author URI: http://www.ianmoffitt.co/
 * License: MIT
 */

/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-15 Ian Moffitt
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

defined('ABSPATH') or die("No script kiddies please!");

if(!class_exists('Gist_Post'))
{

	class Gist_Post
	{
		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
			// register actions
			add_action('admin_init', array(&$this, 'admin_init'));
			add_action('admin_menu', array(&$this, 'add_menu'));
			add_action('import_gists', array(&$this, 'import_gists'));
		} // END public function __construct

		/**
		 * Activate the plugin
		 */
		public static function activate()
		{

			wp_schedule_event(time() + 3600, 'hourly', 'import_gists');

			do_action('import_gists');

		} // END public static function activate

		/**
		 * Deactivate the plugin
		 */
		public static function deactivate()
		{
			wp_clear_scheduled_hook('import_gists');

			//For my purposes
			$mycustomposts = get_pages( array( 'post_type' => 'gist', 'number' => -1) );

			if (count($mycustomposts) > 0) {
				foreach( $mycustomposts as $mypost ) {
					// Delete's each post.
					wp_delete_post( $mypost->ID, true);
					// Set to False if you want to send them to Trash.
				}
			}

		} // END public static function deactivate

		/**
		 * hook into WP's admin_init action hook
		 */
		public function admin_init()
		{
			// Set up the settings for this plugin
			$this->init_settings();
			// Possibly do additional admin_init tasks
		} // END public static function activate

		/**
		 * Initialize some custom settings
		 */
		public function init_settings()
		{
			// register the settings for this plugin
			register_setting('gist-as-post-group', 'github_username');
			register_setting('gist-as-post-group', 'allow_cron');
		} // END public function init_custom_settings()

		/**
		 * add a menu
		 */
		public function add_menu()
		{
			add_options_page('Gist as Post Settings', 'Gist as Post', 'manage_options', 'gist-as-post', array(&$this, 'plugin_settings_page'));
		} // END public function add_menu()

		/**
		 * Menu Callback
		 */
		public function plugin_settings_page()
		{
			if(!current_user_can('manage_options'))
			{
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}

			// Render the settings template
			include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
		} // END public function plugin_settings_page()

		public function import_gists()
		{

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

		}

	} // END class Gist_Post
} // END if(!class_exists('Gist_Post'))

if(class_exists('Gist_Post'))
{
	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('Gist_Post', 'activate'));
	register_deactivation_hook(__FILE__, array('Gist_Post', 'deactivate'));

	// instantiate the plugin class
	$gist_post = new Gist_Post();

	// Add a link to the settings page onto the plugin page
	if(isset($gist_post))
	{
		// Add the settings link to the plugins page
		function plugin_settings_link($links)
		{
			$settings_link = '<a href="options-general.php?page=gist-as-post">Settings</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		function custom_template() {
			global $wp_query, $post;

			/* Checks for single template by post type */
			if ($post->post_type == "gist") {
				if(file_exists(plugin_dir_path(__FILE__). '/templates/post.php')) {
					return plugin_dir_path(__FILE__) . '/templates/post.php';
				}
			} else {
				return $single;
			}
		}

		$plugin = plugin_basename(__FILE__);
		add_filter("plugin_action_links_$plugin", 'plugin_settings_link');

		/* Filter the single_template with our custom function*/
		add_filter('template_include', 'custom_template');

		include_once(plugin_dir_path(__FILE__) . 'post-types/gist-post-type.php');

	}
}
