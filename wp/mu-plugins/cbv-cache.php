<?php
/**
 * Plugin Name: CBV Page Cache
 * Description: Cache HTML trang công khai (khách chưa đăng nhập, GET, không query) vào file. Trả thẳng từ mu-plugin nên bỏ qua toàn bộ plugin/theme/DB.
 * Tắt: tạo file wp-content/cache/cbv-pages/off hoặc define('CBV_CACHE_OFF', true) trong wp-config.php.
 */
if (!defined('ABSPATH')) exit;
if (defined('WP_CLI') || (defined('CBV_CACHE_OFF') && CBV_CACHE_OFF)) return;

const CBV_PC_TTL = 21600; // 6 giờ: ngắn hơn nửa vòng đời nonce (form báo giá trong HTML cache còn hợp lệ)

function cbv_pc_dir() { return WP_CONTENT_DIR . '/cache/cbv-pages'; }
function cbv_pc_key() {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') return null;
    foreach ($_COOKIE as $k => $v) if (preg_match('/^(wordpress_logged_in|wp-postpass|comment_author|woocommerce_items_in_cart|woocommerce_cart_hash|wp_woocommerce_session)/', $k)) return null;
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';
    $q = parse_url($uri, PHP_URL_QUERY);
    if ($q) { parse_str($q, $a); foreach ($a as $k => $v) if (!preg_match('/^(utm_|fbclid$|gclid$|gbraid$|wbraid$|zarsrc$|_gl$|msclkid$)/i', $k)) return null; }
    if (preg_match('#^/(wp-admin|wp-login|wp-json|wp-cron|xmlrpc|wc-api|cart|checkout|my-account|gio-hang|thanh-toan)#i', $path) || preg_match('#/(feed|embed)/?$#i', $path) || preg_match('#\.(xml|xsl|txt|php)$#i', $path)) return null;
    if (is_file(cbv_pc_dir() . '/off')) return null;
    $ver = @filemtime(WP_CONTENT_DIR . '/themes/icd-cabin/functions.php') . '|' . @filemtime(__FILE__);
    return md5(($_SERVER['HTTP_HOST'] ?? '') . '|' . (is_ssl_cbv() ? 's' : 'h') . '|' . $path . '|' . $ver);
}
function is_ssl_cbv() { return (($_SERVER['HTTPS'] ?? '') && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' || ($_SERVER['SERVER_PORT'] ?? '') == 443; }

$cbv_pc_key = cbv_pc_key();
if ($cbv_pc_key) {
    $f = cbv_pc_dir() . '/' . substr($cbv_pc_key, 0, 2) . '/' . $cbv_pc_key . '.html';
    if (is_file($f) && (time() - filemtime($f)) < CBV_PC_TTL) {
        $etag = '"' . substr($cbv_pc_key, 0, 8) . dechex(filemtime($f)) . '"';
        header('X-CBV-Cache: HIT');
        header('ETag: ' . $etag);
        header('Cache-Control: public, max-age=0, must-revalidate');
        header('Vary: Accept-Encoding');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($f)) . ' GMT');
        if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) { http_response_code(304); exit; }
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Length: ' . filesize($f));
        readfile($f);
        exit;
    }
    // Chưa có cache: ghi lại kết quả sau khi WP dựng xong trang (callback ngoài cùng, nhận HTML đã qua mọi bộ lọc)
    add_action('template_redirect', function () use ($cbv_pc_key) {
        if (is_user_logged_in() || is_404() || is_search() || is_feed() || (function_exists('is_cart') && (is_cart() || is_checkout() || is_account_page()))) return;
        ob_start(function ($html) use ($cbv_pc_key) {
            if (strlen($html) < 500 || stripos($html, '</html>') === false || http_response_code() !== 200) return $html;
            foreach (headers_list() as $h) if (stripos($h, 'Set-Cookie:') === 0 || (stripos($h, 'Content-Type:') === 0 && stripos($h, 'text/html') === false)) return $html;
            $dir = cbv_pc_dir() . '/' . substr($cbv_pc_key, 0, 2);
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $tmp = $dir . '/' . $cbv_pc_key . '.tmp' . getmypid();
            if (@file_put_contents($tmp, $html . "\n<!-- cbv-cache " . gmdate('Y-m-d H:i:s') . " UTC -->") !== false) @rename($tmp, $dir . '/' . $cbv_pc_key . '.html');
            @header('X-CBV-Cache: MISS');
            return $html;
        });
    }, -999);
}

/* Xoá cache khi nội dung/giao diện đổi */
function cbv_pc_purge() {
    $d = cbv_pc_dir(); if (!is_dir($d)) return;
    foreach (glob($d . '/*/*.html') ?: [] as $f) @unlink($f);
}
foreach (['save_post', 'deleted_post', 'trashed_post', 'untrashed_post', 'created_term', 'edited_term', 'delete_term', 'wp_update_nav_menu', 'switch_theme', 'update_option_cbv_opts', 'woocommerce_update_product', 'woocommerce_product_set_stock_status'] as $h) add_action($h, 'cbv_pc_purge');
