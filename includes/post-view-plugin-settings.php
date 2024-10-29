<?php 
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Register the settings page in the WordPress admin

function post_view_add_settings_page() {
    add_options_page(
        'Post View Plugin Settings',    // Page title displayed on page in admin
        'Post View Plugin',             // Menu title in admin
        'manage_options',          // Capability required to access the page
        'post-view-plugin-settings',     // Menu slug in admin for plugin settings page
        'post_view_render_settings_page' // Callback function to render the page
    );
}

add_action('admin_menu', 'post_view_add_settings_page');

// Callback function to render the plugin settings page

function post_view_render_settings_page(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'post_views';
    $date_14_days_ago = date('Y-m-d H:i:s', strtotime('-14 days'));

     // Daily view counts for all posts
     $daily_view_counts = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT DATE(view_date) as view_date, SUM(view_count) as total_views 
            FROM $table_name 
            WHERE view_date >= %s 
            GROUP BY DATE(view_date) 
            ORDER BY view_date DESC",
            $date_14_days_ago
        )
    );

    // Prepare data for the chart
    $dates = [];
    $counts = [];
    foreach ($daily_view_counts as $day) {
        $dates[] = $day->view_date;
        $counts[] = $day->total_views;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Post View Plugin Settings', 'post-view'); ?></h1>
        <p><?php echo esc_html__('This is the settings page for Plugin Name.', 'post-view'); ?></p>
        <div>
            <h2><?php echo esc_html__('Total post views for the last 14 days', 'post-view'); ?></h2>
            <p><?php echo esc_html__('The chart shows the total sum of all post views for the last 14 days.', 'post-view'); ?></p>
            <canvas id="pvsChart" width="400" height="200"></canvas>
            <?php echo '<script id="pvsChartData" type="application/json">' . json_encode(array('dates' => array_reverse($dates), 'counts' => array_reverse($counts))) . '</script>'; ?>
        </div>
        <div>
            <h2><?php echo esc_html__('Plugin screenshots', 'post-view'); ?></h2>
            <p><?php echo esc_html__('Open up your single post editing screen.', 'post-view'); ?></p>
            <p><?php echo esc_html__('The plugin will automatically create a database and track your post view counts. The post count statistic is positioned on the right side of the post editing screen.', 'post-view'); ?></p>
            <p><?php echo esc_html__('Here are the plugin area screenshots (for the block and classic editor):', 'post-view'); ?></p>
            <img src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'src/images/post-view-statistic-block-editor.png'); ?>" alt="<?php echo esc_attr__('Post View Statistic Block Editor', 'post-view'); ?>" />
            <img src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'src/images/post-view-statistic-classic-editor.png'); ?>" alt="<?php echo esc_attr__('Post View Statistic Classic Editor', 'post-view'); ?>" />
            <h2><?php echo esc_html__('Counter areas', 'post-view'); ?></h2>
            <p><?php echo esc_html__('The plugin area shows:', 'post-view'); ?></p>
            <ul>
                <li><?php echo esc_html__('Total view count for the last 14 days', 'post-view'); ?></li>
                <li><?php echo esc_html__('View count by date', 'post-view'); ?></li>
                <li><?php echo esc_html__('A visual view count chart (Chart.js was used for the chart functionality).', 'post-view'); ?></li>
            </ul>
        </div>
    </div>
<?php }