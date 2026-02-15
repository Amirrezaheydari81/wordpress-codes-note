<?php
if (!defined('ABSPATH')) exit;

function bca_monthly_clicks($selector) {
    global $wpdb;

    return (int) $wpdb->get_var(
        $wpdb->prepare(
            "
            SELECT COUNT(*)
            FROM {$wpdb->prefix}btn_clicks
            WHERE selector = %s
              AND clicked_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            ",
            $selector
        )
    );
}
