<?php
// faq functions
// ثبت نوع نوشته اختصاصی "faq_question"
function register_faq_post_type()
{
    $labels = array(
        'name' => 'سوالات کاربران',
        'singular_name' => 'سوال کاربر',
        'add_new_item' => 'افزودن سوال جدید',
        'edit_item' => 'ویرایش سوال',
        'new_item' => 'سوال جدید',
        'view_item' => 'مشاهده سوال',
        'search_items' => 'جستجوی سوال',
        'not_found' => 'سوالی پیدا نشد',
        'menu_name' => 'سوالات متداول کاربران',
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'supports' => array('title', 'editor'), // title برای سوال، editor برای پاسخ
        'capability_type' => 'post',
        'menu_icon' => 'dashicons-editor-help',
    );
    register_post_type('faq_question', $args);
}
add_action('init', 'register_faq_post_type');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question-title'])) {
    $question_title = sanitize_text_field($_POST['question-title']);
    $user_name = sanitize_text_field($_POST['user-name']);

    $post_data = array(
        'post_title'   => $question_title . ($user_name ? " - از " . $user_name : ''),
        'post_type'    => 'faq_question',
        'post_status'  => 'pending', // ابتدا منتظر تأیید
    );

    wp_insert_post($post_data);
    echo '<p class="faq-success">✅ سوال شما با موفقیت ارسال شد و پس از تایید مدیر نمایش داده می‌شود.</p>';
}

$faq_published = new WP_Query([
    'post_type'      => 'faq_question',
    'post_status'    => 'publish',
    'posts_per_page' => 1,
]);

$faq_pending = new WP_Query([
    'post_type'      => 'faq_question',
    'post_status'    => 'pending',
    'posts_per_page' => 1,
]);
