<?php
if (!defined('ABSPATH')) exit;
define('CBV_VER', '1.1.1');
require __DIR__ . '/inc/options.php';
require __DIR__ . '/inc/admin-branding.php';
require __DIR__ . '/inc/group-banner.php';

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('woocommerce');
    add_theme_support('responsive-embeds');
    register_nav_menus(['primary' => 'Menu chính', 'footer' => 'Menu chân trang']);
    add_image_size('cbv-card', 480, 380, false);
});

add_action('wp_enqueue_scripts', function () {
    $u = get_template_directory_uri();
    wp_enqueue_style('cbv-fonts', 'https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@600;700&family=Roboto:wght@400;500;700&display=swap', [], null);
    wp_enqueue_style('cbv-main', $u . '/assets/css/main.css', [], CBV_VER);
    wp_enqueue_script('cbv-main', $u . '/assets/js/main.js', [], CBV_VER, ['in_footer' => true, 'strategy' => 'defer']);
    wp_dequeue_style('wp-block-library'); wp_dequeue_style('wc-blocks-style'); wp_dequeue_style('global-styles'); wp_dequeue_style('classic-theme-styles');
}, 20);
add_action('wp_head', function () { echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n"; }, 1);
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

// Ảnh trong nội dung: lazy + async decode
add_filter('the_content', function ($c) {
    return preg_replace('/<img(?![^>]*\bloading=)/i', '<img loading="lazy" decoding="async"', $c);
}, 20);
// Không dùng em dash/en dash
function cbv_no_dash($s) { return is_string($s) ? str_replace(['—', '–', '&#8212;', '&#8211;', '&mdash;', '&ndash;'], '-', $s) : $s; }
foreach (['the_content', 'the_title', 'the_excerpt', 'single_post_title', 'term_description', 'woocommerce_short_description', 'document_title', 'rank_math/frontend/title', 'rank_math/frontend/description', 'rank_math/opengraph/facebook/og_title', 'rank_math/opengraph/twitter/title'] as $h) add_filter($h, 'cbv_no_dash', 20);
/* Lưới cuối: lọc gạch dài trong toàn bộ HTML trang (thông số SP, trích đoạn, alt ảnh...) */
add_action('template_redirect', function () { if (!is_admin() && !wp_doing_ajax() && !is_feed()) ob_start('cbv_no_dash'); }, 0);

// Số sản phẩm/bài mỗi trang giống site cũ
add_action('pre_get_posts', function ($q) {
    if (is_admin() || !$q->is_main_query()) return;
    if ($q->is_tax('product_cat') || $q->is_post_type_archive('product') || $q->is_search()) $q->set('posts_per_page', 24);
    elseif ($q->is_category() || $q->is_home()) $q->set('posts_per_page', 8); // giong site cu, giu duoc URL /trang-N.html
});

/** Danh mục sản phẩm theo thứ tự menu cũ */
function cbv_product_cats() {
    $order = ['Cabin-bao-ve-Bot-gac-bao-ve-pc20', 'cabin-nha-bao-ve-inox', 'choi-gac-bao-ve-thep', 'bot-gac-composite', 'Cabin-thu-phi-giao-thong-BOT', 'Barrier-kiem-soat-ra-vao', 'nha-ve-sinh-cong-cong', 'Xe-ban-hang-luu-dong', 'Nha-di-dong-nha-phi-thuyen-nha-lap-ghep'];
    $terms = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true]);
    usort($terms, function ($a, $b) use ($order) { $x = array_search($a->slug, $order); $y = array_search($b->slug, $order); return ($x === false ? 99 : $x) <=> ($y === false ? 99 : $y); });
    return $terms;
}
function cbv_breadcrumb() {
    if (is_front_page()) return;
    $items = [['Trang chủ', home_url('/')]];
    if (is_singular('product')) {
        $items[] = ['Sản phẩm', get_permalink(wc_get_page_id('shop'))];
        $t = get_the_terms(get_the_ID(), 'product_cat');
        if ($t) { usort($t, fn($a, $b) => $b->parent <=> $a->parent); $c = $t[0];
            foreach (array_reverse(get_ancestors($c->term_id, 'product_cat')) as $a) { $a = get_term($a); $items[] = [$a->name, get_term_link($a)]; }
            $items[] = [$c->name, get_term_link($c)]; }
    } elseif (is_tax('product_cat')) {
        $items[] = ['Sản phẩm', get_permalink(wc_get_page_id('shop'))];
        $c = get_queried_object();
        foreach (array_reverse(get_ancestors($c->term_id, 'product_cat')) as $a) { $a = get_term($a); $items[] = [$a->name, get_term_link($a)]; }
        $items[] = [$c->name, ''];
    } elseif (is_singular('post')) {
        $c = get_the_category(); if ($c) $items[] = [$c[0]->name, get_category_link($c[0])];
    } elseif (is_category()) { $items[] = [single_cat_title('', false), ''];
    } elseif (function_exists('is_shop') && is_shop()) { $items[] = ['Sản phẩm', ''];
    } elseif (is_page()) { $items[] = [get_the_title(), ''];
    } elseif (is_search()) { $items[] = ['Tìm kiếm', '']; }
    echo '<nav class="crumbs" aria-label="Breadcrumb"><div class="wrap"><ol>';
    foreach ($items as $i => $it) {
        $last = $i === count($items) - 1;
        echo '<li>' . (!$last && $it[1] ? '<a href="' . esc_url($it[1]) . '">' . esc_html($it[0]) . '</a>' : '<span>' . esc_html($it[0]) . '</span>') . '</li>';
    }
    echo '</ol></div></nav>';
}
function cbv_pagination() {
    $a = ['type' => 'array', 'prev_text' => '&lsaquo;', 'next_text' => '&rsaquo;', 'mid_size' => 1];
    $o = get_queried_object(); // giu dang URL cu: /slug.html, /slug/trang-N.html
    $u = $o instanceof WP_Term ? get_term_link($o) : ((function_exists('is_shop') && is_shop()) ? get_permalink(wc_get_page_id('shop')) : '');
    if (is_string($u) && substr($u, -5) === '.html') { $a['base'] = substr($u, 0, -5) . '%_%.html'; $a['format'] = '/trang-%#%'; }
    $l = paginate_links($a);
    if ($l) echo '<nav class="pager">' . implode('', $l) . '</nav>';
}
// Thuộc tính sản phẩm (Model, Kích thước, Chất liệu, Hãng SX)
function cbv_attrs($id) {
    $a = get_post_meta($id, '_product_attributes', true);
    return is_array($a) ? array_filter(array_map(fn($x) => $x['value'] ?? '', array_column($a, null, 'name'))) : [];
}

// Schema Organization + LocalBusiness (Rank Math lo phần trang)
add_filter('rank_math/json_ld', function ($data) {
    $data['LocalBusiness'] = ['@type' => 'LocalBusiness', '@id' => home_url('/#localbusiness'), 'name' => 'ICD CABINTECH', 'url' => home_url('/'),
        'telephone' => cbv_opt('hotline'), 'email' => cbv_opt('email'), 'image' => get_template_directory_uri() . '/assets/img/logo.png',
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => cbv_opt('addr_hn'), 'addressCountry' => 'VN']];
    return $data;
}, 20);

// Ảnh: trả bản .webp (tạo sẵn cạnh ảnh gốc, ten.jpg.webp) nếu có - nhẹ hơn ~3 lần, giữ nguyên URL ảnh gốc
add_action('template_redirect', function () {
    if (is_admin() || is_feed()) return;
    ob_start(function ($html) {
        $home = home_url('/');
        return preg_replace_callback('#(?<=["\'\s,(])((?:' . preg_quote($home, '#') . '|/)(?:wp-content/)?uploads/[^"\'\s,)?]+\.(?:jpe?g|png))(?=[\s"\',)])#i', function ($m) use ($home) {
            $rel = rawurldecode(preg_replace('#^' . preg_quote($home, '#') . '|^/#', '', $m[1]));
            return is_file(ABSPATH . $rel . '.webp') ? $m[1] . '.webp' : $m[1];
        }, $html);
    });
});

// rel next/prev + canonical cua Rank Math: /slug.html/page/N -> /slug/trang-N.html
function cbv_fix_paged_url($u) {
    return is_string($u) ? preg_replace(['#\.html/page/1/?$#', '#\.html/page/(\d+)/?$#'], ['.html', '/trang-$1.html'], $u) : $u;
}
add_filter('rank_math/frontend/canonical', 'cbv_fix_paged_url');
foreach (['next', 'prev'] as $rel) add_filter("rank_math/frontend/{$rel}_rel_link", function ($l) { return preg_replace_callback('#href="([^"]+)"#', fn($m) => 'href="' . cbv_fix_paged_url($m[1]) . '"', $l); });

// Trang phân trang vượt quá số trang (URL cũ khi số bài thay đổi) -> 301 về trang 1 của danh mục
add_action('template_redirect', function () {
    global $wp_query;
    if (!is_404() || (int) get_query_var('paged') < 2 || !get_query_var('cbv_html')) return;
    wp_redirect(home_url('/' . get_query_var('cbv_html') . '.html'), 301); exit;
});
