<?php

/**
 * CacheMonitor Class
 *
 * @package CloudflareCacheMonitor
 */

namespace CloudflareCacheMonitor;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class CacheMonitor
{
    /**
     * Singleton instance.
     *
     * @var CacheMonitor
     */
    private static $instance = null;

    /**
     * Worker URL.
     *
     * @var string
     */
    private $worker_url = 'https://this-worker-endpoint.workers.dev';

    /**
     * API Key.
     *
     * @var string
     */
    private $api_key = 'THIS_KEY_SECRET';

    /**
     * Constructor.
     */
    private function __construct()
    {
        // Get saved options.
        $options = get_option('ccm_options');
        $this->worker_url = isset($options['worker_url']) ? $options['worker_url'] : $this->worker_url;
        $this->api_key    = isset($options['api_key']) ? $options['api_key'] : $this->api_key;

        // Register the filter hook.
        add_filter('cloudflare_purge_by_url', array($this, 'handle_purge_by_url'), 10, 2);
    }

    /**
     * Get the singleton instance.
     *
     * @return CacheMonitor
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new CacheMonitor();
        }
        return self::$instance;
    }

    /**
     * Handle the purge by URL.
     *
     * @param array $urls    Array of URLs being purged.
     * @param int   $post_id ID of the post being purged.
     *
     * @return array
     */
    public function handle_purge_by_url($urls, $post_id)
    {
        // Check if the WP_SITEURL constant is defined in the environment
        $current_site_url = defined('WP_SITEURL') && WP_SITEURL ? WP_SITEURL : get_site_url();

        // Log $current_site_url
        Logger::log(sprintf(__('Current Site URL: %s', 'cloudflare-cache-monitor'), $current_site_url));

        // Remove the protocol from the current URL to get only the domain
        $parsed_url = parse_url($current_site_url);
        $current_domain = isset($parsed_url['host']) ? $parsed_url['host'] : '';

        // Pass the server address via hook so the developer can define it
        $server_address = apply_filters('ccm_define_server_address', '');

        // If a server address is defined, apply the replacement logic
        if ($server_address) {
            $urls = apply_filters('ccm_modify_purge_urls', $urls, $post_id, $current_domain, $server_address);
        }

        // Log to check the URLs after applying the filter
        Logger::log('URLs after applying custom filter: ' . wp_json_encode($urls));

        // Get current timestamp.
        $purge_time = strtotime(gmdate('Y-m-d H:i:s'));

        Logger::log(sprintf(__('Purge Time: %d', 'cloudflare-cache-monitor'), $purge_time));

        // Get post_name by get_post_field
        $post_name = get_post_field('post_name', $post_id);

        // Validate exists post_name
        if ($post_name) {
            Logger::log(sprintf(__('Post Name: %s', 'cloudflare-cache-monitor'), $post_name));
        } else {
            $post_name = '';
            Logger::log(sprintf(__('Post Name está vazio ou não encontrado para o Post ID: %d', 'cloudflare-cache-monitor'), $post_id));
        }

        // Get the custom posts page URL.
        $custom_posts_page = get_option('ccm_options')['posts_page_url'] ?? 'empty';
        $custom_posts_page = apply_filters('ccm_define_posts_page', $custom_posts_page);

        // Add the custom posts page URL to the list of URLs to be purged.
        if (!empty($custom_posts_page) && filter_var($custom_posts_page, FILTER_VALIDATE_URL)) {
            $urls[] = $custom_posts_page;
        }

        // Log the custom posts page URL.
        Logger::log(sprintf(__('Custom Posts Page URL: %s', 'cloudflare-cache-monitor'), $custom_posts_page));

        // Data to be sent to the Worker.
        $data = array(
            'post_id'    => $post_id,
            'post_name'  => $post_name,
            'purge_time' => $purge_time,
            'urls'       => $urls,
        );

        Logger::log('Data to be sent to the Worker: ' . wp_json_encode($data));

        // Configure the HTTP request.
        $args = array(
            'body'        => wp_json_encode($data),
            'headers'     => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key,
            ),
            'timeout'     => 15,
            'redirection' => 5,
            'blocking'    => true,
        );

        // Send the POST request to the Worker.
        $response = wp_remote_post($this->worker_url, $args);

        // Check for errors.
        if (is_wp_error($response)) {
            Logger::log(sprintf(__('Error sending data to Cloudflare Worker: %s', 'cloudflare-cache-monitor'), $response->get_error_message()));
        } else {
            Logger::log(__('Data successfully sent to Cloudflare Worker.', 'cloudflare-cache-monitor'));
            // Optional: Log the Worker's response.
            $response_body = wp_remote_retrieve_body($response);
            Logger::log(sprintf(__('Worker response: %s', 'cloudflare-cache-monitor'), $response_body));
        }

        return $urls;
    }
}
