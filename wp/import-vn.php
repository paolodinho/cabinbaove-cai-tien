<?php
/**
 * Chuyen 22 bai chi co tren cabinbaove.vn sang site hop nhat (giu slug cu, them .html; 301 o cbv-redirects.json).
 * Chay: wp/wpx.sh eval-file wp/import-vn.php
 */
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
global $wpdb;
$root = dirname(__DIR__);
define('CBV_VN_MEDIA', $root . '/tham-khao/media-vn/');
$rows = json_decode(file_get_contents($root . '/tham-khao/data/vn-move-import.json'), true);

function cbv_vn_attach($url, $parent, $alt) {
    global $wpdb;
    $rel = rawurldecode(explode('/wp-content/uploads/', $url)[1] ?? '');
    $file = CBV_VN_MEDIA . $rel;
    if (!$rel || !is_file($file)) return 0;
    $ex = (int) $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_cbv_src' AND meta_value=%s", 'vn:' . $rel));
    if ($ex) return $ex;
    $tmp = wp_tempnam(basename($file)); copy($file, $tmp);
    $id = media_handle_sideload(['name' => basename($file), 'tmp_name' => $tmp], $parent, $alt);
    if (is_wp_error($id)) { @unlink($tmp); return 0; }
    update_post_meta($id, '_cbv_src', 'vn:' . $rel);
    update_post_meta($id, '_wp_attachment_image_alt', $alt);
    return $id;
}
$n = 0;
foreach ($rows as $x) {
    $x['slug'] = trim(parse_url($x['vn_url'], PHP_URL_PATH), '/'); // giu dung duong dan cu tren cabinbaove.vn
    $cat = get_term_by('slug', $x['cat'], 'category');
    $id = (int) $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name=%s AND post_type='post' AND post_status<>'trash'", $x['slug']));
    $arr = ['post_type' => 'post', 'post_status' => 'publish', 'post_title' => $x['title'], 'post_content' => '', 'post_excerpt' => $x['excerpt'],
            'post_date' => $x['date'], 'post_category' => $cat ? [$cat->term_id] : []];
    if ($id) { $arr['ID'] = $id; wp_update_post($arr); } else $id = wp_insert_post($arr);
    $content = $x['content'];
    foreach ($x['imgs'] as $u) {
        $a = cbv_vn_attach($u, $id, $x['title']);
        if ($a) $content = str_replace($u, wp_get_attachment_url($a), $content);
    }
    $content = preg_replace('#https?://cabinbaove\.vn/([a-z0-9\-]+)/?(?=["\'])#i', '/$1.html', $content); // link noi bo .vn -> dang .html (301 lo phan con lai)
    wp_update_post(['ID' => $id, 'post_content' => $content]);
    $wpdb->update($wpdb->posts, ['post_name' => $x['slug']], ['ID' => $id]); clean_post_cache($id);
    $f = $x['featured'] ? cbv_vn_attach($x['featured'], $id, $x['title']) : 0;
    if (!$f && $x['imgs']) $f = cbv_vn_attach($x['imgs'][0], $id, $x['title']);
    if ($f) set_post_thumbnail($id, $f);
    update_post_meta($id, '_cbv_old_url', $x['vn_url']);
    $n++;
}
WP_CLI::success("Chuyen tu cabinbaove.vn: $n bai");
