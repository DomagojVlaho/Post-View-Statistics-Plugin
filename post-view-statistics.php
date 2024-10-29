<?php
/**
 * Plugin Name
 *
 * Plugin Name: Post View Statistics
 * Plugin URI:  https://github.com/DomagojVlaho/Post-View-Statistics-Plugin
 * Description: A plugin that shows the single post view count in the wp-admin backend
 * Version:     1.0.0
 * Author:      DomagojVlaho
 * Author URI:  https://github.com/DomagojVlaho
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Requires at least: 4.9
 * Requires PHP: 5.2.4
 *
 */

// Exit if accessed directly

if (!defined('ABSPATH')) {
    exit;
}


// Create a custom database table to store post views
function pvs_create_views_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'post_views';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        view_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        view_count int(11) NOT NULL DEFAULT 1,
        PRIMARY KEY  (id),
        UNIQUE KEY post_date (post_id, view_date)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'pvs_create_views_table');

function pvs_add_test_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'post_views';
    $post_id = 1; // Replace with the actual post ID you want to test with

    // Generate random view counts for the last 14 days
    for ($i = 0; $i < 14; $i++) {
        $view_date = date('Y-m-d H:i:s', strtotime("-$i days"));
        $view_count = rand(1, 100); // Random view count between 1 and 100

        for ($j = 0; $j < $view_count; $j++) {
            $wpdb->insert(
                $table_name,
                array(
                    'post_id' => $post_id,
                    'view_date' => $view_date
                )
            );
        }
    }
}

// Uncomment the line below to execute the function once
// pvs_add_test_data();

// Include necessary files and functions
// Plugin settings page
include(plugin_dir_path(__FILE__) . 'includes/post-view-plugin-settings.php');
// Plugin display page
include(plugin_dir_path(__FILE__) . 'includes/post-view-plugin-display.php');

// Enqueue styles and script for the plugin frontend
function post_view_enqueue_assets() {
    // Enqueue the CSS file
    wp_enqueue_style('post-view-styles', plugin_dir_url(__FILE__) . 'public/css/styles.css', array(), '1.0.0');

    // Enqueue Chart.js from CDN
    wp_enqueue_script('chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js', array(), '4.4.1', true);

    // Enqueue the JavaScript file
    wp_enqueue_script('post-view-scripts', plugin_dir_url(__FILE__) . 'public/js/frontend.bundle.js', array('jquery', 'chart-js'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'post_view_enqueue_assets');

// Enqueue styles and script for the plugin backend
function post_view_enqueue_admin_assets($hook) {
    // Get the current screen
    $screen = get_current_screen();

    // Check if we are on the post edit screen to avoid loading scripts unnecessarily
    if ($screen->base === 'post' && $screen->post_type === 'post') {
        // Enqueue Chart.js from CDN
        // wp_enqueue_script('chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js', array(), '4.4.1', true);
        
        // Enqueue admin CSS
        wp_enqueue_style('post-view-admin-styles', plugin_dir_url(__FILE__) . 'public/css/admin-styles.css', array(), '1.0.0');

        // Enqueue admin JavaScript
        wp_enqueue_script('post-view-admin-scripts', plugin_dir_url(__FILE__) . 'public/js/backend.bundle.js', array('jquery'), '1.0.0', true);
    }

    // Check if we are on the plugin's settings page to avoid loading scripts unnecessarily
    if ($hook !== 'settings_page_post-view-plugin-settings') {
        return;
    }

    // Enqueue admin CSS
    wp_enqueue_style('post-view-admin-styles', plugin_dir_url(__FILE__) . 'public/css/admin-styles.css', array(), '1.0.0');

    // Enqueue admin JavaScript
    wp_enqueue_script('post-view-admin-scripts', plugin_dir_url(__FILE__) . 'public/js/backend.bundle.js', array('jquery'), '1.0.0', true);
}
add_action('admin_enqueue_scripts', 'post_view_enqueue_admin_assets');