<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="<?= esc_url(get_template_directory_uri()) ?>/assets/img/logo.png">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#main">Bỏ qua điều hướng</a>
<div class="topbar">
  <div class="wrap topbar__in">
    <ul class="topbar__social" aria-label="Mạng xã hội">
      <?php if (cbv_opt('facebook')): ?><li><a href="<?= esc_url(cbv_opt('facebook')) ?>" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path fill="currentColor" d="M14 8V6.2c0-.8.2-1.2 1.4-1.2H17V2h-2.6C11.3 2 10 3.5 10 6v2H8v3h2v11h4V11h2.6l.4-3z"/></svg></a></li><?php endif; ?>
      <?php if (cbv_opt('youtube')): ?><li><a href="<?= esc_url(cbv_opt('youtube')) ?>" target="_blank" rel="noopener" aria-label="YouTube"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M23 7.2a3 3 0 0 0-2.1-2.1C19 4.6 12 4.6 12 4.6s-7 0-8.9.5A3 3 0 0 0 1 7.2 31 31 0 0 0 .5 12a31 31 0 0 0 .5 4.8 3 3 0 0 0 2.1 2.1c1.9.5 8.9.5 8.9.5s7 0 8.9-.5a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .5-4.8 31 31 0 0 0-.5-4.8zM9.8 15V9l5.7 3z"/></svg></a></li><?php endif; ?>
      <li><a href="mailto:<?= esc_attr(cbv_opt('email')) ?>" aria-label="Email"><svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path fill="currentColor" d="M2 5h20v14H2zm2 2v.5l8 5 8-5V7z"/></svg></a></li>
    </ul>
    <div class="topbar__right">
      <span class="topbar__hot">Hotline: <a href="<?= esc_attr(cbv_tel(cbv_opt('phone'))) ?>"><?= esc_html(cbv_opt('phone')) ?></a> <span class="sep">|</span> <a href="<?= esc_attr(cbv_tel(cbv_opt('hotline'))) ?>"><?= esc_html(cbv_opt('hotline')) ?></a></span>
      <form class="topbar__search" role="search" action="<?= esc_url(home_url('/')) ?>">
        <label class="sr" for="s-top">Tìm kiếm</label>
        <input id="s-top" type="search" name="s" placeholder="Tìm kiếm sản phẩm" value="<?= esc_attr(get_search_query()) ?>">
        <input type="hidden" name="post_type" value="product">
        <button type="submit" aria-label="Tìm"><svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path fill="currentColor" d="M10 2a8 8 0 0 1 6.3 12.9l5.4 5.4-1.4 1.4-5.4-5.4A8 8 0 1 1 10 2zm0 2a6 6 0 1 0 0 12 6 6 0 0 0 0-12z"/></svg></button>
      </form>
    </div>
  </div>
</div>
<header class="header" id="header">
  <div class="wrap header__in">
    <a class="logo" href="<?= esc_url(home_url('/')) ?>" aria-label="ICD CABINTECH - Trang chủ">
      <img src="<?= esc_url(get_template_directory_uri()) ?>/assets/img/logo.png" alt="ICD Cabin Technology" width="80" height="60">
    </a>
    <nav class="nav" id="nav" aria-label="Menu chính">
      <button class="nav__close" type="button" aria-label="Đóng menu">&times;</button>
      <?php
      $cur = trailingslashit(home_url(add_query_arg([])));
      $menu = [
        ['Trang chủ', home_url('/')],
        ['Giới thiệu', home_url('/gioi-thieu-cong-ty.html')],
        ['Sản phẩm', get_permalink(wc_get_page_id('shop')), 'sub'],
        ['Hoạt động', home_url('/hoat-dong.html')],
        ['Tin tức', home_url('/tin-tuc.html')],
        ['Liên hệ', home_url('/lien-he.html')],
      ];
      $req = home_url(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
      if (has_nav_menu('primary')) {
          wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'menu_class' => 'nav__list', 'depth' => 2]);
      } else {
          echo '<ul class="nav__list">';
          foreach ($menu as $m) {
              $active = ($m[1] === $req) || ($m[0] === 'Sản phẩm' && (is_tax('product_cat') || is_singular('product') || (function_exists('is_shop') && is_shop())))
                  || ($m[0] === 'Hoạt động' && is_singular('post') && has_category('hoat-dong')) || ($m[0] === 'Tin tức' && is_singular('post') && has_category('tin-tuc'));
              echo '<li class="' . (isset($m[2]) ? 'has-sub ' : '') . ($active ? 'is-active' : '') . '"><a href="' . esc_url($m[1]) . '">' . esc_html($m[0]) . '</a>';
              if (isset($m[2])) {
                  echo '<button class="nav__toggle" aria-label="Mở danh mục sản phẩm" aria-expanded="false"></button><ul class="nav__sub">';
                  foreach (cbv_product_cats() as $t) if (!$t->parent) echo '<li><a href="' . esc_url(get_term_link($t)) . '">' . esc_html($t->name) . '</a></li>';
                  echo '</ul>';
              }
              echo '</li>';
          }
          echo '</ul>';
      }
      ?>
    </nav>
    <a class="btn btn--cta header__cta" href="#bao-gia">Nhận báo giá</a>
    <button class="burger" id="burger" aria-label="Mở menu" aria-controls="nav" aria-expanded="false"><span></span><span></span><span></span></button>
  </div>
</header>
<?php cbv_breadcrumb(); ?>
<main id="main">
