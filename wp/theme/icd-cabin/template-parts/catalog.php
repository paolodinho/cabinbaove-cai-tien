<?php
/** Hộp "Tải catalogue": khách điền thông tin, catalogue được gửi về email. File quản lý ở menu Catalogue (plugin cbv-catalog). In ở chân trang. */
if (!function_exists('cbv_catalogs_for')) return;
$grp = cbv_current_group();
$list = $grp ? cbv_catalogs_for($grp->term_id) : [];
if (!$list) return; ?>
<dialog class="catm" id="catm" aria-labelledby="catm-h">
  <form class="catm__f" data-ajax="<?= esc_url(admin_url('admin-ajax.php')) ?>" novalidate>
    <button type="button" class="catm__x" data-close aria-label="Đóng">&times;</button>
    <h2 id="catm-h" class="catm__h">Tải catalogue</h2>
    <p class="catm__sub">Nhập thông tin, catalogue PDF sẽ được gửi ngay về email của bạn.</p>
    <input type="hidden" name="action" value="cbv_catalog"><input type="hidden" name="t0">
    <div class="hp" aria-hidden="true"><input name="website" tabindex="-1" autocomplete="off"></div>
    <fieldset class="catm__list">
      <legend><?= count($list) > 1 ? 'Chọn catalogue' : 'Catalogue' ?></legend>
      <?php foreach ($list as $c): $sz = cbv_cat_size($c); ?>
      <label class="catm__chk"><input type="checkbox" name="ids[]" value="<?= (int) $c->ID ?>" checked> <span><?= esc_html($c->post_title) ?><?= $sz ? ' <small>(PDF ' . esc_html($sz) . ')</small>' : '' ?></span></label>
      <?php endforeach; ?>
    </fieldset>
    <label><span>Họ và tên *</span><input name="name" required autocomplete="name"></label>
    <label><span>Số điện thoại *</span><input name="phone" type="tel" required inputmode="tel" autocomplete="tel"></label>
    <label><span>Email nhận catalogue *</span><input name="email" type="email" required autocomplete="email"></label>
    <label><span>Công ty / đơn vị</span><input name="org" autocomplete="organization"></label>
    <label class="catm__chk"><input type="checkbox" name="human" value="1"> <span>Tôi không phải người máy</span></label>
    <p class="msg" data-msg hidden></p>
    <button class="btn btn--cta catm__go" type="submit">Gửi catalogue về email</button>
    <p class="catm__note">Thông tin chỉ dùng để ICD Cabin gửi tài liệu và tư vấn sản phẩm.</p>
  </form>
</dialog>
