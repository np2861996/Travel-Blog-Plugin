<?php

  /**
 * Create two taxonomies, genres and writers for the post type "book".
 *
 * @see register_post_type() for registering custom post types.
 */


function wpdocs_create_destination_taxonomies() {

	// Add new taxonomy, NOT hierarchical (like tags)
	$labels = array(
		'name'                       => _x( 'destination', 'taxonomy general name', 'textdomain' ),
		'singular_name'              => _x( 'destination', 'taxonomy singular name', 'textdomain' ),
		'search_items'               => __( 'Search destination', 'textdomain' ),
		'popular_items'              => __( 'Popular destination', 'textdomain' ),
		'all_items'                  => __( 'All destination', 'textdomain' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit destination', 'textdomain' ),
		'update_item'                => __( 'Update destination', 'textdomain' ),
		'add_new_item'               => __( 'Add New destination', 'textdomain' ),
		'new_item_name'              => __( 'New destination Name', 'textdomain' ),
		'separate_items_with_commas' => __( 'Separate destination with commas', 'textdomain' ),
		'add_or_remove_items'        => __( 'Add or remove destination', 'textdomain' ),
		'choose_from_most_used'      => __( 'Choose from the most used destination', 'textdomain' ),
		'not_found'                  => __( 'No destination found.', 'textdomain' ),
		'menu_name'                  => __( 'destination', 'textdomain' ),
	);

	$args = array(
		'hierarchical'          => true,
		'labels'                => $labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'destination' ),
        'show_in_rest' => true,
	);

	register_taxonomy( 'destination', 'post', $args );
}
// hook into the init action and call create_book_taxonomies when it fires
add_action( 'init', 'wpdocs_create_destination_taxonomies', 0 );

function wpdocs_create_theme_taxonomies() {
	

	// Add new taxonomy, NOT hierarchical (like tags)
	$labels = array(
		'name'                       => _x( 'Theme', 'taxonomy general name', 'textdomain' ),
		'singular_name'              => _x( 'Theme', 'taxonomy singular name', 'textdomain' ),
		'search_items'               => __( 'Search Theme', 'textdomain' ),
		'popular_items'              => __( 'Popular Theme', 'textdomain' ),
		'all_items'                  => __( 'All Theme', 'textdomain' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Theme', 'textdomain' ),
		'update_item'                => __( 'Update Theme', 'textdomain' ),
		'add_new_item'               => __( 'Add New Theme', 'textdomain' ),
		'new_item_name'              => __( 'New Theme Name', 'textdomain' ),
		'separate_items_with_commas' => __( 'Separate Theme with commas', 'textdomain' ),
		'add_or_remove_items'        => __( 'Add or remove Theme', 'textdomain' ),
		'choose_from_most_used'      => __( 'Choose from the most used Theme', 'textdomain' ),
		'not_found'                  => __( 'No Theme found.', 'textdomain' ),
		'menu_name'                  => __( 'Theme', 'textdomain' ),
	);

	$args = array(
		'hierarchical'          => true,
		'labels'                => $labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'theme' ),
        'show_in_rest' => true,
	);

	register_taxonomy( 'theme', 'post', $args );
}
// hook into the init action and call create_book_taxonomies when it fires
add_action( 'init', 'wpdocs_create_theme_taxonomies', 0 );



