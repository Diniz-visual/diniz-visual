<?php
/**
 * GitHub Theme Updater (no tema, sem plugin)
 * Repo: https://github.com/Diniz-visual/diniz-visual
 *
 * Usa o ZIP anexado na Release com nome: diniz-visual.zip
 * (Workflow incluso no repositório para gerar isso automaticamente)
 */
if (!defined('ABSPATH')) exit;

// PHP 7.x polyfill for str_ends_with
if (!function_exists('str_ends_with')) {
  function str_ends_with($haystack, $needle) {
    $len = strlen($needle);
    if ($len === 0) return true;
    return (substr($haystack, -$len) === $needle);
  }
}

function dvgh_repo(){ return ['owner'=>'Diniz-visual','repo'=>'diniz-visual']; }
function dvgh_stylesheet(){ return get_stylesheet(); }
function dvgh_theme_version(){ $t = wp_get_theme(dvgh_stylesheet()); return $t->get('Version'); }
function dvgh_norm($v){ return ltrim((string)$v, "vV \t\n\r\0\x0B"); }

function dvgh_headers(){
  $h = [
    'Accept'     => 'application/vnd.github+json',
    'User-Agent' => 'WordPress; ' . home_url('/'),
  ];
  if (defined('DV_GITHUB_TOKEN') && DV_GITHUB_TOKEN) {
    $h['Authorization'] = 'Bearer ' . DV_GITHUB_TOKEN;
  }
  return $h;
}

function dvgh_latest_release(){
  $r = dvgh_repo();
  $url = sprintf('https://api.github.com/repos/%s/%s/releases/latest', $r['owner'], $r['repo']);
  $res = wp_remote_get($url, ['timeout'=>12,'headers'=>dvgh_headers()]);
  if (is_wp_error($res)) return null;
  $code = wp_remote_retrieve_response_code($res);
  if ($code < 200 || $code >= 300) return null;
  $json = json_decode(wp_remote_retrieve_body($res), true);
  if (!is_array($json) || empty($json['tag_name'])) return null;
  return $json;
}

function dvgh_pick_asset_zip($release){
  if (empty($release['assets']) || !is_array($release['assets'])) return '';
  $wanted = dvgh_stylesheet().'.zip';
  foreach ($release['assets'] as $a){
    if (!empty($a['name']) && $a['name'] === $wanted && !empty($a['browser_download_url'])) {
      return $a['browser_download_url'];
    }
  }
  foreach ($release['assets'] as $a){
    if (!empty($a['name']) && str_ends_with($a['name'], '.zip') && !empty($a['browser_download_url'])) {
      return $a['browser_download_url'];
    }
  }
  return '';
}

function dvgh_update_payload(){
  $release = dvgh_latest_release();
  if (!$release) return null;

  $new = dvgh_norm($release['tag_name']);
  $cur = dvgh_theme_version();
  if (!$cur || version_compare($new, $cur, '<=')) return null;

  $package = dvgh_pick_asset_zip($release);
  if (!$package && !empty($release['zipball_url'])) $package = $release['zipball_url'];
  if (!$package) return null;

  return [
    'theme'       => dvgh_stylesheet(),
    'new_version' => $new,
    'url'         => $release['html_url'] ?? 'https://github.com/Diniz-visual/diniz-visual/releases',
    'package'     => $package,
  ];
}

function dvgh_filter_updates($transient){
  if (!is_object($transient)) return $transient;
  if (empty($transient->checked) || !is_array($transient->checked)) return $transient;

  $p = dvgh_update_payload();
  if ($p) $transient->response[$p['theme']] = (object)$p;

  return $transient;
}
add_filter('site_transient_update_themes', 'dvgh_filter_updates');

function dvgh_themes_api($result, $action, $args){
  if ($action !== 'theme_information') return $result;
  if (!isset($args->slug) || $args->slug !== dvgh_stylesheet()) return $result;

  $theme = wp_get_theme(dvgh_stylesheet());
  $release = dvgh_latest_release();
  if (!$release) return $result;

  $ver = dvgh_norm($release['tag_name'] ?? $theme->get('Version'));
  $dl  = dvgh_pick_asset_zip($release);
  if (!$dl && !empty($release['zipball_url'])) $dl = $release['zipball_url'];

  return (object)[
    'name'          => $theme->get('Name'),
    'slug'          => dvgh_stylesheet(),
    'version'       => $ver,
    'author'        => $theme->get('Author'),
    'homepage'      => $theme->get('ThemeURI'),
    'sections'      => [
      'description' => wp_kses_post($theme->get('Description')),
      'changelog'   => !empty($release['body']) ? wp_kses_post(nl2br($release['body'])) : '',
    ],
    'download_link' => $dl,
  ];
}
add_filter('themes_api', 'dvgh_themes_api', 10, 3);

function dvgh_source_selection($source, $remote_source, $upgrader, $hook_extra){
  if (empty($hook_extra['theme']) || $hook_extra['theme'] !== dvgh_stylesheet()) return $source;

  $correct = trailingslashit($remote_source) . dvgh_stylesheet();
  if (is_dir($correct)) return $source;

  $dirs = glob(trailingslashit($remote_source) . '*', GLOB_ONLYDIR);
  if (!$dirs || empty($dirs[0])) return $source;

  @rename($dirs[0], $correct);
  return $correct;
}
add_filter('upgrader_source_selection', 'dvgh_source_selection', 10, 4);
