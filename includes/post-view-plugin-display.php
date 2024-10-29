<?php 
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

// Track post views by hooking into wp action
function pvs_track_post_views() {
    if (is_single()) {
        global $post, $wpdb;
        $table_name = $wpdb->prefix . 'post_views';
        $post_id = $post->ID;
        $view_date = current_time('mysql');

        $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'view_date' => $view_date
            )
        );
    }
}

add_action('wp', 'pvs_track_post_views');

// Add meta box to the post editing screen in wp-admin to display the post count for the last 14 days
function pvs_add_meta_box() {
    add_meta_box(
        'pvs_meta_box',
        'Post View Statistics',
        'pvs_meta_box_callback',
        'post',
        'side'
    );
}

add_action('add_meta_boxes', 'pvs_add_meta_box');

function pvs_meta_box_callback($post) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'post_views';
    $post_id = $post->ID;
    $date_14_days_ago = date('Y-m-d H:i:s', strtotime('-14 days'));

    // Total view count for the last 14 days
    $total_view_count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE post_id = %d AND view_date >= %s",
            $post_id,
            $date_14_days_ago
        )
    );

    // Daily view counts for the last 14 days
    $daily_view_counts = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT DATE(view_date) as view_date, COUNT(*) as view_count 
             FROM $table_name 
             WHERE post_id = %d AND view_date >= %s 
             GROUP BY DATE(view_date) 
             ORDER BY view_date DESC",
            $post_id,
            $date_14_days_ago
        )
    );

    // Prepare data for the chart
    $dates = [];
    $counts = [];
    foreach ($daily_view_counts as $day) {
        $dates[] = $day->view_date;
        $counts[] = $day->view_count;
    }

    echo '<h4>Total views in the last 14 days:</h4>';
    echo intval($total_view_count);
    echo '<h4>Daily Views:</h4>';
    echo '<ul>';
    foreach ($daily_view_counts as $day) {
        echo '<li>' . esc_html($day->view_date) . ': ' . intval($day->view_count) . ' views</li>';
    }
    echo '</ul>';

    echo '<canvas id="pvsChart" width="400" height="200"></canvas>';
    ?>
    <?php
    echo '<script id="pvsChartData" type="application/json">' . json_encode(array('dates' => array_reverse($dates), 'counts' => array_reverse($counts))) . '</script>';
}