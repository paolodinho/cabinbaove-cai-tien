<?php
/** Trang "Cài đặt ICD Cabin": mọi thông tin liên hệ, slider, các hàng sản phẩm trang chủ sửa ở đây, không cần sửa code. */
if (!defined('ABSPATH')) exit;

function cbv_defaults() {
    return [
        'hotline'   => '024 2200 9188',
        'phone'     => '0935 482 688',
        'zalo'      => '0935482688',
        'email'     => 'khachhang.icd@gmail.com',
        'brand_line'=> 'ICD CABINTECH - Chuyên sản xuất, cung cấp cabin bốt bảo vệ, kiểm soát ra vào, cabin thu phí giao thông trạm BOT',
        'addr_hn'   => 'Số 55 cụm 5 đường Anh Dũng, Thiên Lộc, Hà Nội',
        'factory'   => 'KCN Bắc Thăng Long, Thiên Lộc, Hà Nội',
        'addr_hcm'  => '551/212 Lê Văn Khương, khu phố 7, Phường Tân Thới Hiệp, TP. Hồ Chí Minh',
        'facebook'  => 'https://www.facebook.com/cabinbaove.com.vn',
        'youtube'   => 'https://www.youtube.com/channel/UC-rvQ0OKVUZ9uiAf-PEejmQ',
        'map'       => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3723.674823374124!2d105.7806757149329!3d21.045693285989277!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135abc051a0b1cd%3A0xa6a3a8106000c742!2zSUNELVPhuqNuIHh14bqldCB2w6AgbOG6r3AgxJHhurd0IENhYmluIGLhuqNvIHbhu4csIGNhYmluIGtp4buDbSBzb8OhdCByYSB2w6Bv!5e0!3m2!1svi!2s!4v1632362154437!5m2!1svi!2s',
        'slides'    => "/uploads/images/cabinbaove.com.jpg | /Cabin-thu-phi-giao-thong-BOT.html | Cabin thu phí giao thông, quốc lộ BOT\n/uploads/images/cabinbaove.com-2.jpg | /cabin-nha-bao-ve-inox.html | Cabin nhà bảo vệ Inox\n/uploads/images/cabinbaove.com-1.jpg | /nha-ve-sinh-cong-cong.html | Nhà vệ sinh công cộng",
        'home_rows' => "cabin-nha-bao-ve-inox | 8\nchoi-gac-bao-ve-thep | 8\nCabin-thu-phi-giao-thong-BOT | 8\nnha-ve-sinh-cong-cong | 8",
        'commit'    => "Nhà máy sản xuất tại KCN Bắc Thăng Long\nThiết kế theo bản vẽ, kích thước riêng\nVận chuyển, lắp đặt trên toàn quốc\nBảo hành, bảo trì sau bàn giao",
        'sidebar_ad'=> '/uploads/images/quang-cao-thung-rac-copy.jpg | https://dailythungrac.com/',
    ];
}
function cbv_opt($k) {
    $o = wp_parse_args(get_option('cbv_opts', []), cbv_defaults());
    return $o[$k] ?? '';
}
function cbv_lines($k) {
    $out = [];
    foreach (preg_split('/\r?\n/', (string) cbv_opt($k)) as $l) if (trim($l) !== '') $out[] = array_map('trim', explode('|', $l));
    return $out;
}
function cbv_tel($n) { return 'tel:' . preg_replace('/[^0-9+]/', '', $n); }

add_action('admin_menu', function () {
    add_menu_page('Cài đặt ICD Cabin', 'Cài đặt ICD Cabin', 'manage_options', 'cbv-settings', 'cbv_settings_page', 'dashicons-admin-generic', 3);
});
add_action('admin_init', function () {
    register_setting('cbv', 'cbv_opts', ['sanitize_callback' => function ($in) {
        $out = [];
        foreach (cbv_defaults() as $k => $v) $out[$k] = isset($in[$k]) ? (in_array($k, ['map'], true) ? esc_url_raw($in[$k]) : sanitize_textarea_field($in[$k])) : $v;
        return $out;
    }]);
});
function cbv_settings_page() {
    $f = [
        'Liên hệ' => ['hotline' => 'Hotline (tổng đài)', 'phone' => 'Số điện thoại di động', 'zalo' => 'Số Zalo (viết liền)', 'email' => 'Email nhận báo giá', 'brand_line' => 'Dòng giới thiệu ở chân trang',
                      'addr_hn' => 'Trụ sở Hà Nội', 'factory' => 'Nhà máy sản xuất', 'addr_hcm' => 'Chi nhánh Hồ Chí Minh', 'facebook' => 'Link Facebook', 'youtube' => 'Link YouTube', 'map' => 'Link nhúng Google Maps (trang Liên hệ)'],
        'Trang chủ' => ['slides' => 'Ảnh slider: mỗi dòng "đường dẫn ảnh | link | mô tả ảnh"', 'home_rows' => 'Các hàng sản phẩm: mỗi dòng "đường dẫn danh mục | số sản phẩm"',
                        'commit' => 'Dải cam kết dưới slider: mỗi dòng 1 ý', 'sidebar_ad' => 'Banner cột trái ở trang tin tức, giới thiệu (trang sản phẩm dùng banner riêng của từng nhóm, sửa trong Sản phẩm > Danh mục): "đường dẫn ảnh | link"'],
    ];
    $o = wp_parse_args(get_option('cbv_opts', []), cbv_defaults());
    echo '<div class="wrap"><h1>Cài đặt ICD Cabin</h1><p>Sửa thông tin hiển thị trên toàn website. Bấm "Lưu thay đổi" ở cuối trang.</p><form method="post" action="options.php">';
    settings_fields('cbv');
    foreach ($f as $group => $fields) {
        echo '<h2>' . esc_html($group) . '</h2><table class="form-table">';
        foreach ($fields as $k => $label) {
            $multi = in_array($k, ['slides', 'home_rows', 'commit'], true);
            echo '<tr><th><label for="cbv_' . $k . '">' . esc_html($label) . '</label></th><td>';
            echo $multi ? '<textarea class="large-text" rows="5" id="cbv_' . $k . '" name="cbv_opts[' . $k . ']">' . esc_textarea($o[$k]) . '</textarea>'
                        : '<input class="large-text" id="cbv_' . $k . '" name="cbv_opts[' . $k . ']" value="' . esc_attr($o[$k]) . '">';
            echo '</td></tr>';
        }
        echo '</table>';
    }
    submit_button('Lưu thay đổi');
    echo '</form></div>';
}
