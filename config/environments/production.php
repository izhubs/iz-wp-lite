<?php
/**
 * Configuration overrides for WP_ENV === 'production'
 *
 * @package iz-wp-lite
 * @ai-context Hardened production environment configuration with disabled error reporting.
 * @ai-constraint Strictly disable debug output and error display.
 */

use function Env\env;

define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', false);
define('WP_DEBUG_LOG', false);
define('SCRIPT_DEBUG', false);
define('DISALLOW_INDEXING', false);

ini_set('display_errors', '0');
