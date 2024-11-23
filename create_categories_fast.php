<?php
// ساخته دسته بندی ها به صورت پیشفرض و سریع

function create_tehran_categories() {
    // بررسی اگر دسته بندی "تهران" از قبل وجود داشته باشد
     $parent_category = get_term_by('name', 'تهران', 'category');

     if (!$parent_category) {
        // ایجاد دسته والد "تهران" در صورتی که وجود نداشته باشد
        $parent_category_id = wp_insert_term(
            'تهران',
            'category'
        );

        if (is_wp_error($parent_category_id)) {
            // اگر خطایی رخ دهد، از ادامه عملیات جلوگیری می‌کنیم
            return;
        }

        $parent_category_id = $parent_category_id['term_id'];
    } else {
        $parent_category_id = $parent_category->term_id;
    }

    // ایجاد زیرمجموعه‌های منطقه ۱ تا ۲۲
    for ($i = 1; $i <= 22; $i++) {
        $sub_category_name = 'منطقه-' . $i;

        // بررسی اگر زیرمجموعه از قبل وجود داشته باشد
        if (!term_exists($sub_category_name, 'category')) {
            wp_insert_term(
                $sub_category_name,   // نام زیرمجموعه
                'category',           // نوع دسته (نوشته)
                array(
                    'parent' => $parent_category_id // دسته والد
                )
            );
        }
    }
}

// اجرای تابع هنگام فعال شدن قالب
add_action('after_setup_theme', 'create_tehran_categories');
