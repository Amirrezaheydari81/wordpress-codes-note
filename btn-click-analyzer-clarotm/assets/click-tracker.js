document.addEventListener('DOMContentLoaded', () => {
    if (!window.BCA) return;

    BCA.selectors.forEach(selector => {
        document.querySelectorAll(selector).forEach(el => {
            el.addEventListener('click', () => {
                navigator.sendBeacon(
                    BCA.ajax,
                    new URLSearchParams({
                        action: 'bca_track',
                        selector: selector
                    })
                );
            });
        });
    });
});
