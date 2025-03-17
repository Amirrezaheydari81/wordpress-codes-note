<?php
require_once 'wp-config.php'; // لود تنظیمات وردپرس
require_once 'wp-load.php';
require_once 'vendor/autoload.php'; // لود PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

function create_category_if_not_exists($category_name)
{
    $term = term_exists($category_name, 'product_cat');
    if ($term === 0 || $term === null) {
        $term = wp_insert_term($category_name, 'product_cat');
        if (is_wp_error($term)) {
            return $category_name;
        }
        return $category_name;
    }
    return $category_name;
}

function import_products_from_excel($file_path)
{
    $spreadsheet = IOFactory::load($file_path);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    foreach ($rows as $index => $row) {
        if ($index == 0) continue; // رد کردن سطر اول (هدر)

        $product_name = trim($row[0]); // ستون A - نام محصول
        $categories = array_map('trim', explode(',', $row[2])); // ستون C - دسته‌ها

        if (empty($product_name)) continue; // بررسی اینکه نام محصول خالی نباشد

        $category_names = [];
        foreach ($categories as $category_name) {
            if (!empty($category_name)) {
                $category_names[] = create_category_if_not_exists($category_name);
            }
        }

        $post_data = [
            'post_title'  => $product_name,
            'post_status' => 'publish',
            'post_type'   => 'product',
        ];

        $post_id = wp_insert_post($post_data);
        if (!is_wp_error($post_id) && !empty($category_names)) {
            wp_set_object_terms($post_id, $category_names, 'product_cat');
        }
    }
}

$file_path = 'atahose-product.xlsx'; // مسیر فایل اکسل
import_products_from_excel($file_path);

echo "وارد کردن محصولات انجام شد.";
