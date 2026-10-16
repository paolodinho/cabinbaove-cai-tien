<aside class="side">
  <section class="side__box">
    <h2 class="side__h">Sản phẩm</h2>
    <ul class="side__cats">
      <?php $cur = is_tax('product_cat') ? get_queried_object_id() : 0;
      foreach (cbv_product_cats() as $t) {
          if ($t->parent) continue;
          $kids = get_terms(['taxonomy' => 'product_cat', 'parent' => $t->term_id, 'hide_empty' => true]);
          echo '<li class="' . ($cur === $t->term_id ? 'is-active' : '') . '"><a href="' . esc_url(get_term_link($t)) . '">' . esc_html($t->name) . '</a>';
          if ($kids) { echo '<ul>'; foreach ($kids as $k) echo '<li class="' . ($cur === $k->term_id ? 'is-active' : '') . '"><a href="' . esc_url(get_term_link($k)) . '">' . esc_html($k->name) . '</a></li>'; echo '</ul>'; }
          echo '</li>';
      } ?>
    </ul>
  </section>
  <section class="side__box side__contact">
    <h2 class="side__h">Hỗ trợ tư vấn</h2>
    <p>Gọi ngay để được tư vấn mẫu và báo giá theo kích thước thực tế.</p>
    <a class="side__tel" href="<?= esc_attr(cbv_tel(cbv_opt('hotline'))) ?>"><?= esc_html(cbv_opt('hotline')) ?></a>
    <a class="side__tel" href="<?= esc_attr(cbv_tel(cbv_opt('phone'))) ?>"><?= esc_html(cbv_opt('phone')) ?></a>
  </section>
  <?php $news = new WP_Query(['post_type' => 'post', 'posts_per_page' => 5, 'category_name' => 'hoat-dong', 'no_found_rows' => true, 'post__not_in' => is_singular('post') ? [get_the_ID()] : []]);
  if ($news->have_posts()): ?>
  <section class="side__box">
    <h2 class="side__h">Hoạt động mới</h2>
    <ul class="side__news">
      <?php while ($news->have_posts()): $news->the_post(); ?>
      <li><a href="<?php the_permalink(); ?>"><?php if (has_post_thumbnail()) the_post_thumbnail('medium', ['loading' => 'lazy', 'alt' => '']); ?><span><?php the_title(); ?></span></a></li>
      <?php endwhile; wp_reset_postdata(); ?>
    </ul>
  </section>
  <?php endif;
  $grp = cbv_current_group();
  if ($grp): echo cbv_group_banner($grp);
  else: $ad = cbv_lines('sidebar_ad'); if ($ad && !empty($ad[0][0])): ?>
  <a class="side__ad" href="<?= esc_url($ad[0][1] ?? '#') ?>" target="_blank" rel="noopener"><img src="<?= esc_url(home_url($ad[0][0])) ?>" alt="Thùng rác môi trường ICD" loading="lazy" width="270" height="400"></a>
  <?php endif; endif; ?>
</aside>
