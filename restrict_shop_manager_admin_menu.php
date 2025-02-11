<?php

function restrict_shop_manager_admin_menu() {
    if (current_user_can('shop_manager')) {
        global $menu, $submenu;

        // لیست مجاز (فقط بخش ووکامرس)
        $allowed_menus = ['woocommerce'];

        // حذف منوهایی که در لیست مجاز نیستند
        foreach ($menu as $key => $item) {
            if (!in_array($item[2], $allowed_menus)) {
                unset($menu[$key]);
            }
        }
 remove_submenu_page('woocommerce', 'wc-admin&path=/extensions');
    }
}
add_action('admin_menu', 'restrict_shop_manager_admin_menu', 999);

function custom_admin_styles() {
    if (current_user_can('shop_manager')) {
        echo '<style>
            /* کد CSS شما اینجا */
            #wp-admin-bar-root-default { display:none !important; }
        </style>';
    }
}
add_action('admin_head', 'custom_admin_styles');

// کم کردن دسترسی و فقط نمایش منوی ووکامرس به مدیر فروشگاه و همچنین حذف منوی بالای کار