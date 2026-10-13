<?php get_header(); the_post(); $contact = is_page('lien-he'); ?>
<div class="wrap <?= $contact ? 'contact' : 'layout' ?>">
  <?php if (!$contact) get_template_part('template-parts/sidebar'); ?>
  <div class="<?= $contact ? '' : 'layout__main' ?>">
    <h1 class="page-head__h"><?php the_title(); ?></h1>
    <?php if ($contact): ?>
      <div class="contact__grid">
        <div class="contact__info">
          <p>Cảm ơn quý khách đã quan tâm đến sản phẩm của ICD CABIN TECHNOLOGY. Chúng tôi chuyên thiết kế, sản xuất và lắp đặt cabin, bốt gác bảo vệ cho cơ quan, tòa nhà, khu đô thị và cabin thu phí trên các tuyến quốc lộ BOT.</p>
          <ul class="footer__list">
            <li><b>Trụ sở Hà Nội:</b> <?= esc_html(cbv_opt('addr_hn')) ?></li>
            <li><b>Nhà máy sản xuất:</b> <?= esc_html(cbv_opt('factory')) ?></li>
            <li><b>Chi nhánh Hồ Chí Minh:</b> <?= esc_html(cbv_opt('addr_hcm')) ?></li>
            <li><b>Tổng đài:</b> <a href="<?= esc_attr(cbv_tel(cbv_opt('hotline'))) ?>"><?= esc_html(cbv_opt('hotline')) ?></a> - <a href="<?= esc_attr(cbv_tel(cbv_opt('phone'))) ?>"><?= esc_html(cbv_opt('phone')) ?></a></li>
            <li><b>Email:</b> <a href="mailto:<?= esc_attr(cbv_opt('email')) ?>"><?= esc_html(cbv_opt('email')) ?></a></li>
          </ul>
          <div class="entry"><?php the_content(); ?></div>
        </div>
        <?php if (cbv_opt('map')): ?><iframe class="contact__map" src="<?= esc_url(cbv_opt('map')) ?>" title="Bản đồ ICD Cabin" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe><?php endif; ?>
      </div>
      <?php get_template_part('template-parts/quote-form'); ?>
    <?php else: ?>
      <div class="entry"><?php the_content(); ?></div>
    <?php endif; ?>
  </div>
</div>
<?php get_footer();
