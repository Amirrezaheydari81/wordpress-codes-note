<?php

add_shortcode('auto_product', function () {

    if (!is_singular('post')) return '';

    $title = get_the_title();

    /* ========= 1) استخراج کدها ========= */
    preg_match_all('/\b\d\.\d{4}\b/u', $title, $m);
    $codes = array_unique($m[0]);

    $has_spk = (bool) preg_match('/\bspk\b/i', $title);

    if (empty($codes) && !$has_spk) return '';

    /* ========= 2) گرفتن محصولات کاندید (همه فولاد سردکار) ========= */
    $q = new WP_Query([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1, // خیلی مهم
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);

    if (!$q->have_posts()) return '';

    $best_id = 0;
    $best_score = 0;

    foreach ($q->posts as $pid) {

        $p_title = mb_strtolower(get_the_title($pid), 'UTF-8');
        $p_slug  = mb_strtolower(get_post_field('post_name', $pid), 'UTF-8');

        $hay = $p_title . ' ' . $p_slug;

        // نرمال‌سازی: 1.2080 == 1-2080 == 12080
        $hay_norm = str_replace(['-', '.', ' '], '', $hay);

        $score = 0;

        foreach ($codes as $code) {
            $code_l = mb_strtolower($code, 'UTF-8');
            $code_norm = str_replace(['.', '-'], '', $code_l);

            // تطابق دقیق
            if (strpos($hay, $code_l) !== false) {
                $score += 1000;
            }

            // تطابق نرمال‌شده
            if (strpos($hay_norm, $code_norm) !== false) {
                $score += 800;
            }
        }

        // امتیاز SPK
        if ($has_spk && strpos($hay, 'spk') !== false) {
            $score += 400;
        }

        if ($score > $best_score) {
            $best_score = $score;
            $best_id = $pid;
        }
    }

    // اگر حتی امتیاز نداشت → خروجی نده (عمداً)
    if (!$best_id || $best_score < 800) return '';

    /* ========= 3) خروجی ========= */
    $product = wc_get_product($best_id);
    if (!$product) return '';

    ob_start(); ?>
    <a
        href="<?php echo esc_url(get_permalink($best_id)); ?>"
        class="auto-product-link"
        target="_blank"
        aria-label="مشاهده محصول <?php echo esc_attr($product->get_name()); ?>">
        <div class="auto-product-inline">

            <div class="api-image">
                <?php echo $product->get_image('woocommerce_thumbnail'); ?>
            </div>

            <div class="api-content">
                <p class="api-title"><?php echo esc_html($product->get_name()); ?></p>
                <div class="api-price"><?php echo wp_kses_post($product->get_price_html()); ?></div>
            </div>

            <div class="api-action">
                <span class="api-btn">مشاهده محصول</span>
            </div>

        </div>
    </a>

<?php
    return ob_get_clean();
});
