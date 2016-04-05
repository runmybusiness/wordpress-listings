<?php

/*
Plugin Name: RunMyBusiness
Plugin URI: https://wordpress.org/plugins/runmybusiness-listings/
Version: 1.0.27
Description: This plugin imports data from RunMyBusiness.
*/

include('functions.php');

// Add custom post types
add_action('init', 'create_post_types');
function create_post_types()
{
    $options = get_option('runmybusiness_options');

    register_post_type('rmb-listing',
        [
            'labels'       => [
                'name'          => __('Listings'),
                'singular_name' => __('Listing'),
                'add_new_item'  => __('Add New Listing'),
                'edit_item'     => __('Edit Listing'),
            ],
            //'show_ui' => false,
            'public'       => true,
            'has_archive'  => true,
            'show_in_rest' => true,
            'rest_base'    => 'rmb-listing',
            'rewrite'      => [
                'slug' => array_get($options, 'runmybusiness_slug_listings', 'rmb-listings'),
            ],
        ]
    );

    register_post_type('rmb-person',
        [
            'labels'       => [
                'name'          => __('People'),
                'singular_name' => __('Person'),
                'add_new_item'  => __('Add New Person'),
                'edit_item'     => __('Edit Person'),
            ],
            'show_ui'      => false,
            'public'       => false,
            'has_archive'  => true,
            'show_in_rest' => true,
            'rest_base'    => 'rmb-person',
            'rewrite'      => [
                'slug' => array_get($options, 'runmybusiness_slug_people', 'rmb-people'),
            ],
        ]
    );
}

//add_action('init', 'add_rmb_taxonomies', 0);
//function add_rmb_taxonomies()
//{
//    register_taxonomy(
//        'rmb-listing_transaction_type',
//        'rmb-listing',
//        [
//            'labels'        => [
//                'name' => 'Transaction Type',
//            ],
//            'show_ui'       => false,
//            'show_tagcloud' => false,
//            'hierarchical'  => true,
//        ]
//    );
//
//    register_taxonomy(
//        'rmb-listing_status',
//        'rmb-listing',
//        [
//            'labels'        => [
//                'name' => 'Market Status',
//            ],
//            'show_ui'       => false,
//            'show_tagcloud' => false,
//            'hierarchical'  => true,
//        ]
//    );
//
//    register_taxonomy(
//        'rmb-listing_property_type',
//        'rmb-listing',
//        [
//            'labels'        => [
//                'name' => 'Property Type',
//            ],
//            'show_ui'       => false,
//            'show_tagcloud' => false,
//            'hierarchical'  => true,
//        ]
//    );
//}

function add_rmb_styles()
{
    wp_register_style('runmybusiness', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_style('runmybusiness');
}

add_action('init', 'add_rmb_styles');

require_once plugin_dir_path(__FILE__) . 'admin/admin_functions.php';

function runmybusiness_section_text()
{
}

require_once plugin_dir_path(__FILE__) . 'includes/sync-all.php';

// Validating options
function runmybusiness_validation($input)
{
    // Schedule runmybusiness cron
    // Schedule the event
    $options = get_option('runmybusiness_options');
    $recurrence = isset($options['runmybusiness_recurrence']) ? $options['runmybusiness_recurrence'] : '';
    if (! empty($recurrence)) {
        wp_unschedule_event(time(), 'runmybusiness_update_content');
        wp_schedule_event(time(), $recurrence, 'runmybusiness_update_content');
    } else {
        wp_unschedule_event(time(), 'runmybusiness_update_content');
    }

    return $input;
}

require_once plugin_dir_path(__FILE__) . 'includes/shortcode-property-filter.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcode-people-list.php';

function rmb_locate_plugin_template($template_names, $load = false, $require_once = true)
{
    if (! is_array($template_names)) {
        return '';
    }
    $located = '';

    $this_plugin_dir = WP_PLUGIN_DIR . '/' . str_replace(basename(__FILE__), '', plugin_basename(__FILE__));
    foreach ($template_names as $template_name) {
        if (! $template_name) {
            continue;
        }
        if (file_exists(STYLESHEETPATH . '/' . $template_name)) {
            $located = STYLESHEETPATH . '/' . $template_name;
            break;
        } elseif (file_exists(TEMPLATEPATH . '/' . $template_name)) {
            $located = TEMPLATEPATH . '/' . $template_name;
            break;
        } elseif (file_exists($this_plugin_dir . $template_name)) {
            $located = $this_plugin_dir . 'templates/' . $template_name;
            break;
        }
    }

    if ($load && $located != '') {
        load_template($located, $require_once);
    }

    return $located;
}

function register_session()
{
    if (! session_id()) {
        session_start();
    }
}

add_action('init', 'register_session');

function add_custom_query_vars($vars)
{
    $vars[] = "sort_by";
    $vars[] = "sort_direction";
    $vars[] = "property_type";
    return $vars;
}
add_filter('query_vars', 'add_custom_query_vars');

function custom_pagination($numpages = '', $pagerange = '', $paged = '')
{
    if (empty($pagerange)) {
        $pagerange = 2;
    }
    global $paged;
    if (empty($paged)) {
        $paged = 1;
    }
    if ($numpages == '') {
        global $wp_query;
        $numpages = $wp_query->max_num_pages;
        if (! $numpages) {
            $numpages = 1;
        }
    }

    $pagination_args = [
        'base'         => get_pagenum_link(1) . '%_%',
        'format'       => 'page/%#%',
        'total'        => $numpages,
        'current'      => $paged,
        'show_all'     => false,
        'end_size'     => 1,
        'mid_size'     => $pagerange,
        'prev_next'    => true,
        'prev_text'    => __('&laquo;'),
        'next_text'    => __('&raquo;'),
        'type'         => 'plain',
        'add_args'     => false,
        'add_fragment' => '',
    ];

    $paginate_links = paginate_links($pagination_args);

    if ($paginate_links) {
        echo "<nav class='custom-pagination'>";
        echo $paginate_links;
        echo '</nav>';
    }
}
