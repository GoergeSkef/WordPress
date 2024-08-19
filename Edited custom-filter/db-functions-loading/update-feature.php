<?php

function create_generic_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'generic_table';

    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            feature_name varchar(255) NOT NULL,
            occurrence_count mediumint(9) NOT NULL,
            post_ids text NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
add_action('init', 'create_generic_table');




function get_post_features($post_id) {
    $features = [];

    if (have_rows('features_&_benefits', $post_id)) {
        while (have_rows('features_&_benefits', $post_id)) {
            the_row();
            
            // Extract and normalize features from each column
            $column_1 = strtolower(trim(get_sub_field('column_1')));
            $column_2 = strtolower(trim(get_sub_field('column_2')));
            $column_3 = strtolower(trim(get_sub_field('column_3')));
            
            // Add features to the array if not empty
            if (!empty($column_1)) {
                $features[$column_1] = true; // Use associative array to avoid duplicates
            }
            if (!empty($column_2)) {
                $features[$column_2] = true;
            }
            if (!empty($column_3)) {
                $features[$column_3] = true;
            }
        }
    } else {
        error_log("Failed to retrieve rows for features_&_benefits from post ID: $post_id");
    }

    return array_keys($features); // Return the unique list of features
}

function update_feature_summary_on_save($post_id, $post, $update) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'generic_table';
    $post_type = get_post_type($post_id);

    if ('saas-reviews' !== $post_type || wp_is_post_revision($post_id)) {
        error_log("update_feature_summary_on_save: Skipping due to incorrect post type or revision for post ID $post_id.");
        return;
    }

    $current_features = get_post_meta($post_id, '_associated_features', true) ?: [];
    $new_features = get_post_features($post_id);

    $current_features = array_map('strtolower', (array) maybe_unserialize($current_features));
    $new_features = array_map('strtolower', $new_features);

    $features_to_add = array_diff($new_features, $current_features);
    $features_to_remove = array_diff($current_features, $new_features);

    foreach ($features_to_add as $feature) {
        $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE feature_name = %s", $feature));
        if ($existing) {
            $post_ids = maybe_unserialize($existing->post_ids) ?: [];
            if (!in_array($post_id, $post_ids)) {
                $post_ids[] = $post_id;
                $result = $wpdb->update(
                    $table_name,
                    ['occurrence_count' => count($post_ids), 'post_ids' => maybe_serialize($post_ids)],
                    ['id' => $existing->id]
                );
                if (false === $result) {
                    error_log("update_feature_summary_on_save: Error updating feature '$feature' with post ID $post_id.");
                }
            }
        } else {
            $result = $wpdb->insert(
                $table_name,
                ['feature_name' => $feature, 'occurrence_count' => 1, 'post_ids' => maybe_serialize([$post_id])],
                ['%s', '%d', '%s']
            );
            if (false === $result) {
                error_log("update_feature_summary_on_save: Error inserting new feature '$feature' for post ID $post_id.");
            }
        }
    }

    foreach ($features_to_remove as $feature) {
        $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE feature_name = %s", $feature));
        if ($existing) {
            $post_ids = maybe_unserialize($existing->post_ids) ?: [];
            $post_ids = array_diff($post_ids, [$post_id]); // Remove the post ID
            $result = $wpdb->update(
                $table_name,
                ['occurrence_count' => count($post_ids), 'post_ids' => maybe_serialize($post_ids)],
                ['id' => $existing->id]
            );
            if (false === $result) {
                error_log("update_feature_summary_on_save: Error removing post ID $post_id from feature '$feature'.");
            }
        }
    }

    $result = update_post_meta($post_id, '_associated_features', maybe_serialize($new_features));
    if (false === $result) {
        error_log("update_feature_summary_on_save: Error updating '_associated_features' post meta for post ID $post_id.");
    }
}

add_action('save_post', 'update_feature_summary_on_save', 10, 3);


function recount_features_from_posts() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'generic_table';

    // Reset feature summary table to rebuild from scratch
    $wpdb->query("TRUNCATE TABLE {$table_name}");

    // Fetch all posts of the custom post type 'saas-reviews'
    $all_posts = get_posts([
        'post_type' => 'saas-reviews',
        'posts_per_page' => -1,
        'suppress_filters' => false
    ]);

    if (empty($all_posts)) {
        error_log("recount_features_from_posts: No 'saas-reviews' posts found.");
        return;
    }

    // Temporary storage for features and their associated posts
    $feature_posts_map = [];

    foreach ($all_posts as $post) {
        $post_features = get_post_features($post->ID); // Extract and normalize features from a post

        if (empty($post_features)) {
            error_log("recount_features_from_posts: No features found for post ID {$post->ID}.");
            continue;
        }

        foreach ($post_features as $feature) {
            $feature = strtolower($feature);
            if (!array_key_exists($feature, $feature_posts_map)) {
                $feature_posts_map[$feature] = [];
            }
            if (!in_array($post->ID, $feature_posts_map[$feature])) {
                $feature_posts_map[$feature][] = $post->ID;
            }
        }
    }

    // Update the database with the new counts and post IDs for each feature
    foreach ($feature_posts_map as $feature => $post_ids) {
        $result = $wpdb->insert(
            $table_name,
            [
                'feature_name' => $feature,
                'occurrence_count' => count($post_ids),
                'post_ids' => maybe_serialize($post_ids)
            ],
            ['%s', '%d', '%s']
        );

        if (false === $result) {
            error_log("recount_features_from_posts: Failed to insert or update feature '{$feature}' with post IDs.");
        }
    }
}




if (!wp_next_scheduled('update_feature_counts_daily')) {
    wp_schedule_event(time(), 'daily', 'update_feature_counts_daily');
}

add_action('update_feature_counts_daily', 'recount_features_from_posts');
// wp_clear_scheduled_hook('update_feature_counts_daily');


?>