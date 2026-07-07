add_action('wp', function () {

if (
is_page_template('page-parent-steel.php') ||
is_home() ||
is_singular('post')
) {
remove_action(
'woodmart_after_header',
'woodmart_page_title',
20
);
}

});