<?php $a = cbv_attrs(get_the_ID()); ?>
<article class="pcard">
  <a class="pcard__img" href="<?php the_permalink(); ?>">
    <?php if (has_post_thumbnail()) the_post_thumbnail('cbv-card', ['alt' => esc_attr(get_the_title()), 'loading' => 'lazy']); else echo '<span class="noimg">Ảnh đang cập nhật</span>'; ?>
  </a>
  <div class="pcard__body">
    <h3 class="pcard__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
    <?php if (!empty($a['Model'])): ?><p class="pcard__code">Mã: <?= esc_html($a['Model']) ?></p><?php endif; ?>
    <a class="pcard__more" href="<?php the_permalink(); ?>">Xem chi tiết</a>
  </div>
</article>
