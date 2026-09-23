<?php
/**
 * Plugin Name:       iZ Tour Engine
 * Plugin URI:        https://izweb.vn
 * Description:       High-performance, self-contained tour management and booking engine with VietQR automated payment reconciliation.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            iZdigi Engineering
 * Author URI:        https://izweb.vn
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       iz-tour-engine
 * Domain Path:       /languages
 *
 * @package           IZTourEngine
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Global Plugin Constants
define('IZ_TOUR_VERSION', '1.0.0');
define('IZ_TOUR_FILE', __FILE__);
define('IZ_TOUR_PATH', plugin_dir_path(__FILE__));
define('IZ_TOUR_URL', plugin_dir_url(__FILE__));

/**
 * Class IZ_Tour_Engine
 *
 * Orchestrates plugin initialization, dependency loading, and lifecycle events.
 *
 * DECISION: Class loader without Composer autoloader.
 * WHY: Zero external plugin dependencies and zero build step. Directly includes
 * structured domain classes to ensure sub-10ms bootstrap latency.
 * REF: https://developer.wordpress.org/plugins/intro/
 */
final class IZ_Tour_Engine
{
    private static ?IZ_Tour_Engine $instance = null;

    private IZ_Tour_CPT_Taxonomies $cpt_taxonomies;
    private IZ_Tour_Meta_Boxes $meta_boxes;
    private IZ_Tour_VietQR $vietqr;
    private IZ_Tour_Admin_Booking $admin_booking;
    private IZ_Tour_REST_API $rest_api;

    /**
     * Singleton instance accessor.
     *
     * WHY: Guarantees single initialization across the WordPress execution cycle.
     *
     * @return self
     */
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     *
     * WHY: Private constructor prevents multiple instantiations.
     */
    private function __construct()
    {
        $this->load_dependencies();
        $this->init_components();
    }

    /**
     * Require internal class definitions.
     *
     * WHY: Loads module files in deterministic order.
     */
    private function load_dependencies(): void
    {
        require_once IZ_TOUR_PATH . 'includes/class-cpt-taxonomies.php';
        require_once IZ_TOUR_PATH . 'includes/class-meta-boxes.php';
        require_once IZ_TOUR_PATH . 'includes/class-vietqr.php';
        require_once IZ_TOUR_PATH . 'includes/class-admin-booking.php';
        require_once IZ_TOUR_PATH . 'includes/class-rest-api.php';
    }

    /**
     * Instantiate domain managers and attach their hooks.
     *
     * WHY: Encapsulates component lifecycle within distinct service objects.
     */
    private function init_components(): void
    {
        $this->cpt_taxonomies = new IZ_Tour_CPT_Taxonomies();
        $this->cpt_taxonomies->register();

        if (is_admin()) {
            $this->meta_boxes = new IZ_Tour_Meta_Boxes();
            $this->meta_boxes->register();

            $this->vietqr = new IZ_Tour_VietQR();
            $this->vietqr->register();

            $this->admin_booking = new IZ_Tour_Admin_Booking();
            $this->admin_booking->register();
        }

        $this->rest_api = new IZ_Tour_REST_API();
        $this->rest_api->register();
    }

    /**
     * Activation callback.
     *
     * WHY: Pre-registers post types, writes rewrite rules to .htaccess/cache,
     * and seeds initial default taxonomy terms.
     */
    public static function activate(): void
    {
        require_once IZ_TOUR_PATH . 'includes/class-cpt-taxonomies.php';

        $registrar = new IZ_Tour_CPT_Taxonomies();
        $registrar->register_post_types();
        $registrar->register_taxonomies();

        IZ_Tour_CPT_Taxonomies::seed_default_terms();

        flush_rewrite_rules();
    }

    /**
     * Deactivation callback.
     *
     * WHY: Cleans up rewrite rules to prevent orphaned URL routes.
     */
    public static function deactivate(): void
    {
        flush_rewrite_rules();
    }
}

// Lifecycle registration
register_activation_hook(IZ_TOUR_FILE, ['IZ_Tour_Engine', 'activate']);
register_deactivation_hook(IZ_TOUR_FILE, ['IZ_Tour_Engine', 'deactivate']);

// Bootstrap engine on plugins_loaded
add_action('plugins_loaded', static function () {
    IZ_Tour_Engine::instance();
});
