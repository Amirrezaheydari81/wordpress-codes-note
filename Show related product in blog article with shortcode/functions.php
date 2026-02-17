<?php
function auto_product_from_post_title_shortcode()
{

    if (! is_singular('post')) return '';

    global $post;
    $title = $post->post_title;
    $keyword = '';

    // استخراج فولاد عددی مثل 1.2080
    if (preg_match('/\b\d\.\d{4}\b/', $title, $m)) {
        $keyword = $m[0];
    } elseif (preg_match('/\bSPK\b/i', $title, $m)) {
        $keyword = $m[0];
    }

    if (empty($keyword)) return '';

    $products = get_posts([
        'post_type'      => 'product',
        'posts_per_page' => 1,
        's'              => $keyword,
    ]);

    if (empty($products)) return '';

    $product = wc_get_product($products[0]->ID);
    if (! $product) return '';

    ob_start();
?>
    <div class="auto-product-inline">
        <div class="api-image">
            <a href="<?php echo get_permalink($product->get_id()); ?>">
                <?php echo $product->get_image('thumbnail'); ?>
            </a>
        </div>

        <div class="api-content">
            <h4 class="api-title">
                <a href="<?php echo get_permalink($product->get_id()); ?>">
                    <?php echo esc_html($product->get_name()); ?>
                </a>
            </h4>

            <div class="api-price">
                <?php echo $product->get_price_html(); ?>
            </div>
        </div>

        <div class="api-action">
            <a href="<?php echo esc_url($product->add_to_cart_url()); ?>"
                class="button api-btn">
                خرید کنید
            </a>
        </div>
    </div>
<?php
    return ob_get_clean();
}

add_shortcode('auto_product', 'auto_product_from_post_title_shortcode');
// Short code  == [auto_product]