<?php
get_header(); // Include header
?>
<?php
// Assuming the software names are passed via the URL as query vars 'software1' and 'software2'.
$software1_name = urldecode(get_query_var('software1'));
$software2_name = urldecode(get_query_var('software2'));

// Function to query software post by its ACF 'software_name' field
function query_software_by_name($software_name) {
	$formatted_software_name = str_replace('-', ' ', $software_name);
	
    $args = array(
        'post_type' => 'saas-reviews', // Adjust to your specific post type
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => 'software_name',
                'value' => $formatted_software_name,
                'compare' => 'LIKE',
            ),
        ),
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        return $query->posts[0]->ID;
    } else {
        error_log("No posts found matching the software name: $software_name");
        return null;
    }
}

// Retrieve software IDs
$software1_id = query_software_by_name($software1_name);
$software2_id = query_software_by_name($software2_name);

if (!$software1_id || !$software2_id) {
    error_log("Failed to retrieve one or both software IDs. Software1 ID: $software1_id, Software2 ID: $software2_id");
    echo "Software information is currently unavailable.";
    die('Failed to load the comparison. Please check the software names and try again.');
}



// Function to render stars based on ratings
function render_stars($rating) {
    $output = '';
    $fullStars = floor($rating);
    $fraction = $rating - $fullStars;
    $emptyStars = 5 - ceil($rating);

    for ($i = 0; $i < $fullStars; $i++) {
        $output .= '<span class="star full">★</span>';
    }
    if ($fraction > 0) { // If there's a fractional part
        // Output a fractional star. Adjust CSS or include a specific method to visually represent this fractional part
        $output .= "<span class=\"star fractional\" style=\"width:" . ($fraction * 100) . "%\">★</span>";
    }
    for ($i = 0; $i < $emptyStars; $i++) {
        $output .= '<span class="star empty">☆</span>';
    }
    return $output;
}


?>
<div class="software-comparison-container">

    <div class="comparison-main-section">
		<div class="comparison-main">
			<?php for ($i = 1; $i <= 2; $i++): 
				$software_id = ${"software{$i}_id"};
				$review_rating = get_field('review_rating', $software_id); // Retrieve the group field here ?>
				<div class="software software-<?php echo $i; ?>">
					<img src="<?php echo get_the_post_thumbnail_url($software_id); ?>" class="comparison-img" alt="Software Image">
					<h2><?php echo get_field('software_name', $software_id); ?></h2>
					<p>Overall Value: <?php echo render_stars($review_rating['overall_value']); ?></p>
					<a href="<?php echo get_permalink($software_id); ?>">Read Review</a>
					<a href="<?php echo get_field('affiliate_link', $software_id); ?>">Visit Website</a>
				</div>
			<?php endfor; ?>
		</div>
	</div>


    <!-- Ratings Section -->
	<div class="comparison-section dropdown ratings">
		<button class="dropdown-toggle">Ratings</button>
		<div class="dropdown-content">
			<?php for ($i = 1; $i <= 2; $i++): 
				$software_id = ${"software{$i}_id"};
				$review_rating = get_field('review_rating', $software_id); // Assuming 'review_rating' is a group field ?>
				<div class="software software-<?php echo $i; ?>">
					<h4><?php echo get_field('software_name', $software_id); ?> Ratings</h4>
					<p>Overall Value: <?php echo render_stars($review_rating['overall_value']); ?></p>
					<p>Ease of Use: <?php echo render_stars($review_rating['ease_of_use']); ?></p>
					<p>Customer Service: <?php echo render_stars($review_rating['customer_service']); ?></p>
					<p>Value for Money: <?php echo render_stars($review_rating['value_for_money']); ?></p>
				</div>
			<?php endfor; ?>
		</div>
	</div>

	<!-- Pricing Packages Section -->
	<div class="comparison-section dropdown pricing">
		<button class="dropdown-toggle">Pricing Packages</button>
		<div class="dropdown-content">
			<?php for ($i = 1; $i <= 2; $i++): 
				$software_id = ${"software{$i}_id"};
				if(have_rows('pricing_packages', $software_id)): ?>
					<div class="software software-<?php echo $i; ?>">
						<h4><?php echo get_field('software_name', $software_id); ?> Pricing</h4>
						<?php while(have_rows('pricing_packages', $software_id)): the_row();
							$availability = get_sub_field('availability_selector');
							$package_name = get_sub_field('package_name');
							$pricing = get_sub_field('pricing');
							$currency = get_sub_field('currency');
							$monthly_or_yearly = get_sub_field('monthly_or_yearly');
							$trial = get_sub_field('trial');
							$free_version = get_sub_field('free_version');
						?>
							<div class="package">
								<p><strong><?php echo esc_html($package_name); ?></strong></p>
								<p>Starts from: <?php echo esc_html($pricing) . ' ' . esc_html($currency) . ' per ' . esc_html($monthly_or_yearly); ?></p>
								<p><?php echo esc_html($trial); ?></p>
								<p><?php echo esc_html($free_version); ?></p>
							</div>
						<?php endwhile; ?>
					</div>
				<?php endif;
			endfor; ?>
		</div>
	</div>

	<?php
	// Utility function to render check or X icons based on condition
	function render_icon($condition) {
		return $condition ? '<span class="icon check">✔</span>' : '<span class="icon cross">✖</span>';
	}

	// Define available choices for each field to match against selected options
	$implementation_choices = ['Web Based', 'Windows', 'Mac OS', 'Linux', 'Android', 'iOS'];
	$support_choices = ['Phone Support', 'Email/Help Desk', 'AI Chat Bot', 'Live Support', '24/7 Support', 'Forum & Community', 'Knowledge Base'];
	$training_choices = ['Live Online', 'Documentation', 'Videos', 'In Person', 'Webinars'];

	// Loop for each software
	for ($i = 1; $i <= 2; $i++): 
		$software_id = ${"software{$i}_id"};
		$offers = get_field('product_offers_to_consumers', $software_id); // Assuming this returns the group field
	?>
	
    <!-- Dropdown for Implementation -->
	<div class="comparison-section dropdown implementation">
		<button class="dropdown-toggle">Implementation</button>
		<div class="dropdown-content">
			<div class="comparison-columns">
				<?php for ($i = 1; $i <= 2; $i++): 
					$software_id = ${"software{$i}_id"};
					$offers = get_field('product_offers_to_consumers', $software_id); // Group field
				?>
					<div class="software-column software-<?php echo $i; ?>">
						<h4><?php echo get_field('software_name', $software_id); ?></h4>
						<?php foreach ($implementation_choices as $choice): ?>
							<p><?php echo $choice . ' ' . render_icon(in_array($choice, $offers['immplementation'])); ?></p>
						<?php endforeach; ?>
					</div>
				<?php endfor; ?>
			</div>
		</div>
	</div>

	<!-- Combined Dropdown for Support -->
	<div class="comparison-section dropdown support">
		<button class="dropdown-toggle">Support</button>
		<div class="dropdown-content">
			<div class="comparison-row">
				<?php for ($i = 1; $i <= 2; $i++): 
					$software_id = ${"software{$i}_id"};
					$offers = get_field('product_offers_to_consumers', $software_id); ?>
					<div class="software-column software-<?php echo $i; ?>">
						<h4><?php echo get_field('software_name', $software_id); ?> Support</h4>
						<?php foreach ($support_choices as $choice): ?>
							<p><?php echo $choice . ' ' . render_icon(in_array($choice, $offers['support'])); ?></p>
						<?php endforeach; ?>
					</div>
				<?php endfor; ?>
			</div>
		</div>
	</div>

	<!-- Combined Dropdown for Training -->
	<div class="comparison-section dropdown training">
		<button class="dropdown-toggle">Training</button>
		<div class="dropdown-content">
			<div class="comparison-row">
				<?php for ($i = 1; $i <= 2; $i++): 
					$software_id = ${"software{$i}_id"};
					$offers = get_field('product_offers_to_consumers', $software_id); ?>
					<div class="software-column software-<?php echo $i; ?>">
						<h4><?php echo get_field('software_name', $software_id); ?> Training</h4>
						<?php foreach ($training_choices as $choice): ?>
							<p><?php echo $choice . ' ' . render_icon(in_array($choice, $offers['training'])); ?></p>
						<?php endforeach; ?>
					</div>
				<?php endfor; ?>
			</div>
		</div>
	</div>

	<?php endfor; ?>
	

    <!-- Combined Dropdown for Pros and Cons -->
	<div class="comparison-section dropdown pros-cons">
		<button class="dropdown-toggle">Pros and Cons</button>
		<div class="dropdown-content">
			<div class="comparison-row">
				<?php for ($i = 1; $i <= 2; $i++): 
					$software_id = ${"software{$i}_id"}; ?>
					<div class="software-column software-<?php echo $i; ?>">
						<h4><?php echo get_field('software_name', $software_id); ?> Pros and Cons</h4>
						<div class="pros">
							<h5>Advantages</h5>
							<ul>
							<?php if (have_rows('pros_&_cons', $software_id)):
								while (have_rows('pros_&_cons', $software_id)): the_row();
									$advantages = get_sub_field('advantages'); ?>
									<li><?php echo esc_html($advantages); ?></li>
								<?php endwhile;
							endif; ?>
							</ul>
						</div>
						<div class="cons">
							<h5>Disadvantages</h5>
							<ul>
							<?php // Assuming the loop is reset or can continue to retrieve cons
								if (have_rows('pros_&_cons', $software_id)):
									while (have_rows('pros_&_cons', $software_id)): the_row();
										$disadvantages = get_sub_field('disadvantages'); ?>
										<li><?php echo esc_html($disadvantages); ?></li>
									<?php endwhile;
								endif; ?>
							</ul>
						</div>
					</div>
				<?php endfor; ?>
			</div>
		</div>
	</div>

</div>

<?php
// Additional PHP for handling specific display logic
?>


<?php
get_footer(); // Include footer
?>
