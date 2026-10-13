<?php get_header(); $paged = max(1, (int) get_query_var('paged')); ?>
<div class="wrap layout">
  <?php get_template_part('template-parts/sidebar'); ?>
  <div class="layout__main">
    <header class="page-head">
      <h1 class="page-head__h"><?= esc_html(is_category() ? single_cat_title('', false) : (is_search() ? 'Kết quả tìm kiếm: ' . get_search_query() : 'Tin tức')) ?><?= $paged > 1 ? ' - Trang ' . $paged : '' ?></h1>
      <?php if (is_category() && $paged === 1 && category_description()): ?><div class="page-head__desc entry"><?= wp_kses_post(category_description()) ?></div><?php endif; ?>
    </header>
    <?php if (have_posts()): ?>
      <div class="alist">
        <?php while (have_posts()) { the_post(); get_template_part('template-parts/card-post'); } ?>
      </div>
      <?php cbv_pagination(); ?>
    <?php else: ?><p class="empty">Chưa có bài viết.</p><?php endif; ?>
  </div>
</div>
<?php get_footer();
