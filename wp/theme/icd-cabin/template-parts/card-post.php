<article class="acard">
  <a class="acard__img" href="<?php the_permalink(); ?>">
    <?php if (has_post_thumbnail()) the_post_thumbnail('cbv-card', ['alt' => esc_attr(get_the_title()), 'loading' => 'lazy']); else echo '<span class="noimg">Ảnh đang cập nhật</span>'; ?>
  </a>
  <div class="acard__body">
    <h3 class="acard__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
    <p class="acard__date"><?= esc_html(get_the_date('d/m/Y')) ?></p>
    <p class="acard__ex"><?= esc_html(wp_trim_words(get_the_excerpt(), 34)) ?></p>
  </div>
</article>
