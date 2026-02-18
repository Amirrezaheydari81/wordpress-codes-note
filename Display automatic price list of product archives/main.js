<script>
    jQuery(function ($) {

        let wrapper = $('.steel-price-list');
    if (!wrapper.length) return;

    let catID = wrapper.data('cat');

    function loadProducts(loadAll = false) {
        wrapper.find('.steel-body').html(
            '<tr><td colspan="5">⏳ در حال بارگذاری...</td></tr>'
        );

    $.post(ajaxurl, {
        action: 'load_steel_archive_prices',
    category: catID,
    load_all: loadAll ? 1 : 0
        }, function (res) {
        wrapper.find('.steel-body').html(res);
        });
    }

    // ✅ لود اولیه (۷ محصول)
    loadProducts(false);

    // ✅ نمایش همه
    $(document).on('click', '.steel-load-more', function () {
        $(this).text('⏳ در حال بارگذاری...');
    loadProducts(true);
    });

});
</script>