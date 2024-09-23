<?php

/**
 * Plugin Name: Codenz Cloudflare Cache Monitor
 * Plugin URI: https://github.com/codenz-br/codenz-cloudflare-cache-monitor
 * Description: A plugin to monitor Cloudflare cache purges and validate content updates for WordPress sites.
 * Version: 1.0.0
 * Author: Codenz
 * Author URI: https://github.com/codenz-br
 * Text Domain: codenz-cloudflare-cache-monitor
 * Domain Path: /languages
 */

/**
 * Functions prefix `ccm_`
 */

/**
 * Definir constantes para o plugin
 */
define('CCM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CCM_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Carregar o autoloader do Composer
 */
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    error_log('Autoloader não encontrado. Por favor, execute "composer install".');
    return;
}

/**
 * Loading text domain translation
 */
function ccm_load_textdomain()
{
    load_plugin_textdomain('cloudflare-cache-monitor', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'ccm_load_textdomain');


/**
 * Initialize the plugin.
 */
function ccm_init()
{
    \CloudflareCacheMonitor\SettingsPage::getInstance();
    \CloudflareCacheMonitor\CacheMonitor::getInstance();
}
add_action('plugins_loaded', 'ccm_init');
