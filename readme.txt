=== Cloudflare Cache Monitor ===
Contributors: rafaell1995
Tags: cloudflare, cache, monitor, wordpress
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 5.6
Stable tag: 1.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A plugin to monitor Cloudflare cache purges and validate content updates for WordPress sites.

== Description ==

Cloudflare Cache Monitor is a plugin that integrates with Cloudflare to monitor cache purges and validate that content updates are properly reflected on your WordPress site.

**Features:**

- Sends purge requests to a Cloudflare Worker.
- Validates content updates after cache purge.
- Provides a settings page to configure the Worker URL and API Key.
- Allows developers to pass a custom server address and modify purge URLs.
- **New in v1.3.0:** Introduced a Logger class for enhanced debugging, and a new `ccm_define_posts_page` hook to handle purges for a custom posts listing page.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/cloudflare-cache-monitor` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to 'Settings' -> 'Cloudflare Cache Monitor' to configure the plugin.

== Frequently Asked Questions ==

= How do I obtain the Worker URL and API Key? =
You need to set up a Cloudflare Worker that processes the purge requests. The API Key is used for authentication with the Worker.

= Is this plugin compatible with other caching plugins? =
Yes, it is designed to work alongside other caching plugins, but its primary function is to interact with Cloudflare's caching system.

= How do I define a custom posts listing page (e.g. /blog) so that it is also purged? =
By default, this plugin will purge only the URLs associated with a given post. If you have a custom posts listing page (such as `/blog`) that needs purging, you can define it in two ways:

1. **Settings:**  
   - Go to "Cloudflare Cache Monitor" settings and add your custom page URL in the "Posts Page URL" field.  
   - Example: `https://example.com/blog/`.

2. **Hook (for theme developers or custom plugins):**  
   ```php
   add_filter('ccm_define_posts_page', function($default_posts_page) {
       // Replace with your custom blog page URL
       return 'https://example.com/blog/';
   });

This will override the default or configured URL, ensuring that /blog is included in cache purge requests.

== Screenshots ==

1. **Settings Page** - Configure your Worker URL and API Key.

== Changelog ==

= 1.3.0 =
* **New Feature:** Introduced Logger class to handle debug logging, replacing standard error_log calls.
* **New Hook:** `ccm_define_posts_page` for specifying a custom posts listing page (e.g., /blog).
* **Improvement:** Replaced direct error_log calls with Logger::log, adding a [CCM] prefix to all logs when WP_DEBUG is enabled.

= 1.2.0 =
* **New Feature:** Added `ccm_define_server_address` filter to define a custom server address.
* **Enhancement:** Introduced `ccm_modify_purge_urls` filter to modify purge URLs based on the server address.
* **Improvement:** Added logging of URLs after applying the custom filter for debugging purposes.

= 1.1.0 =
* **New Feature:** Added support for sending `post_name` to the Cloudflare Worker, allowing for precise URL filtering and improved cache validation.
* **Bug Fix:** Corrected the `$purge_time` to use UTC time (`gmdate`), ensuring consistency across different time zones.
* **Improvement:** Adjusted autoloader to prioritize the global autoloader with a fallback to the plugin's autoloader for better compatibility.
* **Enhancement:** Added detailed logging using `error_log` for improved debugging and validation.
* **Update:** Updated `composer.json` and `.gitignore` files, and removed `composer.lock` from the repository to streamline development.
* **Fix:** Corrected the plugin URI and package name in `composer.json` and the main plugin file to reflect the correct repository.

= 1.0.0 =
* Initial release of Cloudflare Cache Monitor.

== Upgrade Notice ==

= 1.3.0 =
* This release introduces a Logger class for improved logging, plus a new `ccm_define_posts_page` hook that lets you easily include a custom blog page (or any listing page) in cache purges.

== License ==

This plugin is licensed under the GPLv2 or later.
