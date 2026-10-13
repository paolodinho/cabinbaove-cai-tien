<?php
/**
 * Plugin Name: CBV Core (cabinbaove.com.vn)
 * Description: Giữ nguyên URL site cũ (/Slug.html, giữ chữ hoa), 301 các URL cũ/cabinbaove.vn, chế độ catalog (không giỏ hàng), form báo giá lưu vào admin.
 */
if (!defined('ABSPATH')) exit;

/* ================= 1. URL GIỮ NGUYÊN SITE CŨ ================= */
// SP, bài, trang, danh mục SP, danh mục bài: /Slug.html ; phân trang danh mục: /slug/trang-N.html (như site cũ)
add_action('init', function () {
    add_rewrite_rule('^([^/]+)/trang-([0-9]+)\.html$', 'index.php?cbv_html=$matches[1]&paged=$matches[2]', 'top');
    add_rewrite_rule('^([^/]+)\.html$', 'index.php?cbv_html=$matches[1]', 'top');
});
add_filter('query_vars', function ($v) { $v[] = 'cbv_html'; return $v; });

function cbv_lookup($slug) {
    global $wpdb;
    foreach (['product', 'post', 'page'] as $pt) {
        $row = $wpdb->get_row($wpdb->prepare("SELECT ID, post_name FROM {$wpdb->posts} WHERE post_name=%s AND post_type=%s AND post_status='publish' LIMIT 1", $slug, $pt));
        if ($row) return ['type' => $pt, 'id' => (int) $row->ID, 'slug' => $row->post_name];
    }
    foreach (['product_cat', 'category'] as $tax) {
        $row = $wpdb->get_row($wpdb->prepare("SELECT t.term_id, t.slug FROM {$wpdb->terms} t JOIN {$wpdb->term_taxonomy} tt ON tt.term_id=t.term_id WHERE t.slug=%s AND tt.taxonomy=%s LIMIT 1", $slug, $tax));
        if ($row) return ['type' => $tax, 'id' => (int) $row->term_id, 'slug' => $row->slug];
    }
    return null;
}

add_filter('request', function ($qv) {
    if (empty($qv['cbv_html'])) return $qv;
    $slug = rawurldecode($qv['cbv_html']);
    $paged = $qv['paged'] ?? '';
    $f = cbv_lookup($slug);
    if (!$f) return ['error' => 404];
    if ($f['slug'] !== $slug) { // khác chữ hoa/thường -> 301 về đúng URL gốc
        wp_redirect(home_url('/' . $f['slug'] . ($paged > 1 ? '/trang-' . $paged : '') . '.html'), 301); exit;
    }
    switch ($f['type']) {
        case 'product':  return ['post_type' => 'product', 'p' => $f['id']];
        case 'post':     return ['p' => $f['id']];
        case 'page':     return ['page_id' => $f['id']];
        case 'product_cat': $o = ['product_cat' => get_term($f['id'])->slug]; break;
        default:         $o = ['cat' => $f['id']];
    }
    if ($paged) { $o['paged'] = $paged; $o['cbv_html'] = $f['slug']; }
    return $o;
});

add_filter('post_type_link', function ($url, $post) {
    return $post->post_type === 'product' ? home_url('/' . $post->post_name . '.html') : $url;
}, 10, 2);
add_filter('page_link', function ($url, $id) {
    if ((int) get_option('page_on_front') === (int) $id) return home_url('/');
    $p = get_post($id);
    return $p ? home_url('/' . $p->post_name . '.html') : $url;
}, 10, 2);
add_filter('term_link', function ($url, $term, $tax) {
    return in_array($tax, ['product_cat', 'category'], true) ? home_url('/' . $term->slug . '.html') : $url;
}, 10, 3);
// Phân trang dạng /slug/trang-N.html
add_filter('get_pagenum_link', function ($url, $n) {
    if (!preg_match('#^(.*?/)([^/]+)\.html(?:/page/\d+)?/?(\?.*)?$#', $url, $m)) return $url;
    return $n > 1 ? $m[1] . $m[2] . '/trang-' . $n . '.html' : $m[1] . $m[2] . '.html';
}, 10, 2);
add_filter('redirect_canonical', function ($r, $req) {
    return preg_match('#\.html$#', parse_url($req, PHP_URL_PATH) ?: '') ? false : $r;
}, 10, 2);
add_filter('wp_unique_post_slug', function ($slug, $id, $status, $type, $parent, $orig) { return $slug; }, 10, 6);

/* ================= 2. 301: URL cũ không .html, URL 302 cũ, cabinbaove.vn ================= */
add_action('parse_request', function () {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';
    $map = json_decode(@file_get_contents(__DIR__ . '/cbv-redirects.json'), true) ?: [];
    $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
    $key = rawurldecode($path);
    if (strpos($host, 'cabinbaove.vn') !== false && $host !== 'cabinbaove.com.vn') { // domain phụ đã hợp nhất
        $to = $map['vn'][rtrim($key, '/') . '/'] ?? $map['vn'][$key] ?? '/';
        wp_redirect(preg_match('#^https?://#', $to) ? $to : 'https://cabinbaove.com.vn' . $to, 301); exit;
    }
    if (isset($map['com'][$key])) { wp_redirect(home_url($map['com'][$key]), 301); exit; }
    // /Slug (thiếu .html) -> /Slug.html nếu tồn tại
    if ($key !== '/' && !preg_match('#\.[a-z0-9]{2,4}$#i', $key) && substr_count(trim($key, '/'), '/') === 0 && strpos($key, 'wp-') === false) {
        $f = cbv_lookup(trim($key, '/'));
        if ($f) { wp_redirect(home_url('/' . $f['slug'] . '.html'), 301); exit; }
    }
}, 0);

/* ================= 3. CHẾ ĐỘ CATALOG ================= */
add_filter('woocommerce_is_purchasable', '__return_false');
add_filter('woocommerce_get_price_html', function () { return '<span class="cbv-price">Liên hệ</span>'; });
add_filter('woocommerce_loop_add_to_cart_link', '__return_empty_string');
add_filter('woocommerce_enqueue_styles', '__return_empty_array');
add_filter('woocommerce_product_tabs', function ($t) { unset($t['reviews']); return $t; }, 98);
add_action('template_redirect', function () {
    if (function_exists('is_cart') && (is_cart() || is_checkout() || is_account_page())) { wp_safe_redirect(home_url('/'), 302); exit; }
});

/* ================= 4. FORM BÁO GIÁ -> ADMIN + MAIL ================= */
add_action('init', function () {
    register_post_type('bao_gia', [
        'labels' => ['name' => 'Yêu cầu báo giá', 'singular_name' => 'Yêu cầu báo giá', 'menu_name' => 'Báo giá', 'all_items' => 'Tất cả yêu cầu'],
        'public' => false, 'show_ui' => true, 'menu_icon' => 'dashicons-email-alt', 'menu_position' => 26,
        'supports' => ['title', 'editor'], 'capabilities' => ['create_posts' => 'do_not_allow'], 'map_meta_cap' => true,
    ]);
});
add_action('admin_post_nopriv_cbv_quote', 'cbv_handle_quote');
add_action('admin_post_cbv_quote', 'cbv_handle_quote');
function cbv_handle_quote() {
    $back = wp_get_referer() ?: home_url('/');
    if (!isset($_POST['_cbv']) || !wp_verify_nonce($_POST['_cbv'], 'cbv_quote') || !empty($_POST['website'])) { wp_safe_redirect(add_query_arg('bg', 'err', $back) . '#bao-gia'); exit; }
    $name = sanitize_text_field($_POST['name'] ?? ''); $phone = preg_replace('/[^0-9+ .]/', '', $_POST['phone'] ?? '');
    $sp = sanitize_text_field($_POST['sp'] ?? ''); $note = sanitize_textarea_field($_POST['note'] ?? '');
    if (strlen(preg_replace('/\D/', '', $phone)) < 9) { wp_safe_redirect(add_query_arg('bg', 'err', $back) . '#bao-gia'); exit; }
    $name = $name ?: 'Khách để lại SĐT';
    $body = "Họ tên: $name\nĐiện thoại: $phone\nSản phẩm: $sp\nNhu cầu: $note\nTrang gửi: $back";
    wp_insert_post(['post_type' => 'bao_gia', 'post_status' => 'publish', 'post_title' => "$name - $phone" . ($sp ? " - $sp" : ''), 'post_content' => $body]);
    $o = get_option('cbv_opts', []);
    wp_mail(!empty($o['email']) ? $o['email'] : get_option('admin_email'), "[Báo giá cabin] $name - $phone", $body);
    wp_safe_redirect(add_query_arg('bg', 'ok', $back) . '#bao-gia'); exit;
}
