<?php
// functions.php
functions.php

// ctm

// افزودن گزینه آپلود favicon در Customizer
function custom_favicon_customizer($wp_customize) {
    $wp_customize->add_section('favicon_section', array(
        'title'    => __('Favicon Settings', 'your-theme'),
        'priority' => 10,
    ));

    // PNG
    $wp_customize->add_setting('custom_favicon_png');
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'custom_favicon_png', array(
        'label'    => __('Upload Favicon PNG (min 512x512)', 'your-theme'),
        'section'  => 'favicon_section',
        'settings' => 'custom_favicon_png',
    )));

    // ICO
    $wp_customize->add_setting('custom_favicon_ico');
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'custom_favicon_ico', array(
        'label'    => __('Upload Favicon ICO', 'your-theme'),
        'section'  => 'favicon_section',
        'settings' => 'custom_favicon_ico',
    )));

    // SVG
    $wp_customize->add_setting('custom_favicon_svg');
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'custom_favicon_svg', array(
        'label'    => __('Upload Favicon SVG', 'your-theme'),
        'section'  => 'favicon_section',
        'settings' => 'custom_favicon_svg',
    )));
}
add_action('customize_register', 'custom_favicon_customizer');

// تابع برای گرفتن آدرس favicon
function get_custom_favicon_url($type = 'png') {
    if ($type === 'ico') {
        return get_theme_mod('custom_favicon_ico');
    } elseif ($type === 'svg') {
        return get_theme_mod('custom_favicon_svg');
    } else {
        return get_theme_mod('custom_favicon_png');
    }
}




// header.php
// between <head> </head> tag

<?php
$favicon_png = get_custom_favicon_url('png');
$favicon_ico = get_custom_favicon_url('ico');
$favicon_svg = get_custom_favicon_url('svg');

// اگر PNG مشخص شده باشد
if ($favicon_png):
?>
    <!-- Favicons PNG -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url($favicon_png); ?>" />
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo esc_url($favicon_png); ?>" />
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo esc_url($favicon_png); ?>" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url($favicon_png); ?>" />
<?php endif; ?>

<!-- اگر ICO مشخص شده باشد -->
<?php if ($favicon_ico): ?>
    <link rel="icon" type="image/x-icon" href="<?php echo esc_url($favicon_ico); ?>" />
<?php endif; ?>

<!-- اگر SVG مشخص شده باشد -->
<?php if ($favicon_svg): ?>
    <link rel="icon" type="image/svg+xml" href="<?php echo esc_url($favicon_svg); ?>" />
<?php endif; ?>
