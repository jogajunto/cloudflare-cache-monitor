<?php

/**
 * Plugin Name: Cloudflare Cache Monitor
 * Plugin URI:  https://github.com/jogajunto/cloudflare-cache-monitor
 * Description: A plugin to monitor Cloudflare cache purges and validate content updates for WordPress sites.
 * Version:     1.0.0
 * Author:      Rafael de Araujo
 * Author URI:  https://github.com/rafaell1995
 * Text Domain: cloudflare-cache-monitor
 * Domain Path: /languages
 *
 * @package CloudflareCacheMonitor
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Define plugin constants.
 */
define('CCM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CCM_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Load Composer autoloader.
 */
if (file_exists(dirname(__DIR__, 3) . '/vendor/autoload.php')) {
    // Autoload of the main project (root)
    require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/vendor/autoload.php')) {
    // Local autoload of the plugin, if the plugin is standalone
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // No autoload found
    error_log(__('Autoloader not found. Please run "composer install".', 'cloudflare-cache-monitor'));
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
    CloudflareCacheMonitor\SettingsPage::get_instance();
    CloudflareCacheMonitor\CacheMonitor::get_instance();
}
add_action('plugins_loaded', 'ccm_init');
