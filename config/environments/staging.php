<?php
/**
 * Configuration overrides for WP_ENV === 'staging'
 *
 * @package iz-wp-lite
 * @ai-context Staging environment configuration mimicking production with debug logging enabled.
 * @ai-constraint Search engine indexing strictly disallowed.
 */

use function Env\env;

define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', false);
define('WP_DEBUG_LOG', env('WP_DEBUG_LOG') ?? true);
define('SCRIPT_DEBUG', false);
define('DISALLOW_INDEXING', true);

ini_set('display_errors', '0');
