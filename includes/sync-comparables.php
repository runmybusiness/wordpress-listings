<?php

$runmybusiness_datastring = file_get_contents($runmybusiness_comparables_url, false, $context);
$runmybusiness_data = json_decode($runmybusiness_datastring);
if (! empty($runmybusiness_data->data)) {
    $posts_runmybusiness = [];
    $querystr = "SELECT `post_id`, `meta_value` FROM $wpdb->postmeta WHERE `meta_key` = 'runmybusiness_comparable_id' AND `meta_value` > 0";
    $existing_posts = $wpdb->get_results($querystr, 'ARRAY_A');
    foreach ($existing_posts as $p) {
        $posts_runmybusiness[$p['post_id']] = $p['meta_value'];
    }
    $runmybusiness_ids = [];
    foreach ($runmybusiness_data->data as $item) {
        $runmybusiness_ids[] = $item->id;
        // Check if post already exists and update it
        $querystr = "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key` = 'runmybusiness_comparable_id' AND `meta_value` = '$item->id'";
        $post = $wpdb->get_row($querystr);

        if ($post) {
            update_post_meta($post->post_id, 'runmybusiness_datastring',
                str_replace(['\"', '\/'], ['\\\\"', '\\\\/'], json_encode($item)));
        } else {
            // Else we create a new one
            $new_post = [
                'post_title'   => $item->property->name,
                'post_content' => '',
                'post_status'  => 'publish',
                'post_author'  => $user_ID,
                'post_type'    => 'rmb-comparable',
            ];
            $post_id = wp_insert_post($new_post);

            add_post_meta($post_id, 'runmybusiness_comparable_id ', $item->id);
            add_post_meta($post_id, 'runmybusiness_datastring', json_encode($item));
        }
    }
    // Delete the posts that are not present in runmybusiness anymore
    $post_to_delete = array_diff($posts_runmybusiness, $runmybusiness_ids);
    foreach ($post_to_delete as $post_id => $runmybusiness_comparable_id) {
        wp_delete_post($post_id);
    }
}
