<?php

// ثبت بازدید محصول ووکامرس
function wd_track_product_views()
{
    if (is_singular('product')) {
        global $post;

        // جلوگیری از ثبت بازدید برای مدیر
        if (current_user_can('manage_options')) return;

        $views = (int) get_post_meta($post->ID, '_wd_product_views', true);
        $views++;
        update_post_meta($post->ID, '_wd_product_views', $views);
    }
}
add_action('wp_head', 'wd_track_product_views');
// شورتکد منوی عمودی محصولات پربازدید
function wd_popular_products_vertical_menu()
{

    $args = [
        'post_type'      => 'product',
        'posts_per_page' => 6,
        'post_status'    => 'publish',
        'meta_key'       => '_wd_product_views',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
    ];

    $query = new WP_Query($args);

    if (!$query->have_posts()) return '';

    ob_start();
?>
    <nav class="wd-footer-vertical-menu">
        <ul>
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <li>
                    <a href="<?php the_permalink(); ?>">
                        <?php the_title(); ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </nav>
<?php

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('wd_popular_products_menu', 'wd_popular_products_vertical_menu');
