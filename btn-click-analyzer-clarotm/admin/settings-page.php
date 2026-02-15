<?php
add_action('admin_menu', function () {
    add_menu_page(
        'آنالیز کلیک دکمه',
        'آنالیز کلیک دکمه',
        'manage_options',
        'bca',
        'bca_settings_page'
    );
});

function bca_settings_page() {

    if (isset($_POST['selectors'])) {
        $selectors = array_filter(array_map('trim', explode("\n", $_POST['selectors'])));
        update_option('bca_selectors', $selectors);
        echo '<div class="updated"><p>ذخیره شد</p></div>';
    }

    $selectors = get_option('bca_selectors', []);
    ?>

    <div class="wrap">
        <h1>آنالیز کلیک دکمه ها</h1>

        <form method="post">
            <h2>لیست دکمه های رهگیری شده</h2>
            <textarea name="selectors" rows="6" style="width:400px;direction: ltr;text-align: left;unicode-bidi: plaintext;"><?php
                echo esc_textarea(implode("\n", $selectors));
            ?></textarea>
            <p>در هر خط یک انتخابگر مثل 
            <code>#btn</code>
            <code>.btn</code>
            قراردهید
            </p>
            <button class="button button-primary">ذخیره لیست</button>
        </form>

        <hr>

        <h2>ðŸ“Š گزارش کلیک ماهانه</h2>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th>انتخابگر</th>
                    <th>کلیک ها (30 روز گذشته)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($selectors): ?>
                    <?php foreach ($selectors as $selector): ?>
                        <tr>
                            <td><code><?php echo esc_html($selector); ?></code></td>
                            <td><strong><?php echo bca_monthly_clicks($selector); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2">هیچ انتخابگری تعریف نشده است.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
