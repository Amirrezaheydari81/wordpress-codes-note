//
/**
 * Shortcode: محصولات پیشنهادی از REST API ووکامرس با کش سمت سرور و lazy load
 */
function spare_products_wc_optimized_shortcode($atts)
{
	$atts = shortcode_atts(array(
		'per_page' => 12,
		'category' => '',
	), $atts, 'spare_products_wc_optimized');

	$cache_key = 'spare_products_wc_optimized_' . md5($atts['per_page'] . '|' . $atts['category']);
	$products_data = get_transient($cache_key);

	if ($products_data === false) {
		$api_url = add_query_arg(array(
			'per_page' => $atts['per_page'],
			'consumer_key' => 'ck_78d459b2675f9ccf43e4b1e57013d7571c14569b',
			'consumer_secret' => 'cs_3bebf0606d0c9db7348d76b67d246b73feb0d7dd',
		), 'https://persiandiesel.ir/wp-json/wc/v3/products');

		if (!empty($atts['category'])) {
			$api_url = add_query_arg('category', $atts['category'], $api_url);
		}

		$response = wp_remote_get($api_url, array('timeout' => 20));
		if (is_wp_error($response)) {
			$products_data = array();
		} else {
			$products_data = json_decode(wp_remote_retrieve_body($response), true);
			set_transient($cache_key, $products_data, 6 * HOUR_IN_SECONDS);
		}
	}

	ob_start();
?>
	<section id="spare-products" class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 my-10 font-vazir">
		<div class="flex items-center justify-between mb-4">
			<h3 class="text-xl sm:text-2xl font-semibold tracking-tight">
				محصولات پیشنهادی لوازم یدکی
			</h3>
		</div>
		<div id="products-grid" class="grid gap-4 sm:gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
			<?php if (!empty($products_data) && is_array($products_data)):
				foreach ($products_data as $item):
					$title = esc_html($item['name'] ?? '');
					$link = esc_url($item['permalink'] ?? '#');
					$img = esc_url($item['images'][0]['src'] ?? '');
					$price = !empty($item['price_html']) ? $item['price_html'] : (!empty($item['price']) ? number_format($item['price'], 0, '', '.') . ' تومان' : '—');
					$in_stock = !empty($item['in_stock']);
					$stock_label = $in_stock ? 'موجود' : 'ناموجود';
			?>
					<div class="group bg-white rounded-2xl border shadow-sm hover:shadow-md transition-shadow p-3 flex flex-col">
						<a href="<?php echo $link ?>" target="_blank" rel="nofollow noopener"
							class="block rounded-xl overflow-hidden aspect-square bg-gray-50">
							<img src="<?php echo $img ?>" alt="<?php echo $title ?>" loading="lazy"
								class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
						</a>
						<div class="mt-3 space-y-2 flex-1" dir="rtl">
							<a href="<?php echo $link ?>" target="_blank" rel="nofollow noopener"
								class="line-clamp-2 text-sm font-medium leading-6 no-underline hover:no-underline">
								<?php echo $title ?>
							</a>
							<div class="flex items-center justify-between">
								<div class="text-sm font-semibold"><?php echo $price ?></div>
								<span class="text-xs px-2 py-1 rounded-full <?php echo $in_stock ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'; ?>">
									<?php echo $stock_label ?>
								</span>
							</div>
						</div>
						<a href="<?php echo $link ?>" target="_blank" rel="nofollow noopener"
							class="mt-3 inline-flex items-center no-underline justify-center h-10 rounded-xl border hover:border-gray-300 text-sm font-medium
							bg-green-400">
							مشاهده و خرید
						</a>
					</div>
				<?php endforeach;
			else: ?>
				<div class="col-span-full text-center text-gray-500">محصولی برای نمایش یافت نشد.</div>
			<?php endif; ?>
		</div>
	</section>
<?php
	return ob_get_clean();
}
add_shortcode('spare_products_wc_optimized', 'spare_products_wc_optimized_shortcode');

function add_tailwind_once()
{
	echo '<script src="https://cdn.tailwindcss.com"></script>';
}
add_action('wp_head', 'add_tailwind_once');
function enqueue_vazir_font_and_links()
{
	// اضافه کردن فونت وزیر از CDN
	wp_enqueue_style(
		'vazir-font',
		'https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css',
		array(),
		null
	);

	// CSS سفارشی: فونت وزیر + no-underline !important
	$custom_css = "
        .font-vazir {
            font-family: 'Vazir', sans-serif;
        }
        a.no-underline,
        a.no-underline:hover,
        a.no-underline:focus,
        a.no-underline:active {
            text-decoration: none !important;
        }
    ";
	wp_add_inline_style('vazir-font', $custom_css);
}
add_action('wp_enqueue_scripts', 'enqueue_vazir_font_and_links');

// [spare_products_wc_optimized]


