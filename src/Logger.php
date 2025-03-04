<?php

/**
 * Logger Class
 *
 * @package CloudflareCacheMonitor
 */

namespace CloudflareCacheMonitor;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Logger
{
    public static function log($message)
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[CCM] ' . $message);
        }
    }
}
