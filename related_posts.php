<?php
// نمایش پست های مرتبط و درون دسته بندی فعلی برای لینکسازی قوی تر با استایل خود المنتور
function display_related_posts_shortcode( $atts ) {
    $atts = shortcode_atts( [
        'count' => 3, // تعداد پیش‌فرض پست‌ها
    ], $atts );

    $category = get_the_category();

    if ( ! empty( $category ) ) {
        $category_name = $category[0]->name; // دریافت نام دسته‌بندی
        $args = [
            'category__in' => $category[0]->term_id, // دسته‌بندی مشابه با پست جاری
            'post__not_in' => [ get_the_ID() ], // عدم نمایش پست جاری
            'posts_per_page' => $atts['count'], // تعداد پست‌های قابل نمایش
            'orderby' => 'rand', // نمایش تصادفی پست‌ها
        ];
        $query = new WP_Query( $args );

        $output = '<link rel="stylesheet" href="https://webfarsh.com/wp-content/plugins/elementor-pro/assets/css/widget-posts-rtl.min.css">';
//         $output .= '<h3>مقالات مرتبط با ' . esc_html( $category_name ) . '</h3>'; // افزودن عنوان با نام دسته‌بندی
        $output .= '<div class="elementor-element elementor-grid-3 elementor-grid-tablet-2 elementor-grid-mobile-1 elementor-posts--thumbnail-top elementor-card-shadow-yes elementor-posts__hover-gradient elementor-widget elementor-widget-posts">';
        $output .= '<div class="elementor-posts-container elementor-posts elementor-posts--skin-classic elementor-grid elementor-has-item-ratio" style="gap: 25px;">';

        while ( $query->have_posts() ) {
            $query->the_post();
            $image_url = get_the_post_thumbnail_url( get_the_ID(), 'full' ); // لینک تصویر کامل

            $output .= '<article class="elementor-post elementor-grid-item post type-post status-publish format-standard has-post-thumbnail hentry category-blog" style="border:2px #000 solid; padding:10px; border-radius:20px;">';
            $output .= '<a href="' . get_permalink() . '" class="elementor-post__thumbnail__link">';
            $output .= '<img class="attachment-medium size-medium" style="border-radius:20px;" src="'. esc_url( $image_url ) .'" data-lazy-src="' . esc_url( $image_url ) . '" alt="' . get_the_title() . '" />';
            $output .= '</a>';
            $output .= '<span class="elementor-post__title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></span>';
            $output .='<a class="elementor-post__read-more" href="'.get_permalink().'" tabindex="-1"> توضیحات بیشتر » </a>';
            $output .= '</article>';
        }

        wp_reset_postdata();

        $output .= '</div></div>';
    } else {
        $output = '<p>' . __( 'No related posts found', 'textdomain' ) . '</p>';
    }

    return $output;
}
add_shortcode( 'related_posts', 'display_related_posts_shortcode' );
