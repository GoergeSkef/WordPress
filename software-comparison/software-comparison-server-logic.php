<?php

function custom_search_form_shortcode() {
    ob_start();
    ?>
    <div class="custom-search-form">
        <select id="taxonomy_selector" class="comparison-taxonomy-selector">
            <option value="">Select Category</option>
            <?php
            $categories = get_terms(array(
                'taxonomy' => 'software-category',
                'hide_empty' => true,
                'parent' => 0 // Only fetch terms that are parent categories
            ));
	

            if (is_wp_error($categories)) {
                error_log('Error fetching categories: ' . $categories->get_error_message());
            } else {
                foreach ($categories as $category) {
                    echo '<option value="' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</option>';
                }
            }
            ?>
        </select>
        <div class='software-comparison-search'>
            <input type="text" id="software_input_1" placeholder="Enter first software name..." class="software-search-input">
			<div id="suggestions_container_1" class="suggestions-container"></div>
    
            <input type="text" id="software_input_2" placeholder="Enter second software name..." class="software-search-input">
			<div id="suggestions_container_2" class="suggestions-container"></div>
        </div>
        <button class="compare_button">Compare</button>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('software-comparison', 'custom_search_form_shortcode');

add_action('wp_ajax_fetch_search_suggestions', 'fetch_generic_search_suggestions');
add_action('wp_ajax_nopriv_fetch_search_suggestions', 'fetch_generic_search_suggestions');

function fetch_generic_search_suggestions() {
    $taxonomy_slug = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : '';
	
    $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';

    if (!$taxonomy_slug || !$search_term) {
        error_log('Missing required parameters for fetch_generic_search_suggestions.');
        wp_send_json_error(array('message' => 'Missing required parameters.'));
        wp_die();
    }

    $term = get_term_by('slug', $taxonomy_slug, 'software-category');
    $term_ids = array();

    if (!$term) {
        error_log('Failed to get term by slug in fetch_generic_search_suggestions.');
    } else {
        $child_terms = get_terms('software-category', array('child_of' => $term->term_id, 'hide_empty' => false));
        $term_ids[] = $term->term_id; // Include the parent term ID
        foreach ($child_terms as $child) {
            $term_ids[] = $child->term_id; // Add child term IDs
        }
    }

    $args = array(
        'post_type' => 'saas-reviews',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'software-category',
                'field' => 'term_id',
                'terms' => $term_ids,
                'include_children' => false
            ),
        ),
        'meta_query' => array(
            array(
                'key' => 'software_name',
                'value' => $search_term,
                'compare' => 'LIKE'
            ),
        ),
    );

    $query = new WP_Query($args);
    $results = array();

    if (!$query->have_posts()) {
        error_log('No posts found in fetch_generic_search_suggestions.');
    } else {
        while ($query->have_posts()) {
            $query->the_post();
            $results[] = array(
                'id' => get_the_ID(),
                'name' => get_field('software_name'),
                'image' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail')
            );
        }
    }

    wp_reset_postdata();

    if (empty($results)) {
        wp_send_json_error(array('message' => 'No results found.'));
    } else {
        wp_send_json_success($results);
    }
	
	check_ajax_referer('ajax_nonce', 'nonce'); //verify nonce

    wp_die();
}




function custom_rewrite_rules() {
    add_rewrite_tag('%taxonomy_name%', '([^&]+)');
    add_rewrite_tag('%software1%', '([^&]+)');
    add_rewrite_tag('%software2%', '([^&]+)');

    add_rewrite_rule('^software-comparison-tool/Compare-([^/]+)-solutions/?$','index.php?pagename=software-comparison-tool&taxonomy_name=$matches[1]','top');
    add_rewrite_rule('^software-comparison-tool/Compare-([^/]+)-solutions/([^/]+)-vs-([^/]+)/?$', 'index.php?pagename=software-comparison-tool&taxonomy_name=$matches[1]&software1=$matches[2]&software2=$matches[3]', 'top');
}
add_action('init', 'custom_rewrite_rules');

function my_custom_title_change($title_parts) {
    global $wp_query;

    // Log the current query vars for debugging
    error_log('Current query vars: ' . print_r($wp_query->query_vars, true));

    // Check if we are on the "software comparison tool" page
    if (isset($wp_query->query_vars['pagename']) && $wp_query->query_vars['pagename'] == 'software-comparison-tool') {
        error_log('On software comparison tool page.');

        $taxonomy_name = get_query_var('taxonomy_name', false);
        $software1 = get_query_var('software1', false);
        $software2 = get_query_var('software2', false);

        // If viewing a software comparison
        if ($software1 && $software2) {
            $title_parts['title'] = esc_html($software1) . ' vs ' . esc_html($software2) . ' Comparison';
            error_log('Software comparison view: ' . $title_parts['title']);
        }
        // If viewing a taxonomy comparison
        elseif ($taxonomy_name) {
            $title_parts['title'] = 'Software Comparison for ' . esc_html($taxonomy_name) . ' Solutions';
            error_log('Taxonomy comparison view: ' . $title_parts['title']);
        }
        else {
            error_log('Neither software comparison nor taxonomy comparison query variables are set.');
        }
    }
    else {
        error_log('Not on the software comparison tool page or pagename not set.');
    }

    return $title_parts;
}
add_filter('document_title_parts', 'my_custom_title_change');


//Template redirect logic
//
add_action('template_redirect', function() {
    global $wp_query;

    error_log('Checking for software comparison tool page with additional parameters...');
    
    // Check if we are on the "software comparison tool" page with additional parameters
    if (isset($wp_query->query_vars['pagename']) && $wp_query->query_vars['pagename'] == 'software-comparison-tool') {

        // Determine if this is a category view
        $taxonomy_name = get_query_var('taxonomy_name', false);

        // Determine if this is a software comparison view
        $software1 = get_query_var('software1', false);
        $software2 = get_query_var('software2', false);

        // Check if both software comparison variables are set, indicating a comparison view
        if ($software1 && $software2) {
            // Load the software comparison template
            $template_path = get_stylesheet_directory() . '/software-comparison/templates/products-comparison-template.php';
            if (file_exists($template_path)) {
                include($template_path);
                exit;
            } else {
                error_log('Error: Software comparison template not found at ' . $template_path);
            }
        }
        // Otherwise, if only the taxonomy_name is set, load the taxonomy comparison template
        elseif ($taxonomy_name) {
            // Load the taxonomy comparison template
            $template_path = get_stylesheet_directory() . '/software-comparison/templates/taxonomy-comparison-template.php';
            if (file_exists($template_path)) {
                include($template_path);
                exit;
            } else {
                error_log('Error: Taxonomy comparison template not found at ' . $template_path);
            }
        } else {
            error_log('Neither software comparison nor taxonomy comparison query variables are set.');
        }
    }
	
});





//Top ranking software products in chosen category script
function top_saas_in_taxonomy () {
	
    // Versioning based on file modification time for cache busting
    $software_rank = filemtime(get_stylesheet_directory() . '/software-comparison/top-ranking-software.js');
    
    //js enqueue
    wp_enqueue_script('taxonomi-top-ranking-saas', get_stylesheet_directory_uri() . '/software-comparison/top-ranking-software.js', array('jquery'), $software_rank, true);

    
    $chosen_taxonomy_name = get_query_var('taxonomy_name', false);
	// Localize the script with necessary data
	wp_localize_script('taxonomi-top-ranking-saas', 'top_ranking_parameters', array(
		'name' => $chosen_taxonomy_name,
		'ajax_url' => admin_url('admin-ajax.php'),
		// You can add other data your JavaScript might need here
		'nonce' => wp_create_nonce('generic_nonce'), // Example for nonce, adjust as needed
	));

}

add_action( 'wp_enqueue_scripts', 'top_saas_in_taxonomy' );



//top ranking software solution of the selected category
function top_ranking_software () {
    
    check_ajax_referer('top_rank_ajax_nonce', 'nonce'); //verify nonce
    

    $taxonomy = isset($_POST['taxonomy']) ? $_POST['taxonomy'] : 'default-category';

    $args = array(
        'post_type' => 'saas-reviews',
        'tax_query' => array(
            array(
                'taxonomy' => 'software-category',
                'field' => 'slug',
                'terms' => $taxonomy
            )
        ),
        'posts_per_page' => 9,
        'meta_key' => 'review_rating_overall_value',
        'orderby' => 'meta_value_num',
        'order' => 'DESC'
    );

    $query = new WP_Query($args);
    
    $data = array();
    if ($query->have_posts()) : 
        while ($query->have_posts()) : $query->the_post();
            $name = get_field('software_name');
            $rating = get_field('review_rating')['overall_value'];
            if (!$rating) {
                error_log('Rating not found for post: ' . get_the_title());
            }
            $data[] = array(
                'title' => $name,
                'rating' => $rating,
                'image' => get_the_post_thumbnail_url(get_the_ID(), 'full'),
                'starsWidth' => (floatval($rating) / 5) * 100 . '%',
                'link' => get_the_permalink() // Add the post link
            );
        endwhile;
    else :
        error_log('No posts found for category: ' . $category);
    endif;
    wp_reset_postdata();

    wp_send_json($data);
}

add_action('wp_ajax_fetch_top_rank_software', 'top_ranking_software');
add_action('wp_ajax_nopriv_fetch_top_rank_software', 'top_ranking_software');







