<?php

add_filter('single_template', 'single_listing_template');
function single_listing_template($template)
{
    global $wp_query, $post;
    /* Checks for single template by post type */
    if ($post->post_type == 'rmb-listing') {
        $templates[] = 'single-rmb-listing.php';
        $template = rmb_locate_plugin_template($templates);
    }

    return $template;
}
