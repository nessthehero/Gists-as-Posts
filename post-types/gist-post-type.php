<?php

function create_post_type_gist()
{
    register_taxonomy_for_object_type('category', 'gist'); // Register Taxonomies for Category

    register_post_type('gist', // Register Custom Post Type
        array(
        'labels' => array(
            'name' => __('GitHub Gist', 'gist'), // Rename these to suit
            'singular_name' => __('Gist', 'gist'),
            'add_new' => __('Add New', 'gist'),
            'add_new_item' => __('Add New Gist', 'gist'),
            'edit' => __('Edit', 'gist'),
            'edit_item' => __('Edit Gist', 'gist'),
            'new_item' => __('New Gist', 'gist'),
            'view' => __('View Gist', 'gist'),
            'view_item' => __('View Gist', 'gist'),
            'search_items' => __('Search Gist', 'gist'),
            'not_found' => __('No Gists found', 'gist'),
            'not_found_in_trash' => __('No Gists found in Trash', 'gist')
        ),
        'public' => true,
        'hierarchical' => false, // Allows your posts to behave like Hierarchy Pages
        'has_archive' => true,
        'supports' => array(
            'title',
            'editor',
            'revisions'
        ), // Go to Dashboard Custom Gist Blank post for supports
        'can_export' => false, // Allows export in Tools > Export
        'taxonomies' => array(
            // 'post_tag',
            'category'
        ) // Add Category and Post Tags support
    ));
}

add_action('init', 'create_post_type_gist'); // Add our Custom Post Type

// Meta data
function add_meta_box_gist() {
    // Build meta box
    add_meta_box(
        'gist_meta',
        'GitHub Gist Additional Information',
        'show_custom_meta_gist',
        'gist'
        );
}

add_action('add_meta_boxes', 'add_meta_box_gist');

// Define fields
$prefix = 'gist_';
$custom_meta_fields_gist = array();

$custom_meta_fields_gist[] =  array(
    'label' => 'Gist ID',
    'desc'  => 'ID of Gist from Github. Do not modify.',
    'id'    => $prefix.'id',
    'type'  => 'text'
);

// $custom_meta_fields_gist[] =  array(
//     'label' => 'Project URL',
//     'desc'  => 'URL of project/piece',
//     'id'    => $prefix.'url',
//     'type'  => 'text'
// );

// $custom_meta_fields_gist[] =  array(
//     'label' => 'Description',
//     'desc'  => 'Describe the project',
//     'id'    => $prefix.'description',
//     'type'  => 'textarea'
// );

// $custom_meta_fields_gist[] =  array(
//     'label' => 'Date Started',
//     'desc'  => 'Date project kicked off',
//     'id'    => $prefix.'start_date',
//     'type'  => 'date'
// );

// $custom_meta_fields_gist[] =  array(
//     'label' => 'Date Completed',
//     'desc'  => 'Date project launched or work was completed',
//     'id'    => $prefix.'end_date',
//     'type'  => 'date'
// );

// if (wp_count_posts('job-position')->publish > 0) {

//     $custom_meta_fields_portfolio[] =  array(
//         'label' => 'Job created at',
//         'desc'  => 'Your position at the time you created this piece',
//         'id'    => $prefix.'job_position',
//         'type'  => 'post',
//         'post-type' => 'job-position'
//     );

// }

// array_push(
//     array(
//          'label' => '',
//          'desc'  => '',
//          'id'    => $prefix.'',
//          'type'  => ''
//     ),
// , $custom_meta_fields_portfolio);

// Build display of meta box
function show_custom_meta_gist()
{
    global $custom_meta_fields_gist, $post;

    echo util_output_fields_table($custom_meta_fields_gist, $post, wp_create_nonce(basename(__FILE__)), 'gist_meta_box');

}

// Save the Data
function save_custom_meta_gist($post_id) {
    global $custom_meta_fields_gist;

    // verify nonce
    if (!wp_verify_nonce($_POST['gist_meta_box_nonce'], basename(__FILE__)))
        return $post_id;
    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $post_id;
    // check permissions
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id))
            return $post_id;
        } elseif (!current_user_can('edit_post', $post_id)) {
            return $post_id;
    }

    // loop through fields and save the data
    foreach ($custom_meta_fields_gist as $field) {
        $old = get_post_meta($post_id, $field['id'], true);
        $new = $_POST[$field['id']];
        if ($new && $new != $old) {
            update_post_meta($post_id, $field['id'], $new);
        } elseif ('' == $new && $old) {
            delete_post_meta($post_id, $field['id'], $old);
        }
    } // end foreach
}
add_action('save_post', 'save_custom_meta_gist');
