<?php
/**
 * Configuration overrides for WP_ENV === 'development'
 *
 * @package iz-wp-lite
 * @ai-context Development environment settings with full debug reporting.
 * @ai-constraint Never enable in production.
 */

use function Env\env;

define('SAVEQUERIES', true);
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);
define('WP_DEBUG_LOG', env('WP_DEBUG_LOG') ?? true);
define('WP_DISABLE_FATAL_ERROR_HANDLER', true);
define('SCRIPT_DEBUG', true);
define('DISALLOW_INDEXING', true);

ini_set('display_errors', '1');
