<?php
add_shortcode('steel_archive_price_list', function () {

    if (!class_exists('WooCommerce') || !is_product_category()) {
        return '';
    }

    $term = get_queried_object();
    if (empty($term->term_id)) {
        return '';
    }

    ob_start();
?>
    <div class="steel-price-list" data-cat="<?php echo esc_attr($term->term_id); ?>">
        <!-- ✅ تیتر داینامیک -->
        <h2 class="steel-price-title">
            لیست قیمت <?php echo esc_html($term->name); ?>
        </h2>
        <table class="steel-table">
            <thead>
                <tr>
                    <th>نام محصول</th>
                    <th>واحد</th>
                    <th>محل بارگیری</th>
                    <th>قیمت</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="steel-body">
                <tr>
                    <td colspan="5">⏳ در حال بارگذاری...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        window.ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
    </script>
<?php
    return ob_get_clean();
});
add_action('wp_ajax_load_steel_archive_prices', 'load_steel_archive_prices');
add_action('wp_ajax_nopriv_load_steel_archive_prices', 'load_steel_archive_prices');

function load_steel_archive_prices()
{
    if (empty($_POST['category'])) {
        wp_die();
    }

    $cat_id   = (int) $_POST['category'];
    $load_all = !empty($_POST['load_all']);

    // ✅ دسته + زیر‌دسته‌ها
    $term_ids = get_term_children($cat_id, 'product_cat');
    $term_ids[] = $cat_id;

    /**
     * ✅ ترفند مهم:
     * meta_query با OR
     * - محصولات دارای قیمت
     * - محصولات بدون قیمت یا 0
     */
    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $load_all ? -1 : 5,
        'tax_query'      => [
            [
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $term_ids,
            ],
        ],
        'meta_query' => [
            'relation' => 'OR',
            [
                'key'     => '_price',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC',
            ],
            [
                'key'     => '_price',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key'     => '_price',
                'value'   => 0,
                'compare' => '=',
                'type'    => 'NUMERIC',
            ],
        ],
        /**
         * ✅ اول قیمت‌دارها، بعد بی‌قیمت‌ها
         */
        'orderby' => [
            'meta_value_num' => 'DESC',
            'title'          => 'ASC',
        ],
    ];

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        echo '<tr><td colspan="5">محصولی یافت نشد.</td></tr>';
        wp_die();
    }

    while ($query->have_posts()) {
        $query->the_post();
        $product = wc_get_product(get_the_ID());
        if (!$product) continue;

        $price = (float) $product->get_price();

        echo '<tr>
            <td><a href="' . esc_url(get_permalink()) . '" target="_blank">' . esc_html($product->get_name()) . '</a></td>
            <td>' . esc_html($product->get_meta('_steel_unit') ?: 'کیلوگرم') . '</td>
            <td>' . esc_html($product->get_meta('_steel_location') ?: 'انبار تهران') . '</td>
            <td>' . ($price > 0 ? wc_price($price) : '<span class="call">تماس بگیرید</span>') . '</td>
            <td><a class="buy-btn" href="' . esc_url(get_permalink()) . '" target="_blank">خرید</a></td>
        </tr>';
    }

    wp_reset_postdata();

    // ✅ دکمه نمایش بیشتر
    if (!$load_all && $query->found_posts > 7) {
        echo '
        <tr class="load-more-row">
            <td colspan="5" style="text-align:center">
                <button class="steel-load-more">نمایش تمام محصولات</button>
            </td>
        </tr>';
    }

    wp_die();
}
