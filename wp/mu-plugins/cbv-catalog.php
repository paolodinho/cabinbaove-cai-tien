<?php
/**
 * Plugin Name: CBV Catalogue (cabinbaove.com.vn)
 * Description: Tải catalogue PDF theo nhóm sản phẩm. Khách điền đủ họ tên, số điện thoại, email; catalogue được gửi về email của khách; thông tin lưu ở menu "Khách tải catalogue" và gửi email.
 * Quản trị: menu "Catalogue" > Thêm mới: nhập tên, chọn file PDF, tick nhóm sản phẩm, (tuỳ chọn) ảnh bìa = Ảnh đại diện.
 */
if (!defined('ABSPATH')) exit;

add_action('init', function () {
    register_post_type('cbv_catalog', [
        'labels' => ['name' => 'Catalogue', 'singular_name' => 'Catalogue', 'add_new' => 'Thêm catalogue', 'add_new_item' => 'Thêm catalogue mới', 'edit_item' => 'Sửa catalogue', 'all_items' => 'Tất cả catalogue'],
        'public' => false, 'show_ui' => true, 'menu_icon' => 'dashicons-media-document', 'menu_position' => 27,
        'supports' => ['title', 'thumbnail', 'page-attributes'],
    ]);
    register_post_type('tai_catalog', [
        'labels' => ['name' => 'Khách tải catalogue', 'singular_name' => 'Lượt tải catalogue', 'menu_name' => 'Khách tải catalogue', 'all_items' => 'Tất cả lượt tải'],
        'public' => false, 'show_ui' => true, 'menu_icon' => 'dashicons-download', 'menu_position' => 28,
        'supports' => ['title', 'editor'], 'capabilities' => ['create_posts' => 'do_not_allow'], 'map_meta_cap' => true,
    ]);
});

/* ---------- Quản trị: ô chọn file PDF + nhóm sản phẩm ---------- */
add_action('add_meta_boxes_cbv_catalog', function () {
    add_meta_box('cbv_cat_file', 'File PDF và nhóm sản phẩm', function ($p) {
        wp_nonce_field('cbv_cat_save', '_cbvcat');
        $fid = (int) get_post_meta($p->ID, '_cbv_file', true); $groups = (array) get_post_meta($p->ID, '_cbv_groups', true);
        $url = $fid ? wp_get_attachment_url($fid) : '';
        echo '<p><input type="hidden" id="cbv_file" name="cbv_file" value="' . esc_attr($fid ?: '') . '"><button type="button" class="button button-primary" id="cbv_pick">Chọn file PDF</button> <span id="cbv_fname">' . ($url ? esc_html(basename($url)) : 'Chưa chọn file') . '</span></p>';
        echo '<p><b>Hiện ở nhóm sản phẩm:</b></p>';
        foreach (get_terms(['taxonomy' => 'product_cat', 'parent' => 0, 'hide_empty' => false]) as $t) {
            if ($t->slug === 'uncategorized') continue;
            echo '<label style="display:block;margin:4px 0"><input type="checkbox" name="cbv_groups[]" value="' . (int) $t->term_id . '"' . checked(in_array($t->term_id, $groups), true, false) . '> ' . esc_html($t->name) . '</label>';
        }
        echo '<p class="description">Ảnh bìa: đặt ở khung "Ảnh đại diện" bên phải (nên dùng ảnh trang bìa catalogue). Thứ tự hiển thị: ô "Thứ tự" (số nhỏ đứng trước).</p>';
    }, 'cbv_catalog', 'normal', 'high');
});
add_action('save_post_cbv_catalog', function ($id) {
    if (!isset($_POST['_cbvcat']) || !wp_verify_nonce($_POST['_cbvcat'], 'cbv_cat_save') || !current_user_can('edit_post', $id)) return;
    update_post_meta($id, '_cbv_file', (int) ($_POST['cbv_file'] ?? 0));
    update_post_meta($id, '_cbv_groups', array_map('intval', (array) ($_POST['cbv_groups'] ?? [])));
});
add_action('admin_enqueue_scripts', function () {
    $s = get_current_screen(); if (!$s || $s->post_type !== 'cbv_catalog') return;
    wp_enqueue_media();
    wp_add_inline_script('jquery-core', "jQuery(function($){\$('#cbv_pick').on('click',function(){var f=wp.media({title:'Chọn file PDF catalogue',multiple:false,library:{type:'application/pdf'}});f.on('select',function(){var a=f.state().get('selection').first().toJSON();\$('#cbv_file').val(a.id);\$('#cbv_fname').text(a.filename);});f.open();});});");
});
add_filter('manage_cbv_catalog_posts_columns', function ($c) { return array_slice($c, 0, 2) + ['cbv_g' => 'Nhóm sản phẩm', 'cbv_f' => 'File', 'cbv_n' => 'Lượt tải'] + $c; });
add_action('manage_cbv_catalog_posts_custom_column', function ($c, $id) {
    if ($c === 'cbv_g') echo esc_html(implode(', ', array_map(function ($t) { $x = get_term($t); return $x && !is_wp_error($x) ? $x->name : ''; }, (array) get_post_meta($id, '_cbv_groups', true))));
    if ($c === 'cbv_f') { $f = (int) get_post_meta($id, '_cbv_file', true); echo $f ? esc_html(basename(get_attached_file($f))) : '<b style="color:#b00">Chưa có file</b>'; }
    if ($c === 'cbv_n') echo (int) get_post_meta($id, '_cbv_count', true);
}, 10, 2);

/* ---------- Dữ liệu cho giao diện ---------- */
function cbv_catalogs_for($term_id) {
    if (!$term_id) return [];
    $q = get_posts(['post_type' => 'cbv_catalog', 'post_status' => 'publish', 'numberposts' => 20, 'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC']]);
    return array_values(array_filter($q, function ($p) use ($term_id) {
        return in_array($term_id, (array) get_post_meta($p->ID, '_cbv_groups', true)) && get_post_meta($p->ID, '_cbv_file', true);
    }));
}
function cbv_cat_size($p) { $f = get_attached_file((int) get_post_meta($p->ID, '_cbv_file', true)); return $f && file_exists($f) ? size_format(filesize($f), 1) : ''; }

/* ---------- Gửi form -> lưu khách + trả link tải có hạn ---------- */
function cbv_cat_token($id, $exp) { return hash_hmac('sha256', $id . '|' . $exp, wp_salt('auth')); }
function cbv_cat_bad_phone($digits) {
    if (!preg_match('/^0(3|5|7|8|9)\d{8}$/', $digits)) return true;
    if (preg_match('/(\d)\1{6}/', $digits)) return true;
    for ($i = 0; $i <= strlen($digits) - 7; $i++) { $s = substr($digits, $i, 7); $up = $dn = true;
        for ($j = 1; $j < 7; $j++) { if ((int) $s[$j] !== ((int) $s[$j - 1] + 1) % 10) $up = false; if ((int) $s[$j] !== ((int) $s[$j - 1] + 9) % 10) $dn = false; }
        if ($up || $dn) return true; }
    return false;
}
function cbv_cat_norm_phone($p) {
    $d = preg_replace('/\D/', '', $p);
    if (strpos($d, '84') === 0 && strlen($d) === 11) $d = '0' . substr($d, 2);
    if (strlen($d) === 9) $d = '0' . $d;
    $old = ['0162' => '032', '0163' => '033', '0164' => '034', '0165' => '035', '0166' => '036', '0167' => '037', '0168' => '038', '0169' => '039', '0120' => '070', '0121' => '079', '0122' => '077', '0126' => '076', '0128' => '078', '0123' => '083', '0124' => '084', '0125' => '085', '0127' => '081', '0129' => '082', '0186' => '056', '0188' => '058', '0199' => '059'];
    if (strlen($d) === 11 && isset($old[substr($d, 0, 4)])) $d = $old[substr($d, 0, 4)] . substr($d, 4);
    return $d;
}
add_action('wp_ajax_cbv_catalog', 'cbv_catalog_submit');
add_action('wp_ajax_nopriv_cbv_catalog', 'cbv_catalog_submit');
function cbv_catalog_submit() {
    $ids = array_filter(array_map('intval', (array) ($_POST['ids'] ?? [])));
    $cats = array_values(array_filter(array_map('get_post', $ids), function ($p) { return $p && $p->post_type === 'cbv_catalog' && $p->post_status === 'publish' && get_post_meta($p->ID, '_cbv_file', true); }));
    if (!$cats) wp_send_json_error(['msg' => 'Vui lòng chọn ít nhất 1 catalogue.']);
    // Chặn máy: ô bẫy, gửi quá nhanh (dưới 3 giây) -> trả "thành công" giả, không gửi gì
    if (!empty($_POST['website']) || empty($_POST['t0']) || (time() - (int) $_POST['t0']) < 3) wp_send_json_success(['msg' => 'Catalogue đã được gửi tới email của bạn.']);
    if (empty($_POST['human'])) wp_send_json_error(['msg' => 'Vui lòng tick "Tôi không phải người máy".']);
    $name = trim(sanitize_text_field($_POST['name'] ?? '')); $email = sanitize_email($_POST['email'] ?? '');
    $org = sanitize_text_field($_POST['org'] ?? ''); $phone = cbv_cat_norm_phone($_POST['phone'] ?? '');
    if (mb_strlen($name) < 2 || preg_match('#https?://|www\.|[\p{L}]\d|\d[\p{L}]#u', $name)) wp_send_json_error(['msg' => 'Vui lòng nhập họ tên đầy đủ.', 'f' => 'name']);
    if (cbv_cat_bad_phone($phone)) wp_send_json_error(['msg' => 'Số điện thoại chưa đúng (di động Việt Nam 10 số).', 'f' => 'phone']);
    if (!is_email($email)) wp_send_json_error(['msg' => 'Email chưa đúng.', 'f' => 'email']);
    $ip = $_SERVER['REMOTE_ADDR'] ?? ''; $k = 'cbv_cat_rl_' . md5($ip); $n = (int) get_transient($k);
    if ($n >= 3) wp_send_json_error(['msg' => 'Bạn gửi quá nhanh, vui lòng thử lại sau 1 phút.']);
    set_transient($k, $n + 1, 60);

    // File đính kèm: mỗi file <= 10MB và tổng <= 15MB; file lớn hơn gửi bằng link tải (hiệu lực 7 ngày)
    $att = []; $links = []; $total = 0; $exp = time() + 7 * DAY_IN_SECONDS;
    foreach ($cats as $c) {
        $f = get_attached_file((int) get_post_meta($c->ID, '_cbv_file', true)); $sz = $f && file_exists($f) ? filesize($f) : 0;
        if ($sz && $sz <= 10 * MB_IN_BYTES && $total + $sz <= 15 * MB_IN_BYTES) {
            $tmp = trailingslashit(get_temp_dir()) . sanitize_file_name($c->post_title) . '.pdf'; copy($f, $tmp); $att[] = $tmp; $total += $sz;
        }
        $links[] = ['t' => $c->post_title, 'u' => add_query_arg(['cbv_dl' => $c->ID, 'e' => $exp, 'k' => cbv_cat_token($c->ID, $exp)], home_url('/')), 'a' => in_array($tmp ?? '', $att, true)];
        update_post_meta($c->ID, '_cbv_count', (int) get_post_meta($c->ID, '_cbv_count', true) + 1);
        unset($tmp);
    }
    $hot = function_exists('cbv_opt') ? cbv_opt('hotline') : ''; $zalo = function_exists('cbv_opt') ? cbv_opt('phone') : '';
    $li = '';
    foreach ($links as $l) $li .= '<li style="margin:6px 0"><b>' . esc_html($l['t']) . '</b>' . ($l['a'] ? ' (đính kèm trong email)' : '') . ' - <a href="' . esc_url($l['u']) . '" style="color:#E57600">Tải file PDF</a></li>';
    $html = '<div style="font:15px/1.6 Arial,sans-serif;color:#222;max-width:600px">'
        . '<p>Chào ' . esc_html($name) . ',</p>'
        . '<p>Cảm ơn bạn đã quan tâm đến sản phẩm của ICD Cabin Technology. Catalogue bạn yêu cầu:</p><ul>' . $li . '</ul>'
        . '<p style="font-size:13px;color:#666">Link tải có hiệu lực 7 ngày.</p>'
        . '<p>Cần tư vấn chọn mẫu, kích thước hoặc báo giá, vui lòng gọi <b>' . esc_html($hot) . '</b>' . ($zalo ? ' hoặc Zalo <b>' . esc_html($zalo) . '</b>' : '') . '.</p>'
        . '<p>Trân trọng,<br><b>ICD Cabin Technology</b><br><a href="' . esc_url(home_url('/')) . '">' . esc_html(wp_parse_url(home_url(), PHP_URL_HOST)) . '</a></p></div>';
    $titles = implode(', ', wp_list_pluck($cats, 'post_title'));
    $hdr = ['Content-Type: text/html; charset=UTF-8'];
    $staff = (function_exists('cbv_opt') ? cbv_opt('email') : '') ?: get_option('admin_email');
    if ($staff) $hdr[] = 'Reply-To: ICD Cabin <' . $staff . '>';
    $sent = wp_mail($email, 'Catalogue ICD Cabin: ' . $titles, $html, $hdr, $att);
    foreach ($att as $t) @unlink($t);

    $page = esc_url_raw(wp_get_referer() ?: '');
    $body = "Họ tên: $name\nĐiện thoại: $phone\nEmail: $email\nCông ty/Đơn vị: $org\nCatalogue: $titles\nĐã gửi email: " . ($sent ? 'có' : 'LỖI GỬI') . "\nTrang: $page\nThời gian: " . wp_date('d/m/Y H:i');
    wp_insert_post(['post_type' => 'tai_catalog', 'post_status' => 'publish', 'post_title' => "$name - $phone - $titles", 'post_content' => $body]);
    wp_mail($staff, "[Tải catalogue] $name - $phone - $titles", $body);
    if (!$sent) wp_send_json_error(['msg' => 'Chưa gửi được email. Vui lòng gọi ' . $hot . ' để nhận catalogue.']);
    wp_send_json_success(['msg' => 'Catalogue đã được gửi tới ' . $email . '. Vui lòng kiểm tra hộp thư (cả mục Spam/Quảng cáo).']);
}

/* ---------- Link tải: chỉ chạy khi token đúng và còn hạn ---------- */
add_action('template_redirect', function () {
    if (!isset($_GET['cbv_dl'])) return;
    $id = (int) $_GET['cbv_dl']; $exp = (int) ($_GET['e'] ?? 0);
    if ($exp < time() || !hash_equals(cbv_cat_token($id, $exp), (string) ($_GET['k'] ?? ''))) { wp_die('Link tải đã hết hạn. Vui lòng vào website và bấm Tải catalogue để nhận link mới.', 'Link hết hạn', ['response' => 403, 'back_link' => true]); }
    $f = get_attached_file((int) get_post_meta($id, '_cbv_file', true));
    if (!$f || !file_exists($f)) wp_die('Không tìm thấy file.', '', ['response' => 404]);
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/pdf'); header('Content-Length: ' . filesize($f));
    header('Content-Disposition: attachment; filename="' . sanitize_file_name(get_the_title($id)) . '.pdf"');
    header('X-Robots-Tag: noindex'); nocache_headers();
    readfile($f); exit;
}, 1);

/* Tên người gửi email của web: ICD Cabin Technology (thay "WordPress") */
add_filter('wp_mail_from_name', function ($n) { return $n === 'WordPress' ? 'ICD Cabin Technology' : $n; });
