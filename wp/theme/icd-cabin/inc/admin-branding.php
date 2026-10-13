<?php
/** Cá nhân hoá WP Admin theo màu ICD Cabin (theo mẫu wp-admin-branding dùng chung). */
if (!defined('ABSPATH')) exit;
add_filter('login_headerurl', fn() => home_url('/'));
add_filter('login_headertext', fn() => get_bloginfo('name'));
add_action('login_enqueue_scripts', function () { ?>
<style>
body.login{background:#FFF6EC}
#login h1 a{background-image:url('<?= esc_url(get_template_directory_uri()) ?>/assets/img/logo.png');background-size:contain;width:160px;height:110px}
#loginform{border-radius:8px;box-shadow:0 6px 24px rgba(0,0,0,.08)}
input[type=text]:focus,input[type=password]:focus{border-color:#FF8300!important;box-shadow:0 0 0 2px rgba(255,131,0,.2)!important}
.wp-core-ui .button-primary{background:#E57600!important;border-color:#C76600!important;font-weight:600}
.wp-core-ui .button-primary:hover{background:#008D41!important;border-color:#006F32!important}
#backtoblog a,#nav a{color:#A94400!important}
</style>
<?php });
add_action('admin_head', function () { ?>
<style>
#wpadminbar{background:#1E2A23}
#adminmenu,#adminmenuback,#adminmenuwrap{background:#1E2A23}
#adminmenu .wp-submenu{background:#141D18!important}
#adminmenu li.current a.menu-top,#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu,#adminmenu li:hover a.menu-top{background:#008D41!important;color:#fff}
.wp-core-ui .button-primary{background:#E57600;border-color:#C76600}
.wp-core-ui .button-primary:hover{background:#008D41;border-color:#006F32}
</style>
<?php });
add_action('wp_before_admin_bar_render', function () { echo '<style>#wpadminbar{background:#1E2A23}</style>'; });
add_filter('admin_footer_text', fn() => 'Website ICD CABINTECH - cabinbaove.com.vn. Hỗ trợ kỹ thuật: hieudx3107@gmail.com');
remove_action('welcome_panel', 'wp_welcome_panel');
add_action('welcome_panel', function () { ?>
<div style="padding:24px;display:flex;gap:24px;align-items:center;flex-wrap:wrap">
  <img src="<?= esc_url(get_template_directory_uri()) ?>/assets/img/logo.png" alt="" width="80">
  <div><h2 style="margin:0 0 8px">Quản trị website ICD CABINTECH</h2>
  <p style="margin:0 0 12px">Việc hay làm: thêm sản phẩm, đăng bài hoạt động, xem yêu cầu báo giá, sửa hotline và ảnh slider.</p>
  <a class="button button-primary" href="<?= admin_url('post-new.php?post_type=product') ?>">Thêm sản phẩm</a>
  <a class="button" href="<?= admin_url('post-new.php') ?>">Đăng bài</a>
  <a class="button" href="<?= admin_url('edit.php?post_type=bao_gia') ?>">Yêu cầu báo giá</a>
  <a class="button" href="<?= admin_url('admin.php?page=cbv-settings') ?>">Cài đặt ICD Cabin</a></div>
</div>
<?php });
add_action('admin_menu', function () {
    if (current_user_can('manage_options')) return;
    foreach (['tools.php', 'themes.php', 'plugins.php', 'options-general.php'] as $m) remove_menu_page($m);
}, 99);
add_action('admin_head', function () { echo '<link rel="icon" href="' . esc_url(get_template_directory_uri()) . '/assets/img/logo.png">'; });
