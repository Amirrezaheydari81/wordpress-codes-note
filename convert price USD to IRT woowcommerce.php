// تابع دریافت نرخ تبدیل دلار به تومان از API
function get_voucher_price() {
$api_url = "https://red-gift.site/api/v-price";
$response = wp_remote_get($api_url);

if (is_wp_error($response)) {
return false;
}

$data = json_decode(wp_remote_retrieve_body($response), true);

if ($data && isset($data['data']['voucher_price'])) {
return floatval($data['data']['voucher_price']);
}

return false;
}

// نمایش قیمت نهایی در صفحه پرداخت ووکامرس
function display_final_price_on_checkout(){
global $woocommerce;

// مبلغ سبد خرید + مالیات
$amount = $woocommerce->cart->cart_contents_total + $woocommerce->cart->tax_total;

// دریافت مقدار voucher_price از API
$voucher_price = get_voucher_price();

if ($voucher_price) {
$final_price = $amount * $voucher_price;
echo "<p><strong>مبلغ نهایی پرداختی: </strong>" . number_format($final_price) . " تومان</p>";
} else {
echo "<p>خطا در دریافت نرخ تبدیل.</p>";
}
}

add_action('woocommerce_review_order_before_payment', 'display_final_price_on_checkout');

function convert_order_total_to_toman($order_total, $order) {
$voucher_price = get_voucher_price(); // دریافت نرخ تبدیل از API

if ($voucher_price) {
return floatval($order_total) * $voucher_price;
}

return $order_total;
}

add_filter('woocommerce_order_amount_total', 'convert_order_total_to_toman', 10, 2);