</main>
<?php if (!is_page('lien-he')) get_template_part('template-parts/quote-form'); ?>
<footer class="footer">
  <div class="wrap footer__grid">
    <div class="footer__col footer__col--info">
      <h2 class="footer__h">Thông tin liên hệ</h2>
      <p class="footer__brand"><?= esc_html(cbv_opt('brand_line')) ?></p>
      <ul class="footer__list">
        <li><b>Trụ sở Hà Nội:</b> <?= esc_html(cbv_opt('addr_hn')) ?></li>
        <li><b>Nhà máy sản xuất:</b> <?= esc_html(cbv_opt('factory')) ?></li>
        <li><b>Chi nhánh Hồ Chí Minh:</b> <?= esc_html(cbv_opt('addr_hcm')) ?></li>
        <li><b>Hotline:</b> <a href="<?= esc_attr(cbv_tel(cbv_opt('hotline'))) ?>"><?= esc_html(cbv_opt('hotline')) ?></a> - <a href="<?= esc_attr(cbv_tel(cbv_opt('phone'))) ?>"><?= esc_html(cbv_opt('phone')) ?></a></li>
        <li><b>Email:</b> <a href="mailto:<?= esc_attr(cbv_opt('email')) ?>"><?= esc_html(cbv_opt('email')) ?></a></li>
      </ul>
    </div>
    <div class="footer__col">
      <h2 class="footer__h"><a href="<?= esc_url(get_permalink(wc_get_page_id('shop'))) ?>">Danh mục sản phẩm</a></h2>
      <ul class="footer__links">
        <?php foreach (cbv_product_cats() as $t) if ($t->slug !== 'Cabin-bao-ve-Bot-gac-bao-ve-pc20') echo '<li><a href="' . esc_url(get_term_link($t)) . '">' . esc_html($t->name) . '</a></li>'; ?>
      </ul>
    </div>
    <div class="footer__col">
      <h2 class="footer__h">Về ICD Cabin</h2>
      <ul class="footer__links">
        <li><a href="<?= esc_url(home_url('/gioi-thieu-cong-ty.html')) ?>">Giới thiệu công ty</a></li>
        <li><a href="<?= esc_url(home_url('/Dich-vu-cua-chung-toi.html')) ?>">Dịch vụ của chúng tôi</a></li>
        <li><a href="<?= esc_url(home_url('/hoat-dong.html')) ?>">Hoạt động, dự án</a></li>
        <li><a href="<?= esc_url(home_url('/tin-tuc.html')) ?>">Tin tức</a></li>
        <li><a href="<?= esc_url(home_url('/Tuyen-Dung.html')) ?>">Tuyển dụng</a></li>
        <li><a href="<?= esc_url(home_url('/lien-he.html')) ?>">Liên hệ</a></li>
      </ul>
      <div class="footer__social">
        <?php if (cbv_opt('facebook')): ?><a href="<?= esc_url(cbv_opt('facebook')) ?>" target="_blank" rel="noopener">Facebook</a><?php endif; ?>
        <?php if (cbv_opt('youtube')): ?><a href="<?= esc_url(cbv_opt('youtube')) ?>" target="_blank" rel="noopener">YouTube</a><?php endif; ?>
        <a href="https://zalo.me/<?= esc_attr(cbv_opt('zalo')) ?>" target="_blank" rel="noopener">Zalo</a>
      </div>
    </div>
  </div>
  <div class="footer__bottom"><div class="wrap">© <?= date('Y') ?> ICD CABINTECH - Công ty CP Phát triển Công nghiệp và Đô thị Việt Nam.</div></div>
</footer>
<div class="float" aria-label="Liên hệ nhanh">
  <a class="float__btn float__btn--call" href="<?= esc_attr(cbv_tel(cbv_opt('hotline'))) ?>"><svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true"><path fill="currentColor" d="M6.6 10.8a15 15 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1z"/></svg><span><?= esc_html(cbv_opt('hotline')) ?></span></a>
  <a class="float__btn float__btn--zalo" href="https://zalo.me/<?= esc_attr(cbv_opt('zalo')) ?>" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><path fill="currentColor" fill-rule="evenodd" d="M12 2.5C6.75 2.5 2.5 6.25 2.5 10.9c0 2.55 1.27 4.83 3.27 6.37L5 21.5l4.05-2.1c.9.24 1.9.38 2.95.38 5.25 0 9.5-3.75 9.5-8.4S17.25 2.5 12 2.5zM7.7 7.9h8.6v1.5l-5.9 4.6h5.9v1.6H7.7v-1.5l5.9-4.6H7.7V7.9z"/></svg><span>Zalo</span></a>
  <a class="float__btn float__btn--quote" href="#bao-gia"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm8 1.5V8h4.5L14 3.5zM8 12h8v1.5H8V12zm0 3.5h8V17H8v-1.5z"/></svg><span>Báo giá</span></a>
  <button class="float__top" id="totop" aria-label="Lên đầu trang"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 7l7 7-1.4 1.4L12 9.8l-5.6 5.6L5 14z"/></svg></button>
</div>
<?php wp_footer(); ?>
</body>
</html>
