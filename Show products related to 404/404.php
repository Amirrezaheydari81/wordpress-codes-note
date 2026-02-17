<?php

/**
 * Smart 404 Template – Woodmart
 */

get_header();
if (!is_404()) return;

global $wp;

/* =========================
   URL parsing
========================= */
$requested_url = urldecode($wp->request);
$segments      = array_filter(explode('/', $requested_url));
$raw_slug      = end($segments);
$clean_slug    = sanitize_title($raw_slug);

/* استخراج اعداد */
preg_match_all('/\d+(?:\.\d+)?/', $raw_slug, $matches);
$numbers = $matches[0] ?? [];

/* =========================
   1️⃣ Exact slug redirect
========================= */
$exact = get_page_by_path($clean_slug, OBJECT, 'product');
if ($exact) {
    wp_redirect(get_permalink($exact->ID), 301);
    exit;
}

/* =========================
   2️⃣ SKU redirect (numeric)
========================= */
if (!empty($numbers)) {
    $sku = end($numbers);

    $sku_query = new WP_Query([
        'post_type'      => 'product',
        'posts_per_page' => 1,
        'meta_query'     => [[
            'key'   => '_sku',
            'value' => $sku,
        ]],
        'no_found_rows' => true
    ]);

    if ($sku_query->have_posts()) {
        $sku_query->the_post();
        wp_redirect(get_permalink(), 301);
        exit;
    }
    wp_reset_postdata();
}

/* =========================
   3️⃣ Smart search term
========================= */
$title_part = preg_replace('/-\d+.*/', '', $raw_slug);
$title_part = trim(str_replace('-', ' ', $title_part));

/* =========================
   Queries
========================= */
$products_query = $title_part ? new WP_Query([
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    's'              => $title_part,
    'no_found_rows'  => true,
]) : null;

$posts_query = $title_part ? new WP_Query([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 8,
    's'              => $title_part,
    'no_found_rows'  => true,
]) : null;

/* =========================
   ✅ Highlight logic (REAL SKU)
========================= */
$highlight_words = [];

if ($products_query && $products_query->have_posts()) {
    foreach ($products_query->posts as $p) {
        $sku = trim(get_post_meta($p->ID, '_sku', true));
        if (!$sku || mb_strlen($sku) < 2) continue;

        $highlight_words[] = $sku;

        if (strpos($sku, ' ') !== false) {
            foreach (explode(' ', $sku) as $part) {
                if (mb_strlen($part) >= 2) {
                    $highlight_words[] = $part;
                }
            }
        }

        if (preg_match('/[A-Z]+[0-9]+|[0-9]+[A-Z]+/i', $sku)) {
            $highlight_words[] = strtoupper($sku);
        }
    }
}

$highlight_words = array_values(array_unique($highlight_words));

/* =========================
   Match helper
========================= */
function smart404_is_matched($post_id, $words)
{
    $title = get_the_title($post_id);
    foreach ($words as $w) {
        if (stripos($title, $w) !== false) {
            return true;
        }
    }
    return false;
}

/* =========================
   Sort results
========================= */
function smart404_sort($query, $words)
{
    $matched = [];
    $normal  = [];

    if ($query && $query->have_posts()) {
        foreach ($query->posts as $p) {
            if (smart404_is_matched($p->ID, $words)) {
                $matched[] = $p;
            } else {
                $normal[] = $p;
            }
        }
    }
    return array_merge($matched, $normal);
}

$products_sorted = smart404_sort($products_query, $highlight_words);
$posts_sorted    = smart404_sort($posts_query, $highlight_words);
?>

<div class="wd-content-area site-content">
    <div class="page-content">

        <section class="smart404-wrap">
            <h1>صفحه مورد نظر پیدا نشد</h1>

            <div class="serch-wd-custome-404">
                <?php woodmart_search_form(); ?>
            </div>

            <p>اما احتمالاً دنبال این موارد هستید:</p>

            <div class="smart404-list">
                <div class="product-cutome-404-clarotm">
                    <?php if ($products_sorted): ?>
                        <h3 class="smart404-section-title">محصولات مرتبط</h3>
                        <?php foreach ($products_sorted as $post): setup_postdata($post); ?>
                            <a href="<?php the_permalink(); ?>"
                                class="smart404-item <?php echo smart404_is_matched(get_the_ID(), $highlight_words) ? 'smart404-item--matched' : ''; ?>">
                                <?php the_title(); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="blog-cutome-404-clarotm">
                    <?php if ($posts_sorted): ?>
                        <h3 class="smart404-section-title">مطالب وبلاگ</h3>
                        <?php foreach ($posts_sorted as $post): setup_postdata($post); ?>
                            <a href="<?php the_permalink(); ?>"
                                class="smart404-item smart404-item--post <?php echo smart404_is_matched(get_the_ID(), $highlight_words) ? 'smart404-item--matched' : ''; ?>">
                                <?php the_title(); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (!$products_sorted && !$posts_sorted): ?>
                    <p>محتوای مرتبطی پیدا نشد.</p>
                <?php endif; ?>

            </div>

            <?php if ($highlight_words): ?>
                <script>
                    window.smart404HighlightWords = <?php echo json_encode($highlight_words); ?>;
                </script>
            <?php endif; ?>

        </section>

        <?php wp_reset_postdata(); ?>

    </div>
</div>

<script>
    (function() {
        const words = window.smart404HighlightWords;
        if (!Array.isArray(words) || !words.length) return;

        document.querySelectorAll('.smart404-item').forEach(item => {
            let html = item.innerHTML;
            let matched = false;

            words.forEach(word => {
                const safe = word.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const regex = new RegExp(`(${safe})`, 'gi');

                if (regex.test(html)) {
                    matched = true;
                    html = html.replace(regex, '<span class="smart404-highlight">$1</span>');
                }
            });

            item.innerHTML = html;
            if (matched) item.classList.add('smart404-item--matched');
        });
    })();
</script>

<?php get_footer(); ?>