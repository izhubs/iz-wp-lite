<?php
/**
 * Taxonomy Template for Tour Type.
 *
 * PHP version 8.1+
 *
 * @package IZTourTheme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Inherit the complete archive catalog layout and facet filtering
require get_template_directory() . '/archive-tour.php';
