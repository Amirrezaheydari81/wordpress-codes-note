<?php

// ارسال فایل تصویر رسید کارت به کارت در ایمیل و بخش مشتری در ووکامرس
// نمایش تصویر رسید رد ایمیل و جزئیات سفارش

// نمایش فرم آپلود در صفحه تشکر (checkout/order-received)
add_action('woocommerce_thankyou', 'custom_upload_field_thankyou_page', 1, 1);
function custom_upload_field_thankyou_page($order_id)
{
    $order = wc_get_order($order_id);
    if (!$order) return;

?>
    <div style="line-height: 2.5;text-align:center; border-radius:12px;border: 2px solid red; padding: 15px; margin-top: 30px;">
        <h3 style="color: red;">شماره حساب در همین صفحه قرار دارد.<br> لطفاً بعد از واریز، تصویر رسید واریزی خود را از طریق فرم زیر برای ما ارسال کنید:</h3>
        <form id="upload-receipt-form" enctype="multipart/form-data" style="display: flex;flex-direction: column;align-items: center;row-gap: 30px;">
            <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">
            <label style="display: inline-block;padding: 10px 20px;cursor: pointer;background-color: #4E71FF;color: white;border-radius: 5px;font-size: 15px;width: 30%;" for="real-file">انتخاب تصویر</label>
            <input type="file" id="real-file" name="receipt_image" accept="image/*" required style="display: none;padding: 6px; font-size: 15px; border: 1px solid #ccc; border-radius: 4px; background-color: #f9f9f9; margin-bottom: 10px;">
            <button type="submit" style="background-color: #129990; color: white; border: none; padding: 10px 20px; font-size: 16px; border-radius: 5px; cursor: pointer;width: 25%;">📤 ارسال تصویر</button>
            <div id="upload-message" style="margin-top: 10px;"></div>
        </form>
    </div>

    <script>
        jQuery(document).ready(function($) {
            $("#upload-receipt-form").on("submit", function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                formData.append('action', 'upload_receipt_image');
                $.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data && response.data.message) {
                            $("#upload-message").html("<span style='color:green'>" + response.data.message + "</span>");
                        } else if (response.data && response.data.message) {
                            $("#upload-message").html("<span style='color:red'>" + response.data.message + "</span>");
                        } else {
                            $("#upload-message").html("<span style='color:red'>پاسخ نامشخص از سرور دریافت شد.</span>");
                        }
                    },
                    error: function() {
                        $("#upload-message").html("<span style='color:red'>خطایی در ارسال تصویر رخ داد.</span>");
                    }
                });
            });
        });
    </script>
<?php
}

// پردازش Ajax و آپلود تصویر
add_action('wp_ajax_upload_receipt_image', 'handle_receipt_image_upload');
add_action('wp_ajax_nopriv_upload_receipt_image', 'handle_receipt_image_upload');
function handle_receipt_image_upload()
{
    if (!isset($_FILES['receipt_image']) || !isset($_POST['order_id'])) {
        wp_send_json_error(['message' => 'اطلاعات ناقص ارسال شده است.']);
    }

    $file = $_FILES['receipt_image'];
    $order_id = intval($_POST['order_id']);

    if ($file['size'] > 1024 * 1024) {
        wp_send_json_error(['message' => 'حجم تصویر نباید بیشتر از 1 مگابایت باشد.']);
    }

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    $upload = wp_handle_upload($file, ['test_form' => false]);

    if (isset($upload['error'])) {
        wp_send_json_error(['message' => 'خطا در آپلود تصویر: ' . $upload['error']]);
    }

    $filetype = wp_check_filetype($upload['file'], null);
    $attachment = [
        'post_mime_type' => $filetype['type'],
        'post_title'     => sanitize_file_name($file['name']),
        'post_content'   => '',
        'post_status'    => 'inherit'
    ];
    $attach_id = wp_insert_attachment($attachment, $upload['file']);
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
    wp_update_attachment_metadata($attach_id, $attach_data);

    $image_url = wp_get_attachment_url($attach_id);
    update_post_meta($order_id, '_receipt_image_url', $image_url);

    // ایمیل به مدیریت
    $order = wc_get_order($order_id);
    $to = get_option('admin_email');
    $subject = 'رسید واریزی برای سفارش #' . $order->get_order_number();
    $message = '<h2>رسید جدید برای سفارش #' . $order->get_order_number() . '</h2>';
    $message .= '<p><strong>نام مشتری:</strong> ' . $order->get_formatted_billing_full_name() . '</p>';
    $message .= '<p><strong>شماره سفارش:</strong> ' . $order->get_order_number() . '</p>';
    $message .= '<p><strong>لینک تصویر رسید:</strong> <a href="' . esc_url($image_url) . '" target="_blank">مشاهده تصویر</a></p>';
    $message .= '<p><img src="' . esc_url($image_url) . '" style="max-width:300px;border:1px solid #ccc;"></p>';
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    wp_mail($to, $subject, $message, $headers);

    wp_send_json_success(['message' => 'تصویر با موفقیت آپلود شد.']);
}

// نمایش لینک تصویر در پنل مدیریت سفارش
add_action('woocommerce_admin_order_data_after_order_details', 'show_receipt_image_in_admin');
function show_receipt_image_in_admin($order)
{
    $receipt_url = get_post_meta($order->get_id(), '_receipt_image_url', true);
    if ($receipt_url) {
        echo '<p><strong>تصویر رسید واریزی:</strong> <a href="' . esc_url($receipt_url) . '" target="_blank">مشاهده تصویر</a></p>';
    }
}

// نمایش لینک تصویر در ایمیل‌های ووکامرس
add_filter('woocommerce_email_order_meta_fields', 'add_receipt_image_to_email', 10, 3);
function add_receipt_image_to_email($fields, $sent_to_admin, $order)
{
    $receipt_url = get_post_meta($order->get_id(), '_receipt_image_url', true);
    if ($receipt_url) {
        $fields['receipt_image'] = [
            'label' => 'تصویر رسید واریزی',
            'value' => '<a href="' . esc_url($receipt_url) . '">مشاهده تصویر</a>'
        ];
    }
    return $fields;
}
add_action('woocommerce_admin_order_data_after_order_details', 'show_receipt_image_in_admin_preview');
function show_receipt_image_in_admin_preview($order)
{
    $receipt_url = get_post_meta($order->get_id(), '_receipt_image_url', true);
    if ($receipt_url) {
        echo '<p><strong>تصویر رسید واریزی:</strong><br>';
        echo '<a href="' . esc_url($receipt_url) . '" target="_blank">';
        echo '<img src="' . esc_url($receipt_url) . '" style="max-width: 250px; border: 2px solid #ccc; margin-top: 10px;">';
        echo '</a></p>';
    }
}
add_action('woocommerce_email_after_order_table', 'add_receipt_image_to_email_html', 10, 4);
function add_receipt_image_to_email_html($order, $sent_to_admin, $plain_text, $email)
{
    if (!$sent_to_admin) return;

    $receipt_url = get_post_meta($order->get_id(), '_receipt_image_url', true);
    if ($receipt_url) {
        echo '<h3>تصویر رسید واریزی مشتری:</h3>';
        echo '<a href="' . esc_url($receipt_url) . '" target="_blank">';
        echo '<img src="' . esc_url($receipt_url) . '" style="max-width:300px; border: 1px solid #ccc; margin-top: 10px;">';
        echo '</a>';
    }
}
