<?php get_header(); the_post();
$id = get_the_ID(); $a = cbv_attrs($id);
$imgs = array_filter(array_merge([get_post_thumbnail_id($id)], array_map('intval', explode(',', (string) get_post_meta($id, '_product_image_gallery', true))))); ?>
<div class="wrap layout">
  <?php get_template_part('template-parts/sidebar'); ?>
  <div class="layout__main">
    <section class="prd">
      <div class="prd__gallery" data-gallery>
        <div class="prd__main">
          <?php if ($imgs) echo wp_get_attachment_image($imgs[0], 'large', false, ['alt' => esc_attr(get_the_title()), 'fetchpriority' => 'high', 'data-main' => '1']); else echo '<span class="noimg">Ảnh đang cập nhật</span>'; ?>
        </div>
        <?php if (count($imgs) > 1): ?>
        <div class="prd__thumbs">
          <?php foreach ($imgs as $i => $im) echo '<button class="' . ($i ? '' : 'is-on') . '" data-src="' . esc_url(wp_get_attachment_image_url($im, 'large')) . '" aria-label="Ảnh ' . ($i + 1) . '">' . wp_get_attachment_image($im, 'thumbnail', false, ['alt' => '', 'loading' => 'lazy']) . '</button>'; ?>
        </div>
        <?php endif; ?>
      </div>
      <div class="prd__info">
        <h1 class="prd__title"><?php the_title(); ?></h1>
        <p class="prd__price">Giá: <b>Liên hệ</b></p>
        <?php if ($a): ?>
        <dl class="prd__attrs"><?php foreach ($a as $k => $v) echo '<div><dt>' . esc_html($k) . '</dt><dd>' . esc_html($v) . '</dd></div>'; ?></dl>
        <?php endif; ?>
        <?php if (has_excerpt()): ?><div class="prd__ex"><?php the_excerpt(); ?></div><?php endif; ?>
        <div class="prd__cta">
          <a class="btn btn--cta" href="#bao-gia">Nhận báo giá</a>
          <a class="btn btn--call" href="<?= esc_attr(cbv_tel(cbv_opt('hotline'))) ?>">Gọi <?= esc_html(cbv_opt('hotline')) ?></a>
          <a class="btn btn--line" href="https://zalo.me/<?= esc_attr(cbv_opt('zalo')) ?>" target="_blank" rel="noopener">Tư vấn qua Zalo</a>
        </div>
      </div>
    </section>
    <section class="prd__content">
      <h2 class="tab-h">Thông tin chi tiết</h2>
      <div class="entry"><?php the_content(); ?></div>
    </section>
    <?php get_template_part('template-parts/catalog'); ?>
    <?php $cats = wp_get_post_terms($id, 'product_cat', ['fields' => 'ids']);
    $rel = new WP_Query(['post_type' => 'product', 'posts_per_page' => 6, 'post__not_in' => [$id], 'no_found_rows' => true, 'orderby' => 'menu_order', 'order' => 'ASC',
        'tax_query' => [['taxonomy' => 'product_cat', 'terms' => $cats]]]);
    if ($rel->have_posts()): ?>
    <section class="prow">
      <header class="sec-head"><h2 class="sec-head__h">Các sản phẩm khác</h2></header>
      <div class="grid grid--3"><?php while ($rel->have_posts()) { $rel->the_post(); get_template_part('template-parts/card-product'); } wp_reset_postdata(); ?></div>
    </section>
    <?php endif; ?>
  </div>
</div>
<?php get_footer();
