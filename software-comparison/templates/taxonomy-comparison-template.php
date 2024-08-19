<?php
get_header();

// Your existing template logic...

// Modify the document title directly for this template
$taxonomy_name = get_query_var('taxonomy_name');
$software1 = get_query_var('software1');
$software2 = get_query_var('software2');

if ($software1 && $software2) {
    $title = esc_html($software1) . ' vs ' . esc_html($software2) . ' Comparison';
} elseif ($taxonomy_name) {
    $title = 'Software Comparison for ' . esc_html($taxonomy_name) . ' Solutions';
} else {
    $title = 'Default Title for Non-specific Views'; // Fallback title
}

// Now, directly modify the title tag
echo "<title>" . $title . "</title>";

?>


<?php
// Get the current taxonomy name from the query variable
$taxonomy_name = get_query_var('taxonomy_name');
$taxonomy_term = get_term_by('slug', $taxonomy_name, 'software-category');

if ($taxonomy_term):
    // ACF Fields for the taxonomy
    $name = get_field('taxonomy_name', $taxonomy_term);
    $introduction_group = get_field('comparison_intro_content', $taxonomy_term);
    $content_repeater = get_field('comparison_page_content', $taxonomy_term);
    $trending_alternatives_intro = get_field('trending_alternatives_introduction', $taxonomy_term);
    $trending_alternatives = get_field('trending_alternatives', $taxonomy_term);
    $faq = get_field('comparison_faq', $taxonomy_term);
?>
    <section class="taxonomy-comparison">
		
        <?php if ($introduction_group): ?>
            <div class="introduction">
                <h2><?php echo esc_html($introduction_group['title']); ?></h2>
                <p><?php echo esc_html($introduction_group['content']); ?></p>
            </div>
        <?php endif; ?>
		<?php echo do_shortcode('[software-comparison]'); ?>

        <!-- Place your software comparison tool here -->
		
        <?php if ($content_repeater): ?>
            <div class="content-section">
                <?php foreach ($content_repeater as $content): ?>
                    <?php $tag = esc_html($content['title_hierarchy']); ?>
                    <<?php echo $tag; ?>><?php echo esc_html($content['header']); ?></<?php echo $tag; ?>>
                    <?php echo format_content_according_to_type($content['text'], $content['content_type']); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($trending_alternatives_intro): ?>
            <div class="trending-alternatives">
                <h2><?php echo esc_html($trending_alternatives_intro['title']); ?></h2>
                <p><?php echo esc_html($trending_alternatives_intro['content']); ?></p>

                <?php if ($trending_alternatives): ?>
                    <ul>
                        <?php foreach ($trending_alternatives as $post): ?>
                            <li><a href="<?php echo get_permalink($post->ID); ?>"><?php echo get_the_title($post->ID); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <h2>Best rated Software for <?php echo esc_html($name);?></h2>
        <div id="software-grid" class="software-ajax-container"></div>
        <?php if ($faq): ?>
		  <?php foreach ($faq as $faq_item): ?>
				
			<div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                <button class="accordion" itemprop="name"><?php echo esc_html($faq_item['question']); ?></button>
                <div class="panel" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                    <div itemprop="text">
                        <p><?php echo esc_html($faq_item['answer']); ?></p>
                    </div>
                </div>
            </div>
				
          <?php endforeach; ?>
        <?php endif; ?>
				
    </section>

<?php
endif;
get_footer();

// Helper function to format content based on content_type
function format_content_according_to_type($text, $content_type) {
    switch ($content_type) {
        case 'Paragraph':
            return '<p>' . wpautop($text) . '</p>';
        case 'bullet_points':
            // Assume $text contains a comma-separated list of items
            $items = explode(',', $text);
            $html = '<ul>';
            foreach ($items as $item) {
                $html .= '<li>' . esc_html(trim($item)) . '</li>';
            }
            $html .= '</ul>';
            return $html;
        case 'quote':
            return '<blockquote>' . esc_html($text) . '</blockquote>';
        case 'highlight':
            return '<mark>' . esc_html($text) . '</mark>';
        case 'bold':
            return '<strong>' . esc_html($text) . '</strong>';
        default:
            return esc_html($text);
    }
}



function custom_title_change($title_parts) {
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
add_filter('document_title_parts', 'custom_title_change');