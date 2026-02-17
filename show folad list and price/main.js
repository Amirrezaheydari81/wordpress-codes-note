<script>
    jQuery(document).ready(function ($) {

    // ✅ فقط اگر شورت‌کد روی صفحه است
    if (typeof window.steelShortcodeLoaded === 'undefined') {
        return;
    }

    if (typeof ajaxurl === 'undefined' || typeof firstCategory === 'undefined') {
        console.warn('Steel variables missing');
    return;
    }

    let currentCat = firstCategory;

    // ✅ لود اولیه (۷ محصول)
    if (currentCat > 0) {
        loadProducts(currentCat, false);
    } else {
        $('#steel-body').html(
            '<tr><td colspan="5">لطفاً یک دسته‌بندی را انتخاب کنید.</td></tr>'
        );
    }

    // ✅ کلیک روی دسته‌بندی
    $('.steel-cat').on('click', function (e) {
        e.preventDefault();

    currentCat = $(this).data('cat');

    $('.steel-cat').removeClass('active');
    $(this).addClass('active');

    $('#steel-body').html(
    '<tr><td colspan="5">⏳ در حال بارگذاری...</td></tr>'
    );

    loadProducts(currentCat, false);
    });

    // ✅ کلیک روی مشاهده تمام محصولات
    $(document).on('click', '.steel-load-more', function () {
        $(this).text('⏳ در حال بارگذاری...');
    loadProducts(currentCat, true);
    });

    // ✅ Ajax Loader
    function loadProducts(catID, loadAll) {
        $.post(ajaxurl, {
            action: 'load_steel_products',
            category: catID,
            load_all: loadAll ? 1 : 0
        }, function (res) {
            $('#steel-body').html(res);
        });
    }

});
</script>
