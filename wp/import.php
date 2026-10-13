<?php
/**
 * Nhap du lieu cabinbaove.com.vn vao WP Local. Chay: wp/wpx.sh eval-file wp/import.php
 * Giu nguyen slug (ke ca chu hoa), title tag, meta description. Chay lai duoc (cap nhat theo slug).
 */
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
global $wpdb;
$root  = dirname(__DIR__);
$items = json_decode(file_get_contents($root . '/tham-khao/data/import.json'), true);
define('CBV_MEDIA', $root . '/tham-khao/media');
$by    = [];
foreach ($items as $x) $by[$x['type']][] = $x;

function cbv_set_slug($id, $slug) { global $wpdb; $wpdb->update($wpdb->posts, ['post_name' => $slug], ['ID' => $id]); clean_post_cache($id); }
function cbv_find($slug, $type) { global $wpdb; return (int) $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name=%s AND post_type=%s AND post_status<>'trash' LIMIT 1", $slug, $type)); }
function cbv_term($slug, $name, $tax, $parent = 0, $desc = '') {
    global $wpdb;
    $tid = (int) $wpdb->get_var($wpdb->prepare("SELECT t.term_id FROM {$wpdb->terms} t JOIN {$wpdb->term_taxonomy} tt ON tt.term_id=t.term_id WHERE t.slug=%s AND tt.taxonomy=%s", $slug, $tax));
    if (!$tid) { $r = wp_insert_term($name, $tax, ['slug' => sanitize_title($slug), 'parent' => $parent, 'description' => $desc]); $tid = is_wp_error($r) ? (int) $r->get_error_data() : $r['term_id']; }
    else wp_update_term($tid, $tax, ['name' => $name, 'parent' => $parent, 'description' => $desc]);
    $wpdb->update($wpdb->terms, ['slug' => $slug], ['term_id' => $tid]); clean_term_cache($tid, $tax);
    return $tid;
}
function cbv_attach($path, $parent, $title) {
    global $wpdb;
    if (!$path) return 0;
    $path = preg_replace('#^https?://(www\.)?cabinbaove\.com\.vn#', '', $path);
    $file = CBV_MEDIA . urldecode($path);
    if (!is_file($file)) return 0;
    $exist = (int) $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_cbv_src' AND meta_value=%s LIMIT 1", $path));
    if ($exist) return $exist;
    $tmp = wp_tempnam(basename($file)); copy($file, $tmp);
    $id = media_handle_sideload(['name' => basename($file), 'tmp_name' => $tmp], $parent, $title);
    if (is_wp_error($id)) { @unlink($tmp); WP_CLI::warning($path . ' ' . $id->get_error_message()); return 0; }
    update_post_meta($id, '_cbv_src', $path);
    update_post_meta($id, '_wp_attachment_image_alt', $title);
    return $id;
}
function cbv_seo($id, $x) {
    if (!empty($x['title_tag'])) update_post_meta($id, 'rank_math_title', $x['title_tag']);
    if (!empty($x['meta_desc'])) update_post_meta($id, 'rank_math_description', $x['meta_desc']);
}

/* 1. Danh muc san pham (cha truoc) */
$pc = [];
$cats = $by['product_cat'];
usort($cats, fn($a, $b) => strlen($a['parent']) <=> strlen($b['parent']));
foreach ($cats as $c) {
    if ($c['slug'] === 'san-pham') continue;
    $pc[$c['slug']] = cbv_term($c['slug'], $c['name'], 'product_cat', $c['parent'] ? ($pc[$c['parent']] ?? 0) : 0, $c['description'] ?? '');
    update_term_meta($pc[$c['slug']], 'rank_math_title', $c['title_tag']);
    update_term_meta($pc[$c['slug']], 'rank_math_description', $c['meta_desc']);
}
/* 2. Danh muc bai */
$ac = [];
foreach ($by['category'] as $c) {
    $ac[$c['slug']] = cbv_term($c['slug'], $c['name'], 'category');
    update_term_meta($ac[$c['slug']], 'rank_math_title', $c['title_tag']);
    update_term_meta($ac[$c['slug']], 'rank_math_description', $c['meta_desc']);
}
$ac['Dich-vu-cua-chung-toi'] && wp_update_term($ac['Dich-vu-cua-chung-toi'], 'category', ['parent' => $ac['gioi-thieu']]);
$ac['Tuyen-Dung'] && wp_update_term($ac['Tuyen-Dung'], 'category', ['parent' => $ac['hoat-dong']]);
foreach ($by['category'] as $c) { $wpdb->update($wpdb->terms, ['slug' => $c['slug']], ['term_id' => $ac[$c['slug']]]); clean_term_cache($ac[$c['slug']], 'category'); }
wp_update_term(1, 'category', ['name' => 'Chưa phân loại', 'slug' => 'chua-phan-loai']);

/* 3. San pham */
$n = 0; $total = count($by['product']);
foreach ($by['product'] as $i => $x) {
    $id = cbv_find($x['slug'], 'product');
    $arr = ['post_type' => 'product', 'post_status' => 'publish', 'post_title' => $x['h1'], 'post_content' => $x['content'], 'post_excerpt' => $x['excerpt'],
            'menu_order' => $i, 'post_date' => date('Y-m-d H:i:s', strtotime('2026-01-01') - $i * 3600)];
    if ($id) { $arr['ID'] = $id; wp_update_post($arr); } else $id = wp_insert_post($arr);
    cbv_set_slug($id, $x['slug']);
    wp_set_object_terms($id, 'simple', 'product_type');
    wp_set_object_terms($id, array_values(array_filter(array_map(fn($s) => $pc[$s] ?? 0, $x['cats']))), 'product_cat');
    $attrs = []; $pos = 0;
    foreach ($x['attrs'] as $k => $v) {
        if ($v === '') continue;
        $attrs[sanitize_title($k)] = ['name' => $k, 'value' => $v, 'position' => $pos++, 'is_visible' => 1, 'is_variation' => 0, 'is_taxonomy' => 0];
    }
    update_post_meta($id, '_product_attributes', $attrs);
    if (!empty($x['attrs']['Model'])) update_post_meta($id, '_cbv_model', $x['attrs']['Model']);
    update_post_meta($id, '_cbv_old_url', $x['url']);
    $f = cbv_attach($x['featured'], $id, $x['h1']);
    if ($f) set_post_thumbnail($id, $f);
    $g = [];
    foreach (array_slice($x['gallery'], 1) as $p) { $a = cbv_attach($p, $id, $x['h1']); if ($a) $g[] = $a; }
    update_post_meta($id, '_product_image_gallery', implode(',', $g));
    update_post_meta($id, '_stock_status', 'instock'); update_post_meta($id, '_visibility', 'visible');
    cbv_seo($id, $x);
    $n++; if ($n % 20 === 0) WP_CLI::log("SP $n/$total");
}
WP_CLI::success("San pham: $n");

/* 4. Bai viet */
$n = 0;
foreach ($by['post'] as $x) {
    $id = cbv_find($x['slug'], 'post');
    $arr = ['post_type' => 'post', 'post_status' => 'publish', 'post_title' => $x['h1'], 'post_content' => $x['content'], 'post_excerpt' => wp_strip_all_tags($x['excerpt']),
            'post_date' => $x['date'] ?: '2015-01-01 09:00:00', 'post_category' => array_values(array_filter(array_map(fn($s) => $ac[$s] ?? 0, $x['cats'])))];
    if ($id) { $arr['ID'] = $id; wp_update_post($arr); } else $id = wp_insert_post($arr);
    cbv_set_slug($id, $x['slug']);
    update_post_meta($id, '_cbv_old_url', $x['url']);
    $f = cbv_attach($x['featured'], $id, $x['h1']);
    if ($f) set_post_thumbnail($id, $f);
    cbv_seo($id, $x);
    $n++;
}
WP_CLI::success("Bai viet: $n");

/* 5. Trang: Trang chu, San pham (shop), Lien he */
$mk = function ($slug, $title, $content = '') {
    $id = cbv_find($slug, 'page');
    $arr = ['post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'post_content' => $content];
    if ($id) { $arr['ID'] = $id; wp_update_post($arr); return $id; }
    return wp_insert_post($arr);
};
$home = $mk('trang-chu', 'Trang chủ');
$shop = $mk('san-pham', 'Sản phẩm');
$contact = $mk('lien-he', 'Liên hệ', '');
foreach ($by['home'] as $h) if ($h['url'] === 'https://cabinbaove.com.vn/') cbv_seo($home, $h);
foreach ($by['product_cat'] as $h) if ($h['slug'] === 'san-pham') cbv_seo($shop, $h);
foreach ($by['contact'] as $h) cbv_seo($contact, $h);
update_option('show_on_front', 'page'); update_option('page_on_front', $home);
update_option('woocommerce_shop_page_id', $shop);
foreach (['sample-page', 'hello-world'] as $s) foreach (['page', 'post'] as $t) { $d = cbv_find($s, $t); if ($d) wp_delete_post($d, true); }
foreach (['cart', 'checkout', 'my-account'] as $s) { $d = cbv_find($s, 'page'); if ($d) wp_update_post(['ID' => $d, 'post_status' => 'draft']); }
WP_CLI::success('Xong import');
