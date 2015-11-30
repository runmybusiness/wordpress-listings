<?php
add_filter('archive_template', 'archive_person_template');
function archive_person_template($template)
{
    global $wp_query;
    if (is_post_type_archive('rmb-person')) {
        $templates[] = 'archive-rmb-person.php';
        $template = rmb_locate_plugin_template($templates);
    }
    return $template;
}
