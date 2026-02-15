
/**
 * Steel Products Shortcodes + Ajax
 */

/* --------------------------------------------------
 * Global flag (optional but useful)
 * -------------------------------------------------- */
global $steel_shortcode_loaded;
$steel_shortcode_loaded = false;

/* --------------------------------------------------
 * Shortcode: [steel_price_table]
 * -------------------------------------------------- */
add_shortcode('steel_price_table', function () {
    global $steel_shortcode_loaded;
    $steel_shortcode_loaded = true;

    if (!class_exists('WooCommerce')) {
        return '<p>خطا: ووکامرس فعال نیست.</p>';
    }

    $categories = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'parent'     => 0,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    $first_cat_id = 0;
    $category_html = '';

    if (!is_wp_error($categories) && !empty($categories)) {
        $first_cat_id = (int) $categories[0]->term_id;

        foreach ($categories as $cat) {
            $active = ($cat->term_id === $first_cat_id) ? 'active' : '';
            $category_html .= '
                <a href="javascript:void(0)"
                   class="steel-cat ' . $active . '"
                   data-cat="' . esc_attr($cat->term_id) . '">
                   ' . esc_html($cat->name) . '
                </a>';
        }
    }

    ob_start();
    ?>
    <div class="steel-wrapper">
        <div class="steel-sidebar">
            <?php echo $category_html ?: '<p>دسته‌ای یافت نشد</p>'; ?>
        </div>

        <div class="steel-content">
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
                <tbody id="steel-body">
                    <tr>
                        <td colspan="5">⏳ در حال بارگذاری اولیه...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ✅ JS Variables + Flag -->
    <script>
        window.steelShortcodeLoaded = true;
        window.ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        window.firstCategory = <?php echo (int) $first_cat_id; ?>;
    </script>
    <?php
    return ob_get_clean();
});
/* --------------------------------------------------
 * Ajax: Load Steel Products
 * -------------------------------------------------- */
add_action('wp_ajax_load_steel_products', 'load_steel_products_optimized');
add_action('wp_ajax_nopriv_load_steel_products', 'load_steel_products_optimized');

function load_steel_products_optimized()
{
    if (empty($_POST['category'])) {
        wp_die();
    }

    $cat_id = (int) $_POST['category'];
    $page   = isset($_POST['page']) ? (int) $_POST['page'] : 1;

    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 7,
        'paged'          => $page,
        'tax_query'      => [
            [
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $cat_id,
            ],
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
        $price_html = $price > 0 ? wc_price($price) : 'تماس بگیرید';
        $price_class = $price > 0 ? '' : 'call';

        echo '<tr>
            <td><a href="' . esc_url(get_permalink()) . '" target="_blank">' . esc_html($product->get_name()) . '</a></td>
            <td>' . esc_html($product->get_meta('_steel_unit') ?: 'کیلوگرم') . '</td>
            <td>' . esc_html($product->get_meta('_steel_location') ?: 'انبار تهران') . '</td>
            <td class="' . esc_attr($price_class) . '">' . $price_html . '</td>
            <td><a class="buy-btn" href="' . esc_url(get_permalink()) . '" target="_blank">خرید</a></td>
        </tr>';
    }

    wp_reset_postdata();

    if ($query->max_num_pages > $page) {
        echo '
        <tr class="load-more-row">
            <td colspan="5" style="text-align:center">
                <button class="steel-load-more" data-page="' . ($page + 1) . '">
                    مشاهده محصولات بیشتر
                </button>
            </td>
        </tr>';
    }

    wp_die();
}
