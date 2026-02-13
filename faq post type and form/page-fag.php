<?php
// یک صفحه با لینک faq در برگه های وردپرس درست میکنی و تمام

?>
<?php
/*
Template Name: صفحه سوالات متداول
*/
get_header();
?>
<div class="faq-page container">

    <h1 class="faq-title">سوالات متداول</h1>
    <p style="text-align:center;font-size:12px;">هر سوالی در مورد مشکلات خودرو دارید در فرم زیر بپرسید</p>
    <div class="faq-stats">
        <div class="faq-stat faq-stat--approved">
            سوالات پاسخ داده شده:
            <strong><?php echo $faq_published->found_posts; ?></strong>
        </div>

        <div class="faq-stat faq-stat--pending">
            سوالات در انتظار پاسخ:
            <strong><?php echo $faq_pending->found_posts; ?></strong>
        </div>
    </div>

    <!-- فرم ارسال سوال -->
    <form method="post" class="faq-form">
        <?php wp_nonce_field('faq_submit_question', 'faq_nonce'); ?>

        <input
            type="text"
            name="faq_question"
            placeholder="سوال خود را بنویسید..."
            required>

        <button type="submit">ارسال سوال</button>
    </form>

    <?php
    // هندل ارسال فرم
    if (
        isset($_POST['faq_question'], $_POST['faq_nonce']) &&
        wp_verify_nonce($_POST['faq_nonce'], 'faq_submit_question')
    ) {
        $question = sanitize_text_field($_POST['faq_question']);

        wp_insert_post([
            'post_title'  => $question,
            'post_type'   => 'faq_question',
            'post_status' => 'pending',
        ]);

        echo '<p class="faq-success">✅ سوال شما ثبت شد و پس از تایید نمایش داده می‌شود.</p>';
    }
    ?>

    <!-- نمایش سوالات تایید شده -->
    <div class="faq-list">
        <?php
        $faq_query = new WP_Query([
            'post_type'   => 'faq_question',
            'post_status' => 'publish',
            'orderby'     => 'date',
            'order'       => 'DESC',
        ]);

        if ($faq_query->have_posts()):
            while ($faq_query->have_posts()):
                $faq_query->the_post();
        ?>
                <div class="faq-item">
                    <h3 class="faq-question"><?php the_title(); ?></h3>
                    <div class="faq-answer">
                        <?php the_content(); ?>
                    </div>
                </div>
        <?php
            endwhile;
        else:
            echo '<p>هنوز سوالی ثبت نشده است.</p>';
        endif;
        wp_reset_postdata();
        ?>
    </div>

</div>

<?php get_footer(); ?>