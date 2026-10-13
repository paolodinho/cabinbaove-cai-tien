<?php get_header(); the_post(); ?>
<div class="wrap layout">
  <?php get_template_part('template-parts/sidebar'); ?>
  <div class="layout__main">
    <article class="post">
      <h1 class="post__title"><?php the_title(); ?></h1>
      <p class="post__meta">Ngày đăng: <?= esc_html(get_the_date('d/m/Y')) ?><?php $c = get_the_category(); if ($c) echo ' - <a href="' . esc_url(get_category_link($c[0])) . '">' . esc_html($c[0]->name) . '</a>'; ?></p>
      <?php if (has_excerpt()): ?><p class="post__lead"><?= esc_html(get_the_excerpt()) ?></p><?php endif; ?>
      <div class="entry"><?php the_content(); ?></div>
    </article>
    <?php $rel = new WP_Query(['post_type' => 'post', 'posts_per_page' => 3, 'post__not_in' => [get_the_ID()], 'no_found_rows' => true, 'category__in' => wp_get_post_categories(get_the_ID())]);
    if ($rel->have_posts()): ?>
    <section class="prow">
      <header class="sec-head"><h2 class="sec-head__h">Bài viết liên quan</h2></header>
      <div class="grid grid--3"><?php while ($rel->have_posts()) { $rel->the_post(); get_template_part('template-parts/card-post'); } wp_reset_postdata(); ?></div>
    </section>
    <?php endif; ?>
  </div>
</div>
<?php get_footer();
