<?php

add_filter('archive_template', 'archive_listing_template');

function archive_listing_template($template)
{
    global $wp_query;

    if (is_post_type_archive('rmb-listing')) {
        $templates[] = 'archive-rmb-listing.php';
        $template = rmb_locate_plugin_template($templates);
    }

    return $template;
}
