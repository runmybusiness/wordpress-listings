<?php

if (! function_exists('runmybusiness_do_update_content')) {
    // Register new action to run on cron execution
    add_action('runmybusiness_update_content', 'runmybusiness_do_update_content');
    function runmybusiness_do_update_content()
    {
        global $wpdb;
        global $user_ID;
        $runmybusiness_options = get_option('runmybusiness_options');

        $context = stream_context_create([
            'http' => [
                'header' => 'Authorization: Basic ' . base64_encode($runmybusiness_options['runmybusiness_username'] . ':' . $runmybusiness_options['runmybusiness_password']),
            ],
        ]);

        $runmybusiness_listing_url = isset($runmybusiness_options['runmybusiness_listing_url']) ? $runmybusiness_options['runmybusiness_listing_url'] : '';
        if ($runmybusiness_listing_url) {
            require_once plugin_dir_path(__FILE__) . '/sync-listings.php';
        }

        $runmybusiness_people_url = isset($runmybusiness_options['runmybusiness_people_url']) ? $runmybusiness_options['runmybusiness_people_url'] : '';
        if ($runmybusiness_people_url) {
            require_once plugin_dir_path(__FILE__) . '/sync-people.php';
        }

        $runmybusiness_comparables_url = isset($runmybusiness_options['runmybusiness_comparables_url']) ? $runmybusiness_options['runmybusiness_comparables_url'] : '';
        if ($runmybusiness_comparables_url) {
            require_once plugin_dir_path(__FILE__) . '/sync-comparables.php';
        }

        $hooks_path = get_template_directory() . '/runmybusiness_hooks.php';
        if (file_exists($hooks_path)) {
            include($hooks_path);

            rmb_post_sync_hook();
        }

        $runmybusiness_rates_url = isset($runmybusiness_options['runmybusiness_rates_url']) ? $runmybusiness_options['runmybusiness_rates_url'] : '';
        if ($runmybusiness_rates_url) {
            require_once plugin_dir_path(__FILE__) . '../rates/update-rates.php';
        }
    }
}
