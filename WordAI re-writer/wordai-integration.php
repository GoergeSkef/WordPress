<?php
/**
 * Plugin Name: WordAi Integration
 * Description: A plugin to integrate WordAi for post rewriting and AI avoidance.
 * Version: 1.0
 * Author: Your Name
 */

// Enqueue scripts and styles
function wordai_enqueue_assets() {
    wp_enqueue_script('wordai-js', plugin_dir_url(__FILE__) . 'assets/js/wordai.js');
    wp_enqueue_style('wordai-css', plugin_dir_url(__FILE__) . 'assets/css/wordai.css');
    wp_localize_script('wordai-js', 'wordai_vars', array('nonce' => wp_create_nonce('wordai-nonce')));
}
add_action('admin_enqueue_scripts', 'wordai_enqueue_assets');

// Register the Metabox
function wordai_add_metabox() {
    add_meta_box(
        'wordai_metabox',
        'WordAi Options',
        'wordai_metabox_content',
        'post',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'wordai_add_metabox');

// Metabox Content
function wordai_metabox_content($post) {
    // Nonce field for security
    wp_nonce_field(basename(__FILE__), 'wordai_nonce');

    echo '<label for="wordai_mode">Mode:</label>';
    echo '<select name="wordai_mode" id="wordai_mode">';
    echo '<option value="rewrite">Rewrite</option>';
    echo '<option value="avoid">Avoid AI Detection</option>';
    echo '</select>';

    echo '<label for="wordai_uniqueness">Uniqueness:</label>';
    echo '<select name="wordai_uniqueness" id="wordai_uniqueness">';
    echo '<option value="1">Conservative</option>';
    echo '<option value="2">Regular</option>';
    echo '<option value="3">Adventurous</option>';
    echo '</select>';

    echo '<label for="wordai_protected_words">Protected Words (comma separated):</label>';
    echo '<input type="text" name="wordai_protected_words" id="wordai_protected_words" value="" />';

    echo '<label for="wordai_custom_synonyms">Custom Synonyms (format: word1|synonym1,word2|synonym2):</label>';
    echo '<input type="text" name="wordai_custom_synonyms" id="wordai_custom_synonyms" value="" />';

    echo '<button type="button" id="wordai_process">Process with WordAi</button>';
}

// Add a function to display the content as an admin notice
function wordai_display_debug_content() {
    $debug_content = get_transient('wordai_debug_content');
    if ($debug_content) {
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>WordAi Debug Content:</strong></p>';
        echo '<p>' . esc_html($debug_content) . '</p>';
        echo '</div>';
    }
}
add_action('admin_notices', 'wordai_display_debug_content');


// AJAX handler for WordAi processing
function process_wordai() {
    check_ajax_referer('wordai-nonce', 'nonce');

    // WordAi API endpoint and credentials
    $apiUrl = 'https://wai.wordai.com/api/';
    $email = 'name@email.com';
    $apiKey = 'your-api-key';

    $mode = sanitize_text_field($_POST['mode']);
    $uniqueness = sanitize_text_field($_POST['uniqueness']);
    $post_id = intval($_POST['post_id']);
    $protected_words = sanitize_text_field($_POST['protected_words']);
    $custom_synonyms = sanitize_text_field($_POST['custom_synonyms']);

    $post_content = get_post_field('post_content', $post_id);
    set_transient('wordai_debug_content', $post_content, 60); // Store for 60 seconds

    $apiEndpoint = $mode === 'rewrite' ? 'rewrite' : 'avoid';
    $bodyArgs = array(
        'email' => $email,
        'key' => $apiKey,
        'input' => $post_content
    );

    if ($mode === 'rewrite') {
        $bodyArgs['rewrite_num'] = 1; // Default to 1 rewrite
        $bodyArgs['uniqueness'] = $uniqueness;
    } else {
        $bodyArgs['mode'] = $uniqueness === '1' ? 'change_less' : 'change_more';
    }
    
    
    $response = wp_remote_post($apiUrl . $apiEndpoint, array('body' => $bodyArgs));

    error_log(print_r($bodyArgs, true)); // Log the parameters
    
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if ($data['status'] === 'Success') {
        // Update the post content
        wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $data['text']
        ));
        wp_send_json_success(array('message' => 'Post updated successfully!'));
    } else {
        // Return the entire API response for debugging
        $errorMessage = isset($data['error']) ? $data['error'] : 'Unknown error';
        wp_send_json_error(array('message' => 'Error processing with WordAi. Response: ' . $errorMessage));
    }
}

add_action('wp_ajax_process_wordai', 'process_wordai');




?>
