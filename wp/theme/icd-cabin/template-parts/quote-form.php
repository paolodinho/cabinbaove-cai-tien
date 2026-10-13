<?php $st = $_GET['bg'] ?? ''; $sp = is_singular('product') ? get_the_title() : ''; ?>
<section class="quote" id="bao-gia">
  <div class="wrap quote__in">
    <div class="quote__text">
      <h2>Nhận báo giá cabin theo yêu cầu</h2>
      <p>Để lại số điện thoại, kỹ sư ICD Cabin sẽ gọi lại tư vấn mẫu, kích thước và báo giá trong giờ làm việc.</p>
      <ul class="quote__commit">
        <?php foreach (cbv_lines('commit') as $l) echo '<li>' . esc_html($l[0]) . '</li>'; ?>
      </ul>
    </div>
    <form class="quote__form" method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>">
      <?php if ($st === 'ok'): ?><p class="msg msg--ok" role="status">Đã nhận yêu cầu. ICD Cabin sẽ liên hệ lại sớm.</p><?php elseif ($st === 'err'): ?><p class="msg msg--err" role="alert">Vui lòng nhập số điện thoại hợp lệ.</p><?php endif; ?>
      <input type="hidden" name="action" value="cbv_quote"><?php wp_nonce_field('cbv_quote', '_cbv'); ?>
      <div class="hp" aria-hidden="true"><input name="website" tabindex="-1" autocomplete="off"></div>
      <label><span>Họ và tên</span><input name="name" autocomplete="name"></label>
      <label><span>Số điện thoại *</span><input name="phone" type="tel" required inputmode="tel" autocomplete="tel" pattern="[0-9+ .]{9,}"></label>
      <label><span>Sản phẩm quan tâm</span><input name="sp" value="<?= esc_attr($sp) ?>"></label>
      <label class="full"><span>Nhu cầu (số lượng, kích thước, địa điểm lắp đặt)</span><textarea name="note" rows="3"></textarea></label>
      <button class="btn btn--cta full" type="submit">Gửi yêu cầu báo giá</button>
    </form>
  </div>
</section>
