<?php
// برعکس کردن تمام دسته بندی های رنک مث نشان دادن از آخر به اول در متا دیسکریپتش
// new variable rankmath %reverse_categories%
add_filter('rank_math/replacements', function($replacements) {
    $replacements['%reverse_categories%'] = reverse_categories_shortcode(); // مقدار نهایی
    return $replacements;
}, 10, 2);
//متغیر %reverse_categories% را درون رنک مث جایگذاری کنید