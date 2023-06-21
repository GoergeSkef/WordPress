<?php
function add_project_type_taxonomy(){

	//set the name of the taxonomy
	$taxonomy = 'project_type';
	//set the post types for the taxonomy
	$object_type = 'project';


	//define arguments to be used
	$args = array(
		'label'             => "Project types",
		'hierarchical'      => true
	);

	//call the register_taxonomy function
	register_taxonomy($taxonomy, $object_type, $args);
}
add_action('init','add_project_type_taxonomy');


function add_project_skill_taxonomy(){

	//set the name of the taxonomy
	$taxonomy = 'project_skill';
	//set the post types for the taxonomy
	$object_type = 'project';


	//define arguments to be used
	$args = array(
		'label'             => "Project skillz",
		'hierarchical'      => false
	);

	//call the register_taxonomy function
	register_taxonomy($taxonomy, $object_type, $args);
}
add_action('init','add_project_skill_taxonomy');


/*
 * Here it creates the custom post type "project".
 */
add_action( 'init', 'project_post_init' );

function project_post_init() {

	$labels = array(
		'name'               => ( 'Projects'),
		'singular_name'      => ( 'Project'),

	);

	$args = array(

		'labels'             => $labels,
		'public'             => true,
		'hierarchical'       => false,
		'menu_icon'          => 'dashicons-portfolio',
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail')
	);

	register_post_type( 'project', $args );
}

/**
 * Add new menu
 */

add_action( 'after_setup_theme', 'register_page_menu' );
function register_page_menu() {
	register_nav_menu( 'header-nav', 'Page menu' );
}

/**
 * Add new sidebars
 */

add_action( 'widgets_init', 'page_sidebar_widget' );
function page_sidebar_widget() {
	register_sidebar( array(
		'name' => 'Page sidebar',
		'id' => 'page-sidebar',
	) );
}

add_action( 'widgets_init', 'project_sidebar_widget' );
function project_sidebar_widget() {
	register_sidebar( array(
		'name' => 'Project sidebar',
		'id' => 'project-sidebar',
	) );
}

/*
 * Adds the following theme supports
 */
add_theme_support( 'post-thumbnails' );
add_theme_support( 'post-formats' );



/**
 * Add custom image support
 */

add_image_size( 'grid_thumbnail', 300, 300, true );
add_image_size( 'single_large', 660, 400, false );

add_action( 'wp_enqueue_scripts', 'load_stylesheet' );
function load_stylesheet() {
	wp_enqueue_style( 'styles', get_stylesheet_uri() . "/style.css" );
}

?>
