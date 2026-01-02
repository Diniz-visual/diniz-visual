<?php
if (!defined('ABSPATH')) exit;

define('TBZ_THEME_VERSION', '1.0.0');

function tbz_setup(){
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
  register_nav_menus([
    'primary' => __('Menu Principal','tb-zerado'),
  ]);
}
add_action('after_setup_theme','tbz_setup');

function tbz_assets(){
  // Tailwind compilado (recomendado)
  if (file_exists(get_template_directory() . '/assets/css/tailwind.css')){
    wp_enqueue_style('tbz-tailwind', get_template_directory_uri().'/assets/css/tailwind.css', [], TBZ_THEME_VERSION);
  }

  // Bootstrap CSS (somente CSS)
  wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', [], '5.3.3');

  // CSS do tema (fallback/base)
  wp_enqueue_style('tbz-style', get_stylesheet_uri(), [], TBZ_THEME_VERSION);

  // Swiper
  wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11');
  wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11', true);

  // JS principal
  wp_enqueue_script('tbz-main', get_template_directory_uri().'/assets/js/main.js', [], TBZ_THEME_VERSION, true);

  // Passa dados para AJAX (URL + nonce)
  wp_localize_script('tbz-main', 'TBZ_AJAX', [
    'url'   => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('tbz_nonce')
  ]);

  // JS opcional de hero (só usa se existir .tbzHero)
  wp_enqueue_script('tbz-hero', get_template_directory_uri().'/assets/js/hero.js', ['swiper'], TBZ_THEME_VERSION, true);
}
add_action('wp_enqueue_scripts','tbz_assets');

/** AJAX: exemplo "load more" */
function tbz_ajax_load_more(){
  check_ajax_referer('tbz_nonce','nonce');

  $paged = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1;

  $q = new WP_Query([
    'post_type'      => 'post',
    'posts_per_page' => 6,
    'paged'          => $paged,
  ]);

  ob_start();
  if ($q->have_posts()){
    while($q->have_posts()){ $q->the_post();
      get_template_part('template-parts/card-post');
    }
  }
  wp_reset_postdata();

  $html = ob_get_clean();

  wp_send_json_success([
    'html'      => $html,
    'max_pages' => (int) $q->max_num_pages,
    'paged'     => $paged,
  ]);
}
add_action('wp_ajax_tbz_load_more', 'tbz_ajax_load_more');
add_action('wp_ajax_nopriv_tbz_load_more', 'tbz_ajax_load_more');

require_once get_template_directory().'/inc/helpers.php';

/** ACF fallback (não quebra) */
if (!function_exists('have_rows')){
  function have_rows(){ return false; }
  function the_row(){}
  function get_field(){ return null; }
  function get_sub_field(){ return null; }
}

// GitHub Updater
require_once get_template_directory().'/inc/github-updater.php';
