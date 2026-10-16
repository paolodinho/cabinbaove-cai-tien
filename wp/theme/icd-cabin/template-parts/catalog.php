<?php
/** Khối "Tải catalogue" của nhóm sản phẩm đang xem. File quản lý ở menu Catalogue (plugin cbv-catalog). */
if (!function_exists('cbv_catalogs_for')) return;
$grp = cbv_current_group();
$list = $grp ? cbv_catalogs_for($grp->term_id) : [];
if (!$list) return;
// Bản demo tĩnh (GitHub Pages) không có máy chủ nhận form: chỉ khi xuất demo mới ghi đường dẫn file để thử luồng tải.
$demo = stripos($_SERVER['HTTP_USER_AGENT'] ?? '', 'wget') !== false; ?>
<section class="cat" id="catalogue">
  <header class="sec-head"><h2 class="sec-head__h">Tải catalogue <?= esc_html(mb_strtolower($grp->name)) ?></h2></header>
  <p class="cat__lead">Catalogue PDF gồm hình ảnh, thông số và các mẫu tiêu biểu. Để lại thông tin để tải file, kỹ sư ICD Cabin có thể liên hệ hỗ trợ chọn mẫu khi bạn cần.</p>
  <div class="cat__grid">
    <?php foreach ($list as $c): $sz = cbv_cat_size($c); ?>
    <article class="cat__card">
      <div class="cat__cover"><?php if (has_post_thumbnail($c)) echo get_the_post_thumbnail($c, 'medium_large', ['loading' => 'lazy', 'alt' => esc_attr($c->post_title)]); else echo '<span class="cat__pdf" aria-hidden="true">PDF</span>'; ?></div>
      <div class="cat__body">
        <h3 class="cat__t"><?= esc_html($c->post_title) ?></h3>
        <p class="cat__meta">PDF<?= $sz ? ' · ' . esc_html($sz) : '' ?></p>
        <button type="button" class="btn btn--cta cat__btn" data-cat="<?= (int) $c->ID ?>" data-title="<?= esc_attr($c->post_title) ?>"<?= $demo ? ' data-demo="' . esc_url(wp_get_attachment_url((int) get_post_meta($c->ID, '_cbv_file', true))) . '"' : '' ?>>Tải catalogue</button>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<dialog class="catm" id="catm" aria-labelledby="catm-h">
  <form class="catm__f" data-ajax="<?= esc_url(admin_url('admin-ajax.php')) ?>" novalidate>
    <button type="button" class="catm__x" data-close aria-label="Đóng">&times;</button>
    <h2 id="catm-h" class="catm__h">Tải catalogue</h2>
    <p class="catm__t" data-ttl></p>
    <input type="hidden" name="action" value="cbv_catalog"><input type="hidden" name="id"><input type="hidden" name="t0">
    <div class="hp" aria-hidden="true"><input name="website" tabindex="-1" autocomplete="off"></div>
    <label><span>Họ và tên *</span><input name="name" required autocomplete="name"></label>
    <label><span>Số điện thoại *</span><input name="phone" type="tel" required inputmode="tel" autocomplete="tel"></label>
    <label><span>Email *</span><input name="email" type="email" required autocomplete="email"></label>
    <label><span>Công ty / đơn vị</span><input name="org" autocomplete="organization"></label>
    <label class="catm__chk"><input type="checkbox" name="human" value="1"> Tôi không phải người máy</label>
    <p class="msg" data-msg hidden></p>
    <button class="btn btn--cta catm__go" type="submit">Nhận file catalogue</button>
    <p class="catm__note">Thông tin chỉ dùng để ICD Cabin gửi tài liệu và tư vấn sản phẩm.</p>
  </form>
</dialog>
