<?php get_header();
$is_cat = is_tax('product_cat');
$term = $is_cat ? get_queried_object() : null;
$title = $is_cat ? $term->name : (is_search() ? 'Kết quả tìm kiếm: ' . get_search_query() : 'Sản phẩm');
$paged = max(1, (int) get_query_var('paged')); ?>
<div class="wrap layout">
  <?php get_template_part('template-parts/sidebar'); ?>
  <div class="layout__main">
    <header class="page-head">
      <h1 class="page-head__h"><?= esc_html($title) ?><?= $paged > 1 ? ' - Trang ' . $paged : '' ?></h1>
      <?php if ($is_cat && $paged === 1 && trim($term->description)): ?><div class="page-head__desc entry"><?= wp_kses_post(wpautop($term->description)) ?></div><?php endif; ?>
      <?php if ($is_cat): $kids = get_terms(['taxonomy' => 'product_cat', 'parent' => $term->term_id, 'hide_empty' => true]);
        if ($kids): ?><ul class="chips"><?php foreach ($kids as $k) echo '<li><a href="' . esc_url(get_term_link($k)) . '">' . esc_html($k->name) . ' (' . (int) $k->count . ')</a></li>'; ?></ul><?php endif; endif; ?>
      <?php if ($is_cat && $paged === 1) echo cbv_cat_btn('page-head__cat'); ?>
    </header>
    <?php if (have_posts()): ?>
      <div class="grid grid--3">
        <?php while (have_posts()) { the_post(); get_template_part('template-parts/card-product'); } ?>
      </div>
      <?php cbv_pagination(); ?>
    <?php else: ?>
      <p class="empty">Chưa có sản phẩm phù hợp. Vui lòng gọi <a href="<?= esc_attr(cbv_tel(cbv_opt('hotline'))) ?>"><?= esc_html(cbv_opt('hotline')) ?></a> để được tư vấn.</p>
    <?php endif; ?>
  </div>
</div>
<?php get_footer();
