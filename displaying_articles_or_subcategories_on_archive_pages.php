<?php
function custom_category_or_articles() {
    ob_start(); // برای ذخیره خروجی در بافر

    if ( is_category() ) {
        // بررسی می‌کنیم که آیا این دسته دارای زیر دسته است
        $term = get_queried_object();
        $child_terms = get_terms( array(
            'taxonomy' => 'category',
            'parent'   => $term->term_id,
            'hide_empty' => false, // اگر می‌خواهید فقط دسته‌های با مقاله‌های موجود نشان داده شوند
        ) );

        if ( ! empty( $child_terms ) && ! is_wp_error( $child_terms ) ) {
            // اگر زیر دسته‌ها وجود دارند، آن‌ها را نمایش می‌دهیم
            echo '<div class="list-displaying-articles-or-subcategories-on-archive-pages">';
            echo '<ul>';
            foreach ( $child_terms as $child_term ) {
                echo '<li><a href="' . get_term_link( $child_term ) . '">' . $child_term->name . '</a></li>';
            }
            echo '</ul>';
            echo '</div>';
        } else {
            // اگر زیر دسته‌ای وجود ندارد، مقالات دسته جاری را بدون محدودیت نمایش می‌دهیم
            $args = array(
                'post_type' => 'post', // مقالات
                'posts_per_page' => -1, // بدون محدودیت
                'cat' => $term->term_id // فقط مقالات این دسته را نمایش می‌دهد
            );

            $query = new WP_Query( $args );

            if ( $query->have_posts() ) {
                echo '<div class="list-displaying-articles-or-subcategories-on-archive-pages">';
                echo '<ul>';
                while ( $query->have_posts() ) {
                    $query->the_post();
                    // فقط تیتر مقاله به همراه لینک به آن
                    echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
                }
                echo '</ul>';
                echo '</div>';
            } else {
                echo "";
            }
            wp_reset_postdata(); // برای بازنشانی درخواست
        }
    } else {
        // برای سایر آرشیوها (مثلاً تاریخ‌ها، نویسندگان، و غیره)
        $args = array(
            'post_type' => 'post', // مقالات
            'posts_per_page' => -1 // بدون محدودیت
        );

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            echo '<div class="list-displaying-articles-or-subcategories-on-archive-pages">';
            echo '<ul>';
            while ( $query->have_posts() ) {
                $query->the_post();
                // فقط تیتر مقاله به همراه لینک به آن
                echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
            }
            echo '</ul>';
            echo '</div>';
        } else {
            echo "";
        }
        wp_reset_postdata(); // برای بازنشانی درخواست
    }

    return ob_get_clean(); // خروجی را برمی‌گردانیم
}
add_shortcode( 'displaying_articles_or_subcategories_on_archive_pages', 'custom_category_or_articles' );
// [displaying_articles_or_subcategories_on_archive_pages]
