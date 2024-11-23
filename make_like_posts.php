<?php
// ساخت لایک پست ها و نمایش با شورت کد + تغییر در هر گست به صورت دستی و کاملا خاص
// افزودن فیلد سفارشی "تعداد لایک‌ها" به نوشته‌ها
add_action('add_meta_boxes', 'add_likes_meta_box_to_posts');
function add_likes_meta_box_to_posts() {
    add_meta_box(
        'post_likes_meta_box',
        'تعداد لایک‌ها',
        'display_post_likes_meta_box',
        'post',
        'side',
        'default'
    );
}

// نمایش فیلد در متاباکس
function display_post_likes_meta_box($post) {
    $likes = get_post_meta($post->ID, '_post_likes', true);
    $likes = $likes ? $likes : rand(300, 500); // مقدار پیش‌فرض
    wp_nonce_field('save_post', 'post_likes_nonce');
    ?>
    <label for="post_likes">تعداد لایک‌ها:</label>
    <input type="number" id="post_likes" name="post_likes" class="widefat" value="<?php echo esc_attr($likes); ?>" min="300" step="1" />
    <?php
}

// ذخیره تعداد لایک‌ها در متای پست
add_action('save_post', 'save_post_likes_meta');
function save_post_likes_meta($post_id) {
    if (isset($_POST['post_likes']) && check_admin_referer('save_post', 'post_likes_nonce')) {
        $likes = intval($_POST['post_likes']);
        update_post_meta($post_id, '_post_likes', $likes);
    }
}

// شورت‌کد برای نمایش تعداد لایک‌ها و ایجاد JSON-LD
function display_post_likes($atts) {
    global $post;

    $atts = shortcode_atts(
        array(
            'default_likes' => 300,
            'min_votes' => 5
        ),
        $atts
    );

    $likes = get_post_meta($post->ID, '_post_likes', true);
    if (!$likes) {
        $likes = rand($atts['default_likes'], $atts['default_likes'] + 200);
        update_post_meta($post->ID, '_post_likes', $likes);
    }

    $votes = floor($likes / 2);
    $rating_value = 5;
    $best_rating = 5;
    $rating_count = max($votes, $atts['min_votes']);

    $title = esc_html(get_the_title());
    $json_ld = array(
        "@context" => "https://schema.org/",
        "@type" => "CreativeWorkSeries",
        "name" => $title,
        "aggregateRating" => array(
            "@type" => "AggregateRating",
            "ratingValue" => $rating_value,
            "bestRating" => $best_rating,
            "ratingCount" => $rating_count
        )
    );

    echo '<script type="application/ld+json">' . json_encode($json_ld, JSON_UNESCAPED_UNICODE) . '</script>';
    return '<div class="post-likes">' . "تا کنون " . esc_html($likes) . " نفر از خدمات امداد اکسپرس در " . $title . " رضایت داشتند" . '</div>';
}
add_shortcode('post_likes', 'display_post_likes');

// شورت‌کد برای نمایش تعداد رأی‌دهنده‌ها
function display_post_votes($atts) {
    global $post;

    $atts = shortcode_atts(
        array(
            'default_likes' => 300,
        ),
        $atts
    );

    // دریافت تعداد لایک‌ها از متای پست
    $likes = get_post_meta($post->ID, '_post_likes', true);

    // مقدار پیش‌فرض در صورت نبودن لایک
    if (!$likes) {
        $likes = rand($atts['default_likes'], $atts['default_likes'] + 200);
        update_post_meta($post->ID, '_post_likes', $likes);
    }

    // محاسبه تعداد رأی‌دهنده‌ها
    $votes = floor($likes / 2);

    return '<div class="post-votes">' . esc_html($votes) . " نفر تاکنون رأی داده‌اند." . '</div>';
}
add_shortcode('post_votes', 'display_post_votes');