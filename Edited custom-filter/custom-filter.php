<?php

function load_generic_posts() {
   
	// Set HTTP caching headers
    header('Content-Type: application/json');
    header('Cache-Control: public, max-age=3600, must-revalidate');

    

    if (!empty($cachedResponse)) {
        // Cached response found, send it back to the client
        echo $cachedResponse;
        exit;
    }

	
    error_log('load_generic_posts called');
    error_log('AJAX handler called'); // This will log to debug.log if the function is executed
	error_log('Received nonce: ' . $_REQUEST['nonce']);
	error_log('AJAX action: ' . $_REQUEST['action']);

   
	//Categories fetching and filtering...
	// Get the current category object from the queried object
    $current_filter_category = get_queried_object();
    $is_parent_category = ($current_filter_category->parent == 0);

    $filter_categories = array();
    $selected_category_slug = $current_filter_category->slug; // Default to the current category's slug

    // Check if we are in a category context
    if ($current_filter_category && isset($current_filter_category->taxonomy) && $current_filter_category->taxonomy == 'software-category') {
			if ($is_parent_category) {
				// Fetch child categories for a parent category
				$filter_categories = get_categories([
					'taxonomy' => 'software-category',
					'parent' => $current_filter_category->term_id,
					'hide_empty' => true,
				]);
				// Include the parent category itself at the top
				array_unshift($filter_categories, $current_filter_category);
			} else {
				// Fetch sibling categories including the current one for a child category
				$parent_category = get_term($current_filter_category->parent, 'software-category');
				$filter_categories = get_categories([
					'taxonomy' => 'software-category',
					'parent' => $current_filter_category->parent,
					'hide_empty' => true,
				]);
				// Ensure the parent category is included at the top
				array_unshift($filter_categories, $parent_category);
				// In this case, the selected category remains the current one, as initialized
			}
		}
	

	$dropdown_options = [];
    foreach ($filter_categories as $category) {
        $selected = ($category->slug == $selected_category_slug) ? 'selected' : '';
        $dropdown_options[] = [
            'slug' => $category->slug,
            'name' => $category->name,
            'selected' => $selected,
        ];
    }


        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
		error_log('Received nonce: ' . $_REQUEST['nonce']);
		error_log('Received category: ' . $category);
		error_log('Received deployment: ' . implode(', ', $_POST['deployment'] ?? []));
		error_log('Received support: ' . implode(', ', $_POST['support'] ?? []));
	
	
	/*
		// Localize script to pass PHP variables to JavaScript
		wp_localize_script('custom-filter-js', 'ajax_filter_params', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('generic_nonce'),
			'current_category' => $selected_category_slug, // Send the correct category slug for initial AJAX request
			'dropdown_options' => $dropdown_options, // Pass dropdown options with the selected attribute
		]);
	*/
		// Debug: Log the parameters being passed to JavaScript
		error_log('Localizing script with: ' . json_encode(array(
			'current_category' => isset($current_filter_category->slug) ? $current_filter_category->slug : 'default-category',
			'is_parent_category' => $is_parent_category,
			'parent_category_id' => $is_parent_category ? $current_filter_category->term_id : '',
		)));
	
	
	
		//Features Comparison..
		global $wpdb;
        $table_name = $wpdb->prefix . 'generic_table';
       
	
		// Retrieve selected filters from the AJAX request
		$category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
		$deployment_options = isset($_POST['deployment']) ? $_POST['deployment'] : array();
		$support_options = isset($_POST['support']) ? $_POST['support'] : array();
		$selected_features = isset($_POST['features']) ? array_map('sanitize_text_field', $_POST['features']) : [];
		// Get AJAX parameters
		$min_price = isset($_POST['min_price']) ? $_POST['min_price'] : '';
		$max_price = isset($_POST['max_price']) ? $_POST['max_price'] : '';
		$payment_period = isset($_POST['payment_period']) ? $_POST['payment_period'] : '';
		$free_trial = isset($_POST['free_trial']) ? $_POST['free_trial'] : '';
		$free_version = isset($_POST['free_version']) ? $_POST['free_version'] : '';
	
	 	$intersect_post_ids = [];

		// Only proceed if features are selected
		if (!empty($selected_features)) {
			$post_ids_per_feature = [];

			foreach ($selected_features as $feature) {
				$feature_row = $wpdb->get_row($wpdb->prepare("SELECT post_ids FROM $table_name WHERE feature_name = %s", $feature));

				if ($feature_row && !empty($feature_row->post_ids)) {
					$post_ids_for_feature = maybe_unserialize($feature_row->post_ids);

					// Collect post IDs only if they are not empty
					if (!empty($post_ids_for_feature)) {
						$post_ids_per_feature[] = $post_ids_for_feature;
					}
				}
			}

			// Calculate the intersection of post IDs if there are any to compare
			if (!empty($post_ids_per_feature)) {
				$intersect_post_ids = call_user_func_array('array_intersect', $post_ids_per_feature);
			} else {
				// If no specific post IDs match all selected features, set $intersect_post_ids to ensure no posts are returned
				$intersect_post_ids = ['0']; // '0' is an ID that typically doesn't exist
			}
		}
		

        // Initialize query arguments
        $args = array(
            'post_type' => 'saas-reviews',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'software-category',
                    'field' => 'slug',
                    'terms' => $category,
                ),
            ),
            'posts_per_page' => 9,
            'meta_key' => 'review_rating_overall_value',
            'orderby' => 'meta_value_num',
            'order' => 'DESC'
        );
       
	
		// Check if feature checkboxes are used
		$features_used = !empty($_POST['features']);
	
		// Only add 'post__in' if $intersect_post_ids is not empty and contains actual post IDs
		if (!empty($intersect_post_ids)) {
			$args['post__in'] = $intersect_post_ids;
		} elseif ($features_used) {
			$args['post__in'] = ['0'];
		} else {
			unset($args['post__in']);
		}

		// Log $intersect_post_ids again before querying
		error_log('Final intersect_post_ids before query: ' . print_r($intersect_post_ids, true));

		
        // Get the full path to your script
		$script_path = get_template_directory() . '/custom-filter.js';

		// Use filemtime() to get the last modification time of the file
		$version = filemtime($script_path);

		// Enqueue the script with the version parameter
		wp_enqueue_script('custom-filter-js', get_template_directory_uri() . '/custom-filter.js', array('jquery'), $version, true);


		check_ajax_referer('filter_posts_nonce', 'nonce'); // Assuming a nonce is passed for security
            

        

		error_log('Query args: ' . print_r($args, true));

        $query = new WP_Query($args);
    	error_log('Found posts: ' . $query->found_posts);

    // Prepare data for posts that match the ACF criteria
    $post_data = array();
    if ($query->have_posts()) {
		while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            // Assuming 'pricing_packages' is a repeater field within your post
            $pricing_packages = get_field('pricing_packages', $post_id);
            $package_meets_criteria = false;
			$free_version_field = get_field('free_version', $post_id); // "Free Version" or "No Free Version"
            $free_trial_field = get_field('trial', $post_id); // "Free Trial" or "No Free Trial"
            $payment_period = $_POST['payment_period'] ?? ''; // 'per Month' or 'per Year'
			$pricing_packages = get_field('pricing_packages', $post_id);
            // Initialize $package_meets_criteria based on whether any price or period filters are set.
            $has_price_or_period_filter = isset($_POST['min_price']) || isset($_POST['max_price']) || !empty($_POST['payment_period']);
            $package_meets_criteria = !$has_price_or_period_filter; // False if filters are set, true otherwise.


			
			 // Additional fields retrieval
			$affiliate_link = get_field('affiliate_link', $post_id);
			$intro_content = get_field('intro_content', $post_id); // Assuming 'intro_content' is a group field
			$content = $intro_content['content'] ?? '';

			// Retrieving 'top_features' repeater field
			$top_features = get_field('top_features', $post_id);
			$formatted_top_features = array();
			if ($top_features) {
				foreach ($top_features as $feature) {
					$formatted_top_features[] = array(
						'feature' => $feature['feature'],
						'features_star_rating' => $feature['features_star_rating'],
					);
				}
			}

			// Retrieving 'pros_&_cons' repeater field
			$pros_cons = get_field('pros_&_cons', $post_id);
			$formatted_pros_cons = array();
			if ($pros_cons) {
				foreach ($pros_cons as $item) {
					$formatted_pros_cons[] = array(
						'advantages' => $item['advantages'],
						'disadvantages' => $item['disadvantages'],
					);
				}
			}
			
			$pros_html = '<ul class="pros">';
			$cons_html = '<ul class="cons">';
			foreach ($pros_cons as $item) {
				$pros_html .= '<li>' . '<i class="fa fa-plus-circle"></i>' . $item['advantages'] . '</li>';
				$cons_html .= '<li>' . '<i class="fa fa-minus-circle"></i>' . $item['disadvantages'] . '</li>';
			}
			$pros_html .= '</ul>';
			$cons_html .= '</ul>';

			$features_html = '<ul class="features">';
			foreach ($top_features as $feature) {
				$features_html .= '<li>' . $feature['feature'] . ' - ' . $feature['features_star_rating'] . ' stars</li>';
			}
			$features_html .= '</ul>';
			
			
             if ($has_price_or_period_filter && is_array($pricing_packages)) {
                $package_meets_criteria = false;
                if (is_array($pricing_packages)) {
                    foreach ($pricing_packages as $package) {
                        $price = $package['pricing'];
                        $payment_period_selected = $package['monthly_or_yearly'];
						$min_price = $_POST['min_price'] !== '' ? floatval($_POST['min_price']) : null;
                    	$max_price = $_POST['max_price'] !== '' ? floatval($_POST['max_price']) : null;
                    	$payment_period = $_POST['payment_period'] ?? '';

					// Debugging output
					error_log("Package Price: {$price}, Payment Period Selected: {$payment_period_selected}, Min Price: {$min_price}, Max Price: {$max_price}, Payment Period Filter: {$payment_period}");

					if (($min_price === null || $price >= $min_price) &&
                            ($max_price === null || $price <= $max_price) &&
                            (empty($payment_period) || $payment_period_selected === $payment_period)) {
                            $package_meets_criteria = true;
                            break; // Found a package that meets the criteria
                        } else {
						error_log("Package does not meet criteria");
						}
					} 
				}
			} else {
					error_log('Expected $pricing_packages to be an array or object, got ' . gettype($pricing_packages) . ' instead.');
			}
			
			// Adjustments for "Free Trial" and "Free Version" fields
            $free_version = $_POST['free_version'] ?? ''; // Expecting "Free Version" or "No Free Version"
            $free_trial = $_POST['free_trial'] ?? ''; // Expecting "Free Trial" or "No Free Trial"

			$meets_free_version_criteria = empty($free_version) || $free_version_field === $free_version;
            $meets_free_trial_criteria = empty($free_trial) || $free_trial_field === $free_trial;

			// Fetch the ACF group field data for the current post
			$product_offers = get_field('product_offers_to_consumers', $post_id);

			// Verify if post's implementation and support match all selected options
			$implementation_matches = empty($deployment_options) || 
									  (!empty($product_offers['immplementation']) && 
									   !array_diff($deployment_options, $product_offers['immplementation']));

			$support_matches = empty($support_options) || 
							   (!empty($product_offers['support']) && 
								!array_diff($support_options, $product_offers['support']));

			if ($package_meets_criteria && $meets_free_version_criteria && $meets_free_trial_criteria && $implementation_matches && $support_matches) {
				// Post matches all selected ACF criteria; prepare its data
				$name = get_field('software_name', $post_id);
				$rating = get_field('review_rating', $post_id)['overall_value'];
				if (!$rating) {
					error_log('Rating not found for post: ' . get_the_title($post_id));
					continue;
				}

				$post_data[] = array(
					'ID' => $post_id,
					'title' => $name,
					'rating' => $rating,
					'image' => get_the_post_thumbnail_url($post_id, 'full'),
					'starsWidth' => (floatval($rating) / 5) * 100 . '%',
					'link' => get_permalink($post_id),
					'implementation' => $product_offers['immplementation'] ?? [],
					'support' => $product_offers['support'] ?? [],
					'pricing' => $package['pricing'] ?? [],
					'monthly_or_yearly' => $package['monthly_or_yearly'] ?? [],
					'currency' => $package['currency'] ?? [],
					'trial' => $free_trial,
					'free_version' => $free_version,
					'affiliate_link' => $affiliate_link,
					'content' => $content,
					'pros_html' => $pros_html,
					'cons_html' => $cons_html,
					'features_html' => $features_html,
				);
				
				
			}
		}
		// Check if there's a cached version of the response
		$cacheKey = 'load_filtered_posts_' . md5(serialize($post_data));
		$cachedResponse = get_transient($cacheKey);
		
		// After preparing $post_data
		$json_response = json_encode($post_data);
		set_transient($cacheKey, $json_response, 3600);
		echo $json_response;
		exit;
		
		wp_reset_postdata();
	} else {
		error_log('No posts found for category: ' . $category);
	}

    
    

	// Send filtered post data back as JSON
	//wp_send_json($post_data);

    
}


function load_generic_features() {
	global $wpdb;
    $table_name = $wpdb->prefix . 'generic_table';

    // Verify nonce for security
    check_ajax_referer('filter_posts_nonce', 'nonce');

    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $search_term = '%' . $wpdb->esc_like($search) . '%';
    $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE feature_name LIKE %s ORDER BY occurrence_count DESC, feature_name ASC LIMIT %d, %d", $search_term, $offset, $limit);
    $features = $wpdb->get_results($sql);

    $total_features_sql = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE feature_name LIKE %s", $search_term);
    $total_features = $wpdb->get_var($total_features_sql);
    $total_pages = ceil($total_features / $limit);

    // Generate HTML for the features list
    $features_html = '';
    foreach ($features as $feature) {
        $features_html .= '<label class="feature"><input type="checkbox" name="features[]" value="' . esc_attr($feature->feature_name) . '"> ' . esc_html($feature->feature_name) . '</label>';
    }

    // Return data
    $response_data = array(
		'features_html' => $features_html,
		'total_pages' => $total_pages,
		'current_page' => $page,
	);
	
    // Logic to fetch and return features data including pagination
    wp_send_json($response_data);
	
}
add_action('wp_ajax_load_filtered_features', 'load_generic_features');
add_action('wp_ajax_nopriv_load_filtered_features', 'load_generic_features');


function my_ajax_handler() {
    // Check for nonce security
    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
    
    if (!wp_verify_nonce($nonce, 'filter_posts_nonce')) {
        // Nonce is invalid
        wp_send_json_error(['error' => 'Invalid nonce.']);
    } else {
        // Nonce is valid, proceed with your AJAX handling
        wp_send_json_success(['message' => 'Nonce is valid.']);
    }
}
add_action('wp_ajax_my_action', 'custom-filter-js');
add_action('wp_ajax_nopriv_my_action', 'custom-filter-js');



function custom_filter_shortcode() {
    // AJAX action hook
    //add_action('wp_ajax_load_filtered_posts', 'load_generic_posts');
    //add_action('wp_ajax_nopriv_load_filtered_posts', 'load_generic_posts');


   // Get the current category object from the queried object
    $current_filter_category = get_queried_object();
    $is_parent_category = ($current_filter_category->parent == 0);

    $filter_categories = array();
    $selected_category_slug = $current_filter_category->slug; // Default to the current category's slug

    // Check if we are in a category context
    if ($current_filter_category && isset($current_filter_category->taxonomy) && $current_filter_category->taxonomy == 'software-category') {
        if ($is_parent_category) {
            // Fetch child categories for a parent category
            $filter_categories = get_categories([
                'taxonomy' => 'software-category',
                'parent' => $current_filter_category->term_id,
                'hide_empty' => true,
            ]);
            // Include the parent category itself at the top
            array_unshift($filter_categories, $current_filter_category);
        } else {
            // Fetch sibling categories including the current one for a child category
            $parent_category = get_term($current_filter_category->parent, 'software-category');
            $filter_categories = get_categories([
                'taxonomy' => 'software-category',
                'parent' => $current_filter_category->parent,
                'hide_empty' => true,
            ]);
            // Ensure the parent category is included at the top
            array_unshift($filter_categories, $parent_category);
            // In this case, the selected category remains the current one, as initialized
        }
    }

    // Prepare categories for the dropdown, ensuring the current or parent category is accurately represented
    $dropdown_options = [];
    foreach ($filter_categories as $category) {
        $selected = ($category->slug == $selected_category_slug) ? 'selected' : '';
        $dropdown_options[] = [
            'slug' => $category->slug,
            'name' => $category->name,
            'selected' => $selected,
        ];
    }
	
	

    // Shortcode output (possibly including form/filter HTML not shown here for brevity)
    ob_start();

?>
    

    <div class="layout-container">
        <div class="filters-container">
			<div class="dopdown-filters-container">
				<span class="mobile-filters"> Filters <i class='fa fa-cog' style='color: white; margin: 7px; float:right;'></i> </span>
				
				<div class="dropdown-filter">
					<span class="dropbtn">Category</span>
					<div class="dropdown-content">
						<select class="filter" id="category-filter-dropdown" name="category">
							<?php
							// Assume $current_filter_category holds the current category object.
							// $filter_categories should already be populated as per your logic.
							foreach ($filter_categories as $filter_category):
								// Determine if this category should be selected
								$selected = ($current_filter_category->slug === $filter_category->slug) ? 'selected' : '';
							?>
								<option value="<?php echo esc_attr($filter_category->slug); ?>" <?php echo $selected; ?>>
									<?php echo esc_html($filter_category->name); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>       
				</div>

            
	            <div class="dropdown-filter">
                	<span class="dropbtn">Price Filter</span>
                	<div class="dropdown-content">
                    	<div class="price-filter">
							<input type="number" id="min_price" placeholder="Min Price">
							<input type="number" id="max_price" placeholder="Max Price">
							<select id="payment_period">
								<option value="per Month">Monthly</option>
								<option value="per Year">Yearly</option>
							</select>
							<input type="checkbox" id="free_trial" name="trial" value="Free Trial"> Free Trial
							<input type="checkbox" id="free_version" name="free_version" value="Free Version"> Free Version
						</div>
               		</div>
				</div>
				
				<div class="dropdown-filter">
					<div class="dropbtn">Deployment</div>
					<div class="dropdown-content">
						<div class="deployment-filter">
							<div>
								<label class="deployment-label"><input type="checkbox" id="deployment_web"  value="Web Based"> Web Based<br></label>
								<label class="deployment-label"><input type="checkbox" id="deployment_windows" name="deployment[]" value="Windows"> Windows<br></label>
								<label class="deployment-label"><input type="checkbox" id="deployment_mac" name="deployment[]" value="Mac OS"> Mac OS<br></label>
								<label class="deployment-label"><input type="checkbox" id="deployment_linux" name="deployment[]" value="Linux"> Linux<br></label>
								<label class="deployment-label"><input type="checkbox" id="deployment_android" name="deployment[]" value="Android"> Android<br></label>
								<label class="deployment-label"><input type="checkbox" id="deployment_ios" name="deployment[]" value="iOS"> iOS</label>
							</div>
						</div>
					</div>
				</div>
				
				<div class="dropdown-filter">
					<div class="dropbtn">Support</div>
					<div class="dropdown-content">
						<div class="support-filter">
							<div id="support-list">
								<label class="support-label"><input type="checkbox" id="support_web" name="support[]" value="Phone Support"> Phone Support <br></label>
								<label class="support-label"><input type="checkbox" id="support_windows" name="support[]" value="Email/Help Desk"> Email/Help Desk <br></label>
								<label class="support-label"><input type="checkbox" id="support_mac" name="support[]" value="AI Chat Bot"> AI Chat Bot<br></label>
								<label class="support-label"><input type="checkbox" id="support_linux" name="support[]" value="Live Support"> Live Support <br></label>
								<label class="support-label"><input type="checkbox" id="support_android" name="support[]" value="24/7 Support"> 24/7 Support <br></label>
								<label class="support-label"><input type="checkbox" id="support_ios" name="support[]" value="Forum & Community"> Forum & Community <br></label>
								<label class="support-label"><input type="checkbox" id="support_ios" name="support[]" value="Knowledge Base"> Knowledge Base</label>
							</div>
						</div>
					</div>
				</div>
				
				<div class="dropdown-filter">
					<div class="dropbtn"> Features </div>
					<div class="dropdown-content">
						<div class="features-filter">
							<input type="text" id="feature_search" placeholder="Search Features">
							<div id="features-list">
								<?php foreach ($features as $feature): ?>
									<label class="feature">
										<input type="checkbox" name="features[]" value="<?php echo esc_attr($feature->feature_name); ?>">
										<?php echo esc_html($feature->feature_name); ?>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
						<div id="pagination" class="pagination">
							
							<span id="pagination-numbers" class="pagination-numbers"></span>
	
						</div>
					</div>
				</div>
				
			</div>
        </div>


        <div class="posts-grid-container" id="posts-grid-contain">
            <!-- Posts grid HTML goes here -->
        </div>


    </div>



    <?php

    return ob_get_clean();
}
add_shortcode('custom_filter', 'custom_filter_shortcode');


?>