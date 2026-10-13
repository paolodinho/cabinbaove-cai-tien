<?php get_header(); ?>
<div class="wrap notfound">
  <h1 class="page-head__h">Không tìm thấy trang</h1>
  <p>Trang bạn tìm có thể đã được đổi địa chỉ. Mời xem danh mục sản phẩm hoặc gọi <a href="<?= esc_attr(cbv_tel(cbv_opt('hotline'))) ?>"><?= esc_html(cbv_opt('hotline')) ?></a>.</p>
  <ul class="chips"><?php foreach (cbv_product_cats() as $t) if (!$t->parent) echo '<li><a href="' . esc_url(get_term_link($t)) . '">' . esc_html($t->name) . '</a></li>'; ?></ul>
</div>
<?php get_footer();
