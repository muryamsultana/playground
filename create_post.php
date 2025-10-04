<?php
/*
Plugin Name: Create 'Movie' custom post type
Description: Create Movies post type
Version: 1.0.0
Author: Muryam
License: GPL-2.0+
*/
add_action('init', 'register_movie_post_type');

function register_movie_post_type() {
    $labels = array(
        'name'                  => _x('Movies', 'Post type general name', 'textdomain'),
        'singular_name'         => _x('Movie', 'Post type singular name', 'textdomain'),
        'menu_name'             => _x('Movies', 'Admin Menu text', 'textdomain'),
        'name_admin_bar'        => _x('Movie', 'Add New on Toolbar', 'textdomain'),
        'add_new'               => __('Add New', 'textdomain'),
        'add_new_item'          => __('Add New Movie', 'textdomain'),
        'new_item'              => __('New Movie', 'textdomain'),
        'edit_item'             => __('Edit Movie', 'textdomain'),
        'view_item'             => __('View Movie', 'textdomain'),
        'all_items'             => __('All Movies', 'textdomain'),
        'search_items'          => __('Search Movies', 'textdomain'),
        'parent_item_colon'     => __('Parent Movies:', 'textdomain'),
        'not_found'             => __('No movies found.', 'textdomain'),
        'not_found_in_trash'    => __('No movies found in Trash.', 'textdomain'),
        'featured_image'        => __('Movie Poster', 'textdomain'),
        'set_featured_image'    => __('Set movie poster', 'textdomain'),
        'remove_featured_image' => __('Remove movie poster', 'textdomain'),
        'use_featured_image'    => __('Use as movie poster', 'textdomain'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'movie'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-video-alt3',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'comments'),
        'show_in_rest'       => true,
        'taxonomies'         => array('category', 'post_tag'),
    );

    register_post_type('movie', $args);
}
?>
