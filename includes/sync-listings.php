<?php

$runmybusiness_datastring = file_get_contents($runmybusiness_listing_url, false, $context);
$runmybusiness_data = json_decode($runmybusiness_datastring);

if (! empty($runmybusiness_data->data)) {
    $posts_runmybusiness = [];
    $querystr = "SELECT `post_id`, `meta_value` FROM $wpdb->postmeta WHERE `meta_key` = 'runmybusiness_listing_id' AND `meta_value` > 0";
    $existing_posts = $wpdb->get_results($querystr, 'ARRAY_A');
    foreach ($existing_posts as $p) {
        $posts_runmybusiness[$p['post_id']] = $p['meta_value'];
    }
    $runmybusiness_ids = [];
    foreach ($runmybusiness_data->data as $item) {
        $runmybusiness_ids[] = $item->id;
        // Check if post already exists and update it
        $querystr = "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key` = 'runmybusiness_listing_id' AND `meta_value` = '$item->id'";
        $found = $wpdb->get_row($querystr);

        if ($found) {
            $update_post = [
                'ID'           => $found->post_id,
                'post_title'   => $item->name,
                'post_content' => str_replace("\r\n", "<br>", $item->overview),
            ];

            $post_id = wp_update_post($update_post);
            $postMetaFunction = 'update_post_meta';
            echo "{$post_id}";
        } else {
            // Else we create a new one
            $new_post = [
                'post_title'   => $item->name,
                'post_content' => str_replace("\r\n", "<br>", $item->overview),
                'post_status'  => 'publish',
                'post_type'    => 'rmb-listing',
            ];
            $post_id = wp_insert_post($new_post);
            $postMetaFunction = 'add_post_meta';
        }

        if (! empty($item->id)) {
            $postMetaFunction($post_id, 'runmybusiness_listing_id', $item->id);
        }
        if (! empty($item->position)) {
            $postMetaFunction($post_id, 'position', $item->position);
        }
        if (! empty($item->name)) {
            $postMetaFunction($post_id, 'listing_name', $item->name);
        }
        if (! empty($item->property->address->geolookup->street->full)) {
            $postMetaFunction($post_id, 'location', $item->property->address->geolookup->street->full);
        }
        if (! empty($item->property->address->geolookup->province->name)) {
            $postMetaFunction($post_id, 'state', $item->property->address->geolookup->province->short_name);
        }
        if (! empty($item->property->building_size)) {
            $postMetaFunction($post_id, 'square_footage', $item->property->building_size);
        }
        if (! empty($item->price->min)) {
            $postMetaFunction($post_id, 'price', $item->price->min);
        }
        if (! empty($item->cap_rate)) {
            $postMetaFunction($post_id, 'cap_rate', $item->cap_rate);
        }
        if (! empty($item->property->data->type->name)) {
            $postMetaFunction($post_id, 'property_type', $item->property->type->name);
        }
        if (! empty($item->transaction_type->name)) {
            $postMetaFunction($post_id, 'transaction_type', $item->transaction_type->name);
        }
        if (! empty($item->property->tenancy->name)) {
            $postMetaFunction($post_id, 'tenancy', $item->property->tenancy->name);
        }
        if (! empty($item->status->friendly)) {
            $postMetaFunction($post_id, 'status', $item->status->friendly);
        }

        $postMetaFunction($post_id, 'runmybusiness_datastring', base64_encode(json_encode($item)));

        echo "\n Updated/Inserted {$item->name}\n";
    }
    // Delete the posts that are not present in runmybusiness anymore
    $post_to_delete = array_diff($posts_runmybusiness, $runmybusiness_ids);
    foreach ($post_to_delete as $post_id => $runmybusiness_listing_id) {
        wp_delete_post($post_id);
    }
}
