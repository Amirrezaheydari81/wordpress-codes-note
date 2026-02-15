// Add end line  in footer.php file
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

    if (currentCat > 0) {
        loadProducts(currentCat, 1, false);
    } else {
        $('#steel-body').html(
            '<tr><td colspan="5">لطفاً یک دسته‌بندی را انتخاب کنید.</td></tr>'
        );
    }

    $('.steel-cat').on('click', function (e) {
        e.preventDefault();

        currentCat = $(this).data('cat');

        $('.steel-cat').removeClass('active');
        $(this).addClass('active');

        $('#steel-body').html(
            '<tr><td colspan="5">⏳ در حال بارگذاری...</td></tr>'
        );

        loadProducts(currentCat, 1, false);
    });

    $(document).on('click', '.steel-load-more', function () {
        let nextPage = $(this).data('page');
        $(this).text('⏳ ...');
        loadProducts(currentCat, nextPage, true);
    });

    function loadProducts(catID, page, append) {
        $.post(ajaxurl, {
            action: 'load_steel_products',
            category: catID,
            page: page
        }, function (res) {
            if (append) {
                $('.load-more-row').remove();
                $('#steel-body').append(res);
            } else {
                $('#steel-body').html(res);
            }
        });
    }
});
