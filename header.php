<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class('bg-white'); ?>>
<?php wp_body_open(); ?>

<header class="border-bottom bg-white sticky-top">
  <div class="container py-3 d-flex align-items-center justify-content-between gap-3">
    <a class="fw-bold text-decoration-none" href="<?php echo esc_url(home_url('/')); ?>">
      <?php bloginfo('name'); ?>
    </a>

    <nav>
      <?php
        wp_nav_menu([
          'theme_location' => 'primary',
          'container'      => false,
          'menu_class'     => 'd-flex gap-3 list-unstyled mb-0',
          'fallback_cb'    => false,
        ]);
      ?>
    </nav>
  </div>
</header>

<main class="py-4">
