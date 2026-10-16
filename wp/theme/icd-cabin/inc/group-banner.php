<?php
/**
 * Banner quảng cáo riêng cho từng nhóm sản phẩm (cột trái desktop, cuối trang mobile).
 * Sửa trong: Sản phẩm > Danh mục > (chọn nhóm cấp 1) > khối "Banner quảng cáo của nhóm".
 * - "Ảnh banner thiết kế sẵn": nếu có, hiện nguyên ảnh này (ảnh dọc khoảng 270x400 trở lên).
 * - Nếu để trống: banner tự dựng từ ảnh sản phẩm + 2 dòng chữ + hotline.
 */
if (!defined('ABSPATH')) exit;

function cbv_banner_defaults() {
    return [
        'Cabin-bao-ve-Bot-gac-bao-ve-pc20'        => ['photo' => 211, 'sub' => 'Inox - Thép - Composite', 'note' => 'Thiết kế theo bản vẽ, kích thước riêng'],
        'Cabin-thu-phi-giao-thong-BOT'            => ['photo' => 254, 'sub' => 'Cao tốc, quốc lộ, cảng hàng không', 'note' => 'Thiết kế theo bản vẽ, kích thước riêng'],
        'Barrier-kiem-soat-ra-vao'                => ['photo' => 318, 'sub' => 'Cần thẳng, cần gập, cổng trượt tự động', 'note' => 'Lắp đặt tại công trình trên toàn quốc'],
        'nha-ve-sinh-cong-cong'                   => ['photo' => 261, 'sub' => 'Inox, composite, container, di động', 'note' => 'Bán và cho thuê theo sự kiện, công trình'],
        'Nha-di-dong-nha-phi-thuyen-nha-lap-ghep' => ['photo' => 280, 'sub' => 'Nhà phi thuyền, nhà lắp ghép F10 - F40', 'note' => 'Thiết kế theo bản vẽ, kích thước riêng'],
        'Xe-ban-hang-luu-dong'                    => ['photo' => 289, 'sub' => 'Xe điện 3 - 4 bánh, xe kéo, ki-ốt', 'note' => 'Thiết kế theo mô hình kinh doanh'],
    ];
}

/** Nhóm sản phẩm cấp 1 của trang đang xem (danh mục hoặc sản phẩm), không có thì null. */
function cbv_current_group() {
    $term = null;
    if (is_tax('product_cat')) $term = get_queried_object();
    elseif (is_singular('product')) {
        foreach ((array) get_the_terms(get_the_ID(), 'product_cat') as $t) if ($t && $t->slug !== 'uncategorized') { $term = $t; break; }
    }
    if (!$term) return null;
    while ($term->parent) $term = get_term($term->parent, 'product_cat');
    return $term->slug === 'uncategorized' ? null : $term;
}

function cbv_group_banner($term) {
    $d = cbv_banner_defaults()[$term->slug] ?? ['photo' => 0, 'sub' => '', 'note' => 'Thiết kế theo bản vẽ, kích thước riêng'];
    $img = get_term_meta($term->term_id, 'cbv_ban_img', true);
    if ($img) {
        return '<a class="side__ad" href="#bao-gia"><img src="' . esc_url($img) . '" alt="' . esc_attr($term->name . ' - ICD Cabin Technology') . '" loading="lazy" width="270" height="400"></a>';
    }
    $photo = get_term_meta($term->term_id, 'cbv_ban_photo', true) ?: wp_get_attachment_image_url($d['photo'], 'medium_large');
    $sub   = get_term_meta($term->term_id, 'cbv_ban_sub', true) ?: $d['sub'];
    $note  = get_term_meta($term->term_id, 'cbv_ban_note', true) ?: $d['note'];
    $hot   = cbv_opt('hotline');
    ob_start(); ?>
<div class="gban">
  <div class="gban__top">
    <img class="gban__logo" src="<?= esc_url(get_template_directory_uri()) ?>/assets/img/logo.png" alt="ICD Cabin Technology" width="64" height="48" loading="lazy">
    <p class="gban__brand">ICD CABIN TECHNOLOGY</p>
    <p class="gban__k">Sản xuất &amp; lắp đặt</p>
    <p class="gban__name"><?= esc_html($term->name) ?></p>
    <?php if ($sub): ?><p class="gban__sub"><?= esc_html($sub) ?></p><?php endif; ?>
    <?php if ($note): ?><p class="gban__note"><?= esc_html($note) ?></p><?php endif; ?>
    <p class="gban__k gban__k--call">Liên hệ báo giá:</p>
    <a class="gban__tel" href="<?= esc_attr(cbv_tel($hot)) ?>"><?= esc_html($hot) ?></a>
  </div>
  <?php if ($photo): ?><div class="gban__ph"><img src="<?= esc_url($photo) ?>" alt="<?= esc_attr($term->name) ?> ICD" loading="lazy" width="270" height="270"></div><?php endif; ?>
  <a class="gban__cta" href="#bao-gia">Nhận báo giá</a>
  <?php if (function_exists('cbv_catalogs_for') && cbv_catalogs_for($term->term_id)): ?><a class="gban__cat" href="#catalogue">Tải catalogue PDF</a><?php endif; ?>
</div>
<?php return ob_get_clean();
}

/* Ô nhập trong trang sửa danh mục sản phẩm cấp 1 */
add_action('product_cat_edit_form_fields', function ($t) {
    if ($t->parent) return;
    $f = ['cbv_ban_img' => ['Ảnh banner thiết kế sẵn (không bắt buộc)', 'Có ảnh này thì hiện nguyên ảnh, bỏ qua banner tự dựng. Ảnh dọc, rộng từ 540px.', 1],
          'cbv_ban_photo' => ['Ảnh sản phẩm trong banner tự dựng', 'Để trống = dùng ảnh mặc định của nhóm.', 1],
          'cbv_ban_sub' => ['Dòng mô tả (dưới tên nhóm)', 'Ví dụ: Inox - Thép - Composite', 0],
          'cbv_ban_note' => ['Dòng cam kết', 'Ví dụ: Thiết kế theo bản vẽ, kích thước riêng', 0]];
    echo '<tr><th colspan="2"><h2 style="margin:16px 0 0">Banner quảng cáo của nhóm</h2></th></tr>';
    foreach ($f as $k => [$label, $hint, $isImg]) {
        $v = get_term_meta($t->term_id, $k, true);
        echo '<tr class="form-field"><th><label for="' . $k . '">' . esc_html($label) . '</label></th><td><input id="' . $k . '" name="' . $k . '" value="' . esc_attr($v) . '">';
        if ($isImg) echo ' <button type="button" class="button cbv-pick" data-for="' . $k . '">Chọn ảnh</button>';
        echo '<p class="description">' . esc_html($hint) . '</p></td></tr>';
    }
});
add_action('edited_product_cat', function ($id) {
    if (!current_user_can('manage_product_terms') && !current_user_can('manage_categories')) return;
    foreach (['cbv_ban_img' => 'esc_url_raw', 'cbv_ban_photo' => 'esc_url_raw', 'cbv_ban_sub' => 'sanitize_text_field', 'cbv_ban_note' => 'sanitize_text_field'] as $k => $fn)
        if (isset($_POST[$k])) update_term_meta($id, $k, $fn(wp_unslash($_POST[$k])));
});
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'term.php' || ($_GET['taxonomy'] ?? '') !== 'product_cat') return;
    wp_enqueue_media();
    wp_add_inline_script('jquery-core', "jQuery(function($){\$(document).on('click','.cbv-pick',function(){var t=\$('#'+\$(this).data('for'));var f=wp.media({title:'Chọn ảnh',multiple:false,library:{type:'image'}});f.on('select',function(){t.val(f.state().get('selection').first().toJSON().url)});f.open();});});");
});
