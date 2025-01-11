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
// افزودن گزینه "مرتب‌سازی بر اساس کلیک" به لیست مرتب‌سازی
add_filter('woocommerce_default_catalog_orderby_options', 'add_sort_by_clicks_option');
add_filter('woocommerce_catalog_orderby', 'add_sort_by_clicks_option');

function add_sort_by_clicks_option($options) {
    $options['click_count'] = 'مرتب‌سازی بر اساس کلیک'; // اضافه کردن گزینه
    return $options;
}

// تنظیم پیش‌فرض مرتب‌سازی روی کلیک
add_filter('pre_option_woocommerce_default_catalog_orderby', function ($default_orderby) {
    return 'click_count'; // تنظیم پیش‌فرض روی مرتب‌سازی بر اساس کلیک
});

// اعمال مرتب‌سازی بر اساس کلیک
add_action('woocommerce_product_query', 'apply_click_sorting');
function apply_click_sorting($query) {
    // بررسی اگر کاربر "مرتب‌سازی بر اساس کلیک" را انتخاب کرده باشد یا پیش‌فرض است
    $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : get_option('woocommerce_default_catalog_orderby');

    if ($orderby === 'click_count') {
        // اضافه کردن متای کلیک برای محصولات
        $query->set('meta_query', [
            [
                'key' => '_product_clicks',
                'compare' => 'EXISTS',
            ],
        ]);

        // تنظیم مرتب‌سازی بر اساس کلیک
        $query->set('orderby', 'meta_value_num'); // مرتب‌سازی بر اساس مقدار عددی متا
        $query->set('meta_key', '_product_clicks'); // کلید متای تعداد کلیک
        $query->set('order', 'DESC'); // ترتیب نزولی
    }
}
