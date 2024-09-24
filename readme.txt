=== Cloudflare Cache Monitor ===
Contributors: rafaell1995
Tags: cloudflare, cache, monitor, wordpress
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 5.6
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A plugin to monitor Cloudflare cache purges and validate content updates for WordPress sites.

== Description ==

Cloudflare Cache Monitor is a plugin that integrates with Cloudflare to monitor cache purges and validate that content updates are properly reflected on your WordPress site.

**Features:**

- Sends purge requests to a Cloudflare Worker.
- Validates content updates after cache purge.
- Provides a settings page to configure the Worker URL and API Key.
- **New in v1.1.0:** Sends `post_name` to the Worker for precise URL filtering.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/cloudflare-cache-monitor` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to 'Settings' -> 'Cloudflare Cache Monitor' to configure the plugin.

== Frequently Asked Questions ==

= How do I obtain the Worker URL and API Key? =

You need to set up a Cloudflare Worker that processes the purge requests. The API Key is used for authentication with the Worker.

= Is this plugin compatible with other caching plugins? =

Yes, it is designed to work alongside other caching plugins, but its primary function is to interact with Cloudflare's caching system.

== Screenshots ==

1. **Settings Page** - Configure your Worker URL and API Key.

== Changelog ==

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

= 1.1.0 =
This update includes new features, important bug fixes, and improvements. It is recommended to update to this version for enhanced functionality and stability.

== License ==

This plugin is licensed under the GPLv2 or later.

