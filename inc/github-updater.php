<?php
/**
 * GitHub Theme Updater (sem plugins)
 * Repo: https://github.com/Diniz-visual/diniz-visual
 *
 * ✅ Suporta:
 * - Releases (recomendado)   /releases/latest
 * - Tags (fallback)         /tags  (pega a tag mais recente)
 *
 * IMPORTANTE:
 * - Para o WordPress comparar versões, você precisa **publicar uma TAG** (ex.: v1.0.1)
 *   ou criar uma **Release** com tag.
 * - Mantenha "Version: 1.0.1" no style.css igual à tag (sem o "v").
 */

if (!defined('ABSPATH')) exit;

function tbz_github_repo() {
  return [
    'owner' => 'Diniz-visual',
    'repo'  => 'diniz-visual',
    // branch padrão (usado apenas se você quiser adaptar pra commits)
    'branch' => 'main',
  ];
}

function tbz_theme_stylesheet() {
  return get_stylesheet();
}

function tbz_theme_version() {
  $theme = wp_get_theme(tbz_theme_stylesheet());
  return $theme->get('Version');
}

function tbz_github_headers($extra = []) {
  $headers = [
    'Accept'     => 'application/vnd.github+json',
    'User-Agent' => 'WordPress; ' . home_url('/'),
  ];

  // Opcional: token para evitar rate limit:
  // define('TBZ_GITHUB_TOKEN', 'ghp_...');
  if (defined('TBZ_GITHUB_TOKEN') && TBZ_GITHUB_TOKEN) {
    $headers['Authorization'] = 'Bearer ' . TBZ_GITHUB_TOKEN;
  }

  return array_merge($headers, $extra);
}

function tbz_normalize_version($v) {
  return ltrim((string)$v, "vV \t\n\r\0\x0B");
}

function tbz_github_get_json($url) {
  $res = wp_remote_get($url, [
    'timeout' => 12,
    'headers' => tbz_github_headers(),
  ]);

  if (is_wp_error($res)) return null;
  $code = wp_remote_retrieve_response_code($res);
  if ($code < 200 || $code >= 300) return null;

  $body = wp_remote_retrieve_body($res);
  $json = json_decode($body, true);
  return is_array($json) ? $json : null;
}

/**
 * Retorna:
 * [
 *   'version' => '1.0.1',
 *   'package' => 'https://api.github.com/repos/.../zipball/v1.0.1',
 *   'url'     => 'https://github.com/.../releases/tag/v1.0.1' (opcional)
 * ]
 */
function tbz_github_latest_package() {
  $repo = tbz_github_repo();

  // 1) Tenta latest release
  $release_url = sprintf('https://api.github.com/repos/%s/%s/releases/latest', $repo['owner'], $repo['repo']);
  $release = tbz_github_get_json($release_url);

  if (is_array($release) && !empty($release['tag_name'])) {
    return [
      'version' => tbz_normalize_version($release['tag_name']),
      'package' => $release['zipball_url'] ?? '',
      'url'     => $release['html_url'] ?? '',
      'body'    => $release['body'] ?? '',
    ];
  }

  // 2) Fallback: tags (pega a primeira)
  $tags_url = sprintf('https://api.github.com/repos/%s/%s/tags?per_page=1', $repo['owner'], $repo['repo']);
  $tags = tbz_github_get_json($tags_url);

  if (is_array($tags) && !empty($tags[0]['name'])) {
    $tag = $tags[0]['name'];
    $zip = sprintf('https://api.github.com/repos/%s/%s/zipball/%s', $repo['owner'], $repo['repo'], rawurlencode($tag));
    $html = sprintf('https://github.com/%s/%s/tree/%s', $repo['owner'], $repo['repo'], rawurlencode($tag));
    return [
      'version' => tbz_normalize_version($tag),
      'package' => $zip,
      'url'     => $html,
      'body'    => '',
    ];
  }

  return null;
}

function tbz_github_update_payload() {
  $remote = tbz_github_latest_package();
  if (!$remote || empty($remote['version']) || empty($remote['package'])) return null;

  $new_version = $remote['version'];
  $current     = tbz_theme_version();

  if (!$current || version_compare($new_version, $current, '<=')) return null;

  $stylesheet = tbz_theme_stylesheet();
  $theme      = wp_get_theme($stylesheet);

  return [
    'theme'       => $stylesheet,
    'new_version' => $new_version,
    'url'         => !empty($remote['url']) ? $remote['url'] : $theme->get('ThemeURI'),
    'package'     => $remote['package'],
  ];
}

/** 1) Injetar update no transient */
function tbz_filter_update_themes($transient) {
  if (!is_object($transient)) return $transient;
  if (empty($transient->checked) || !is_array($transient->checked)) return $transient;

  $payload = tbz_github_update_payload();
  if ($payload) {
    $transient->response[$payload['theme']] = (object) $payload;
  }
  return $transient;
}
add_filter('site_transient_update_themes', 'tbz_filter_update_themes');

/** 2) Informações do tema */
function tbz_filter_themes_api($result, $action, $args) {
  if ($action !== 'theme_information') return $result;
  if (!isset($args->slug) || $args->slug !== tbz_theme_stylesheet()) return $result;

  $remote = tbz_github_latest_package();
  if (!$remote) return $result;

  $theme = wp_get_theme(tbz_theme_stylesheet());

  return (object) [
    'name'          => $theme->get('Name'),
    'slug'          => tbz_theme_stylesheet(),
    'version'       => $remote['version'] ?: $theme->get('Version'),
    'author'        => $theme->get('Author'),
    'homepage'      => $theme->get('ThemeURI'),
    'sections'      => [
      'description' => wp_kses_post($theme->get('Description')),
      'changelog'   => !empty($remote['body']) ? wp_kses_post(nl2br($remote['body'])) : '',
    ],
    'download_link' => $remote['package'] ?? '',
  ];
}
add_filter('themes_api', 'tbz_filter_themes_api', 10, 3);

/** 3) Corrigir nome da pasta após atualizar */
function tbz_upgrader_source_selection($source, $remote_source, $upgrader, $hook_extra) {
  if (empty($hook_extra['theme']) || $hook_extra['theme'] !== tbz_theme_stylesheet()) return $source;

  $correct = trailingslashit($remote_source) . tbz_theme_stylesheet();
  if (is_dir($correct)) return $source;

  $dirs = glob(trailingslashit($remote_source) . '*', GLOB_ONLYDIR);
  if (!$dirs || empty($dirs[0])) return $source;

  @rename($dirs[0], $correct);
  return $correct;
}
add_filter('upgrader_source_selection', 'tbz_upgrader_source_selection', 10, 4);
