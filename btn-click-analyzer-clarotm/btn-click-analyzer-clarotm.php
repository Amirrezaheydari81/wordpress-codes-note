<?php
/*
Plugin Name: آنالیز تعداد کلیک دکمه تماس (اختصاصی)
Description: Track clicks on CSS selectors and show monthly reports
Version: 1.0.0
Author: Amirreza Heydari - ClaroTM
*/

if (!defined('ABSPATH')) exit;

define('BCA_PATH', plugin_dir_path(__FILE__));
define('BCA_URL', plugin_dir_url(__FILE__));

require_once BCA_PATH . 'admin/settings-page.php';
require_once BCA_PATH . 'includes/reports.php';


/* === Activation: Create Table === */
register_activation_hook(__FILE__, 'bca_create_table');
function bca_create_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'btn_clicks';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED AUTO_INCREMENT,
        selector VARCHAR(190) NOT NULL,
        clicked_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY selector_date (selector, clicked_at)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// 
add_action('wp_enqueue_scripts', function () {

    $selectors = get_option('bca_selectors', []);

    if (empty($selectors)) return;

    wp_enqueue_script(
        'bca-tracker',
        BCA_URL . 'assets/click-tracker.js',
        [],
        '1.0',
        true
    );

    wp_localize_script('bca-tracker', 'BCA', [
        'ajax' => admin_url('admin-ajax.php'),
        'selectors' => $selectors
    ]);
});

add_action('wp_ajax_nopriv_bca_track', 'bca_track');
add_action('wp_ajax_bca_track', 'bca_track');

function bca_track() {
    if (empty($_POST['selector'])) wp_die();

    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'btn_clicks',
        [
            'selector' => sanitize_text_field($_POST['selector']),
            'clicked_at' => current_time('mysql')
        ]
    );

    wp_die();
}

