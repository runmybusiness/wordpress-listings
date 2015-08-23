<?php


$runmybusiness_datastring = file_get_contents($runmybusiness_listing_url, false, $context);
$runmybusiness_data = json_decode($runmybusiness_datastring);
if (!empty($runmybusiness_data->data)) {
    $posts_runmybusiness = array();
    $querystr = "SELECT `post_id`, `meta_value` FROM $wpdb->postmeta WHERE `meta_key` = 'runmybusiness_listing_id' AND `meta_value` > 0";
    $existing_posts = $wpdb->get_results($querystr, 'ARRAY_A');
    foreach ($existing_posts as $p) {
        $posts_runmybusiness[$p['post_id']] = $p['meta_value'];
    }
    $runmybusiness_ids = array();
    foreach ($runmybusiness_data->data as $item) {
        $runmybusiness_ids[] = $item->id;
        // Check if post already exists and update it
        $querystr = "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key` = 'runmybusiness_listing_id' AND `meta_value` = '$item->id'";
        $post = $wpdb->get_row($querystr);
        $address = '';
        foreach ($item->property->data->fields->data as $k) {
            if (isset($k->field->legacy_id) && $k->field->legacy_id == 'address') {
                $address = $k->value;
            }
        }
        $square_foot = 0;
        foreach ($item->property->data->fields->data as $k) {
            if (isset($k->field->legacy_id) && $k->field->legacy_id == 'square_foot') {
                $square_foot = (int)str_replace(',', '', $k->value);
            }
        }
        $price = '';

        foreach ($item->fields->data as $k) {
            if (isset($k->field->legacy_id) && $k->field->legacy_id == 'price') {
                $price = str_replace('$', '', $k->value);
                if ($price == "Best Offer") {
                    // $price = '1';
                }
                else {
                    // Edit the input to match database required values (its input is something like: 49,900,000, so we make it: 49900.000)
                    $parts = explode(',', $price);
                    $str = '';
                    foreach ($parts as $key => $val) {
                        if ($key < count($parts) - 1) {
                            $str .= $val;
                        }
                        else {
                            $str .= '.' . $val;
                        }
                    }
                    $price = $str;
                }
            }
        }

        if ($post) {
            if (!empty($address))
                update_post_meta($post->post_id, 'location', $address);
            if (!empty($square_foot))
                update_post_meta($post->post_id, 'square_footage', $square_foot);
            if (!empty($price))
                update_post_meta($post->post_id, 'price', $price);
            if (!empty($item->property->data->type->name))
                update_post_meta($post->post_id, 'property_type', $item->property->data->type->name);
            if (!empty($item->transaction_type->name))
                update_post_meta($post->post_id, 'transaction_type', $item->transaction_type->name);
            if (!empty($item->status->friendly))
                update_post_meta($post->post_id, 'status', $item->status->friendly);
            update_post_meta($post->post_id, 'runmybusiness_datastring', str_replace(array('\"', '\/'), array('\\\\"', '\\\\/'), json_encode($item)));
        }
        else
        {
            // Else we create a new one
            $new_post = array(
                'post_title' => $item->property->data->name,
                'post_content' => '',
                'post_status' => 'publish',
                'post_author' => $user_ID,
                'post_type' => 'rmb-listing'
            );
            $post_id = wp_insert_post($new_post);
            add_post_meta($post_id, 'runmybusiness_listing_id ', $item->id);
            if (!empty($address))
                add_post_meta($post_id, 'location', $address);
            if (!empty($square_foot))
                add_post_meta($post_id, 'square_footage', $square_foot);
            if (!empty($price))
                add_post_meta($post_id, 'price', $price);
            if (!empty($item->property->data->type->name))
                add_post_meta($post_id, 'property_type', $item->property->data->type->name);
            if (!empty($item->transaction_type->name))
                add_post_meta($post_id, 'transaction_type', $item->transaction_type->name);
            if (!empty($item->status->friendly))
                add_post_meta($post_id, 'status', $item->status->friendly);
            add_post_meta($post_id, 'runmybusiness_datastring', str_replace(array('\"', '\/'), array('\\\\"', '\\\\/'), json_encode($item)));
        }
    }
    // Delete the posts that are not present in runmybusiness anymore
    $post_to_delete = array_diff($posts_runmybusiness, $runmybusiness_ids);
    foreach ($post_to_delete as $post_id => $runmybusiness_listing_id) {
        wp_delete_post($post_id);
    }
}
