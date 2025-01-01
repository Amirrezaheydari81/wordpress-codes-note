<?php
/**
 * * مقدار کلیک یا همون کلیک پروداکت اگر در دیتابیس نباشد یا 0 باشد میاد مقدار رو پیشفرض میزاره روی 1 و همچنین یک گزینه هم به مرتب سازی وردپرس اضافه میکند به اسم بر اساس کلیک که کار کاربر راحت تر باشه، به صورت دیفالت و پیشفرض اگر میخواید روی این گزینه باشه باید از بخش سفارشی سازی ووکامرس این مورد رو بزاری روی بر اساس کلیک و سیو کنید
 */
//////////////////////////////////// فیلتر نمایش محصولات بر اساس کلیک
// اضافه کردن مقدار اولیه کلیک به محصولات جدید
add_action('woocommerce_process_product_meta', 'add_initial_click_count');
function add_initial_click_count($post_id) {
    // گرفتن مقدار متای کلیک
    $click_count = get_post_meta($post_id, '_product_clicks', true);

    // اگر مقدار متای کلیک 0 یا خالی باشد، آن را به 1 تنظیم می‌کنیم
    if ($click_count === '' || $click_count == 0) {
        update_post_meta($post_id, '_product_clicks', 1);
    }
}

add_action('template_redirect', 'track_product_clicks');
function track_product_clicks() {
    if (is_product()) { // بررسی اگر صفحه محصول است
        global $post;
        $clicks = get_post_meta($post->ID, '_product_clicks', true);
        $clicks = !empty($clicks) ? (int)$clicks + 1 : 1;
        update_post_meta($post->ID, '_product_clicks', $clicks);
    }
}
add_filter('woocommerce_get_catalog_ordering_args', 'click_sorting_args');
function click_sorting_args($args) {
    if (isset($_GET['orderby']) && 'click_count' === $_GET['orderby']) {
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC'; // مرتب‌سازی نزولی
        $args['meta_key'] = '_product_clicks'; // کلید متای تعداد کلیک
    }
    return $args;
}
// افزودن گزینه "مرتب‌سازی بر اساس کلیک"
add_filter('woocommerce_default_catalog_orderby_options', 'add_sort_by_clicks_option');
add_filter('woocommerce_catalog_orderby', 'add_sort_by_clicks_option');

function add_sort_by_clicks_option($options) {
    $options['click_count'] = 'مرتب‌سازی بر اساس کلیک';
    return $options;
}
// تغییر کوئری محصولات برای مرتب‌سازی بر اساس تعداد کلیک
add_filter('woocommerce_get_catalog_ordering_args', 'set_click_sorting_args');
function set_click_sorting_args($args) {
    if (isset($_GET['orderby']) && 'click_count' === $_GET['orderby']) {
        $args['orderby'] = 'meta_value_num'; // مرتب‌سازی بر اساس مقدار عددی متا
        $args['order'] = 'DESC'; // ترتیب نزولی
        $args['meta_key'] = '_product_clicks'; // کلید متای تعداد کلیک
    }
    return $args;
}
//////////////////////
add_action('woocommerce_product_query', 'apply_click_sorting_if_selected');
function apply_click_sorting_if_selected($query) {
    // بررسی فیلتر مرتب‌سازی از سمت کاربر
    $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : '';

    // بررسی تنظیم مرتب‌سازی پیش‌فرض از تنظیمات قالب
    $default_orderby = get_option('woocommerce_default_catalog_orderby', 'menu_order');

    // اگر کاربر یا تنظیمات قالب "مرتب‌سازی بر اساس کلیک" را انتخاب کرده باشد
    if ($orderby === 'click_count' || $default_orderby === 'click_count') {
        // تنظیم متای کوئری برای نمایش همه محصولات
        $query->set('meta_query', [
            'relation' => 'OR',
			[
                'key' => '_product_clicks',
                'value' => '0',
                'compare' => '='
            ],
            [
                'key' => '_product_clicks',
                'compare' => 'EXISTS',
            ],
        ]);

        // مرتب‌سازی: ابتدا براساس کلیک و سپس تاریخ
        $query->set('orderby', [
            'meta_value_num' => 'DESC', // مرتب‌سازی نزولی بر اساس مقدار کلیک
            'date' => 'DESC', // مرتب‌سازی نزولی بر اساس تاریخ اضافه شدن
        ]);

        // مشخص کردن کلید متا برای مرتب‌سازی
        $query->set('meta_key', '_product_clicks');
    }
}
