<?php
/**
 * GitHub Theme Updater (sem plugins)
 * Repo: https://github.com/Diniz-visual/diniz-visual
 *
 * Como funciona:
 * - Busca a última release no GitHub (tag_name).
 * - Se a versão da release for maior que a do tema, mostra update.
 * - Usa o zipball_url da release para baixar e atualizar.
 *
 * Recomendação:
 * - Faça releases no GitHub (ex.: v1.0.1, v1.0.2...)
 * - No style.css, mantenha "Version: X.Y.Z" sincronizado com a release.
 */

if (!defined('ABSPATH')) exit;

function tbz_github_repo() {
  return [
    'owner' => 'Diniz-visual',
    'repo'  => 'diniz-visual',
  ];
}

function tbz_theme_stylesheet() {
  // Nome da pasta do tema (stylesheet) é o identificador do update no WP.
  return get_stylesheet();
}

function tbz_theme_version() {
  $theme = wp_get_theme(tbz_theme_stylesheet());
  return $theme->get('Version');
}

function tbz_github_headers($args = []) {
  $headers = [
    'Accept'     => 'application/vnd.github+json',
    'User-Agent' => 'WordPress; ' . home_url('/'),
  ];

  // Opcional: se você quiser evitar limites do GitHub, defina um token em wp-config.php:
  // define('TBZ_GITHUB_TOKEN', 'ghp_...');
  if (defined('TBZ_GITHUB_TOKEN') && TBZ_GITHUB_TOKEN) {
    $headers['Authorization'] = 'Bearer ' . TBZ_GITHUB_TOKEN;
  }

  return array_merge($headers, $args);
}

function tbz_github_latest_release() {
  $repo = tbz_github_repo();
  $url  = sprintf('https://api.github.com/repos/%s/%s/releases/latest', $repo['owner'], $repo['repo']);

  $res = wp_remote_get($url, [
    'timeout' => 12,
    'headers' => tbz_github_headers(),
  ]);

  if (is_wp_error($res)) return null;

  $code = wp_remote_retrieve_response_code($res);
  if ($code < 200 || $code >= 300) return null;

  $body = wp_remote_retrieve_body($res);
  $json = json_decode($body, true);
  if (!is_array($json)) return null;

  // Esperado: tag_name, zipball_url, html_url, published_at, etc.
  return $json;
}

function tbz_normalize_version($v) {
  // Remove prefixo "v" (v1.0.2 -> 1.0.2)
  return ltrim((string)$v, "vV \t\n\r\0\x0B");
}

function tbz_github_update_payload() {
  $release = tbz_github_latest_release();
  if (!$release || empty($release['tag_name']) || empty($release['zipball_url'])) return null;

  $new_version = tbz_normalize_version($release['tag_name']);
  $current     = tbz_theme_version();

  if (!$current || version_compare($new_version, $current, '<=')) return null;

  $stylesheet = tbz_theme_stylesheet();
  $theme      = wp_get_theme($stylesheet);

  return [
    'theme'       => $stylesheet,
    'new_version' => $new_version,
    'url'         => !empty($release['html_url']) ? $release['html_url'] : $theme->get('ThemeURI'),
    'package'     => $release['zipball_url'],
    // 'requires'  => '6.0',
    // 'requires_php' => '7.4',
  ];
}

/**
 * 1) Injetar update no transient do WP
 */
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

/**
 * 2) Informações na tela "Ver detalhes da versão"
 */
function tbz_filter_themes_api($result, $action, $args) {
  if ($action !== 'theme_information') return $result;
  if (!isset($args->slug) || $args->slug !== tbz_theme_stylesheet()) return $result;

  $release = tbz_github_latest_release();
  if (!$release) return $result;

  $theme = wp_get_theme(tbz_theme_stylesheet());

  return (object) [
    'name'          => $theme->get('Name'),
    'slug'          => tbz_theme_stylesheet(),
    'version'       => tbz_normalize_version($release['tag_name'] ?? $theme->get('Version')),
    'author'        => $theme->get('Author'),
    'homepage'      => $theme->get('ThemeURI'),
    'requires'      => $theme->get('RequiresWP'),
    'requires_php'  => $theme->get('RequiresPHP'),
    'sections'      => [
      'description' => wp_kses_post($theme->get('Description')),
      'changelog'   => !empty($release['body']) ? wp_kses_post(nl2br($release['body'])) : '',
    ],
    'download_link' => $release['zipball_url'] ?? '',
  ];
}
add_filter('themes_api', 'tbz_filter_themes_api', 10, 3);

/**
 * 3) Corrigir nome da pasta após atualizar (zip do GitHub vem com pasta repo-hash)
 */
function tbz_upgrader_source_selection($source, $remote_source, $upgrader, $hook_extra) {
  if (empty($hook_extra['theme']) || $hook_extra['theme'] !== tbz_theme_stylesheet()) return $source;

  $correct_folder = trailingslashit($remote_source) . tbz_theme_stylesheet();
  if (is_dir($correct_folder)) return $source; // já está ok

  // Procura a primeira pasta dentro do zip extraído
  $files = glob(trailingslashit($remote_source) . '*', GLOB_ONLYDIR);
  if (!$files || empty($files[0])) return $source;

  $from = $files[0];
  $to   = $correct_folder;

  // Renomeia para o nome do stylesheet (pasta do tema)
  @rename($from, $to);

  return $to;
}
add_filter('upgrader_source_selection', 'tbz_upgrader_source_selection', 10, 4);
