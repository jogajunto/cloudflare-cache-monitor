<?php

/**
 * SettingsPage Class
 *
 * @package CloudflareCacheMonitor
 */

namespace CloudflareCacheMonitor;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class SettingsPage
{
    /**
     * Singleton instance.
     *
     * @var SettingsPage
     */
    private static $instance = null;

    /**
     * Constructor.
     */
    private function __construct()
    {
        // Hook to add the settings page.
        add_action('admin_menu', array($this, 'add_settings_page'));
        // Hook to register settings.
        add_action('admin_init', array($this, 'register_settings'));
        // Hook to intercept the validation and saving of data.
        add_filter('pre_update_option_ccm_options', array($this, 'pre_update_options'), 10, 2);
    }

    /**
     * Get the singleton instance.
     *
     * @return SettingsPage
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add the settings page.
     */
    public function add_settings_page()
    {
        add_options_page(
            __('Cloudflare Cache Monitor', 'cloudflare-cache-monitor'),
            __('Cloudflare Cache Monitor', 'cloudflare-cache-monitor'),
            'manage_options',
            'codenz-ccm',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Render the settings page.
     */
    public function render_settings_page()
    {
?>
        <div class="wrap">
            <h1><?php esc_html_e('Cloudflare Cache Monitor Settings', 'cloudflare-cache-monitor'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('ccm_options_group');
                do_settings_sections('ccm');
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    /**
     * Register settings.
     */
    public function register_settings()
    {
        register_setting('ccm_options_group', 'ccm_options', array($this, 'sanitize_options'));

        add_settings_section(
            'ccm_main_section',
            __('Main Settings', 'cloudflare-cache-monitor'),
            null,
            'ccm'
        );

        add_settings_field(
            'worker_url',
            __('Worker URL', 'cloudflare-cache-monitor'),
            array($this, 'worker_url_callback'),
            'ccm',
            'ccm_main_section'
        );

        add_settings_field(
            'api_key',
            __('API Key', 'cloudflare-cache-monitor'),
            array($this, 'api_key_callback'),
            'ccm',
            'ccm_main_section'
        );
    }

    /**
     * Sanitize options before saving.
     *
     * @param array $options Options to sanitize.
     *
     * @return array
     */
    public function sanitize_options($options)
    {
        if (isset($options['worker_url'])) {
            $options['worker_url'] = esc_url_raw($options['worker_url']);
        }
        if (isset($options['api_key'])) {
            $options['api_key'] = sanitize_text_field($options['api_key']);
        }
        return $options;
    }

    /**
     * Callback for the worker_url field.
     */
    public function worker_url_callback()
    {
        $options     = get_option('ccm_options');
        $worker_url  = isset($options['worker_url']) ? $options['worker_url'] : '';
    ?>
        <input type="text" name="ccm_options[worker_url]" value="<?php echo esc_attr($worker_url); ?>" size="50">
    <?php
    }

    /**
     * Callback for the api_key field.
     */
    public function api_key_callback()
    {
        $options = get_option('ccm_options');
        $api_key = isset($options['api_key']) ? $options['api_key'] : '';

        // Mask the API Key for display.
        if (! empty($api_key)) {
            $api_key_masked = $this->token_masked($api_key);
        } else {
            $api_key_masked = '';
        }
    ?>
        <input type="text" name="ccm_options[api_key]" value="<?php echo esc_attr($api_key_masked); ?>" size="50">
<?php
    }

    /**
     * Mask the API token for display.
     *
     * @param string $token         The API token.
     * @param string $mask_char     The character to use for masking.
     * @param int    $visible_start Number of visible characters at the start.
     * @param int    $visible_end   Number of visible characters at the end.
     *
     * @return string
     */
    private function token_masked($token, $mask_char = '*', $visible_start = 4, $visible_end = 4)
    {
        $token_length = strlen($token);

        // Ensure the token is long enough to be masked.
        if ($token_length <= ($visible_start + $visible_end)) {
            return $token; // Return the original token if it's too short.
        }

        // Define the visible parts at the start and end.
        $start = substr($token, 0, $visible_start);
        $end   = substr($token, -$visible_end);

        // Calculate how many characters will be masked.
        $masked_part = str_repeat($mask_char, $token_length - $visible_start - $visible_end);

        // Concatenate the visible part with the masked part and the end visible.
        return $start . $masked_part . $end;
    }

    /**
     * Hook to handle saving options and ensure the original token is preserved.
     *
     * @param array $new_value The new option value.
     * @param array $old_value The old option value.
     *
     * @return array
     */
    public function pre_update_options($new_value, $old_value)
    {
        // Check if the API key was modified by the user.
        if (isset($new_value['api_key']) && isset($old_value['api_key'])) {
            $new_api_key          = $new_value['api_key'];
            $original_api_key     = $old_value['api_key'];
            $masked_original_key  = $this->token_masked($original_api_key);

            // Check if the new API key contains valid characters.
            if ($this->is_valid_token($new_api_key)) {
                // If the new key is equal to the masked or empty, keep the original value.
                if ($new_api_key === $masked_original_key || empty($new_api_key)) {
                    $new_value['api_key'] = $original_api_key; // Keep the original token.
                }
            } else {
                // If the token contains invalid characters, keep the original value.
                $new_value['api_key'] = $original_api_key;
                add_settings_error('ccm_options', 'invalid_api_key', __('The API token contains invalid characters and the original value was kept.', 'cloudflare-cache-monitor'));
            }
        }

        return $new_value;
    }

    /**
     * Validate the token to ensure it contains only allowed characters.
     * We allow only letters, numbers, hyphens, and underscores, for example.
     *
     * @param string $token The token to validate.
     *
     * @return bool
     */
    private function is_valid_token($token)
    {
        // Regular expression that allows only letters, numbers, hyphens, and underscores.
        return (bool) preg_match('/^[a-zA-Z0-9-_]+$/', $token);
    }
}
