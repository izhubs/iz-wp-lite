<?php
/**
 * Post Types and Taxonomies Registration for iZ Tour Engine.
 *
 * PHP version 8.1+
 *
 * @package IZTourEngine
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class IZ_Tour_CPT_Taxonomies
 *
 * Registers the Custom Post Types 'tour' and 'tour_booking', as well as
 * hierarchical taxonomies 'tour_destination' and 'tour_type'.
 *
 * DECISION: Dedicated CPT for tour_booking vs custom database table.
 * WHY: CPT tour_booking leverages native WordPress revision history, user capabilities,
 * post meta APIs, and admin search without adding migration maintenance overhead.
 * TRADE-OFF: Slight postmeta table growth on high-volume sites, mitigated by indexing.
 * REF: https://developer.wordpress.org/plugins/post-types/registering-custom-post-types/
 */
final class IZ_Tour_CPT_Taxonomies
{
    /**
     * Hook registration into WordPress core lifecycle.
     *
     * WHY: Post types and taxonomies must be registered on the 'init' hook
     * to ensure rewrites, query vars, and translations are properly bound.
     */
    public function register(): void
    {
        add_action('init', [$this, 'register_post_types']);
        add_action('init', [$this, 'register_taxonomies']);
    }

    /**
     * Register Custom Post Types: tour and tour_booking.
     *
     * WHY: 'tour' must be publicly queryable for frontend catalog and REST API.
     * 'tour_booking' must remain private to prevent customer PII exposure,
     * restricted exclusively to authenticated dashboard administrators.
     */
    public function register_post_types(): void
    {
        // 1. Tour CPT
        $tour_labels = [
            'name'                  => _x('Tours', 'Post type general name', 'iz-tour-engine'),
            'singular_name'         => _x('Tour', 'Post type singular name', 'iz-tour-engine'),
            'menu_name'             => _x('Quản Lý Tour', 'Admin Menu text', 'iz-tour-engine'),
            'name_admin_bar'        => _x('Tour', 'Add New on Toolbar', 'iz-tour-engine'),
            'add_new'               => __('Thêm Tour Mới', 'iz-tour-engine'),
            'add_new_item'          => __('Thêm Tour Du Lịch Mới', 'iz-tour-engine'),
            'new_item'              => __('Tour Mới', 'iz-tour-engine'),
            'edit_item'             => __('Chỉnh Sửa Tour', 'iz-tour-engine'),
            'view_item'             => __('Xem Tour', 'iz-tour-engine'),
            'all_items'             => __('Tất Cả Tour', 'iz-tour-engine'),
            'search_items'          => __('Tìm Kiếm Tour', 'iz-tour-engine'),
            'parent_item_colon'     => __('Tour Cha:', 'iz-tour-engine'),
            'not_found'             => __('Không tìm thấy tour nào.', 'iz-tour-engine'),
            'not_found_in_trash'    => __('Không có tour nào trong thùng rác.', 'iz-tour-engine'),
            'featured_image'        => _x('Ảnh Đại Diện Tour', 'Overrides featured image label', 'iz-tour-engine'),
            'set_featured_image'    => _x('Đặt ảnh đại diện', 'Overrides set featured image label', 'iz-tour-engine'),
            'remove_featured_image' => _x('Xóa ảnh đại diện', 'Overrides remove featured image label', 'iz-tour-engine'),
            'use_featured_image'    => _x('Dùng làm ảnh đại diện', 'Overrides use featured image label', 'iz-tour-engine'),
        ];

        $tour_args = [
            'labels'             => $tour_labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'tours', 'with_front' => false],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-palmtree',
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt'],
            'show_in_rest'       => true,
        ];

        register_post_type('tour', $tour_args);

        // 2. Tour Booking CPT (Private admin-only)
        $booking_labels = [
            'name'               => _x('Đơn Đặt Tour', 'Post type general name', 'iz-tour-engine'),
            'singular_name'      => _x('Đơn Đặt', 'Post type singular name', 'iz-tour-engine'),
            'menu_name'          => _x('Đơn Đặt Tour', 'Admin Menu text', 'iz-tour-engine'),
            'all_items'          => __('Tất Cả Đơn Đặt', 'iz-tour-engine'),
            'add_new'            => __('Tạo Đơn Thủ Công', 'iz-tour-engine'),
            'add_new_item'       => __('Tạo Đơn Đặt Tour Mới', 'iz-tour-engine'),
            'edit_item'          => __('Chi Tiết Đơn Đặt', 'iz-tour-engine'),
            'view_item'          => __('Xem Đơn Đặt', 'iz-tour-engine'),
            'search_items'       => __('Tìm Kiếm Đơn', 'iz-tour-engine'),
            'not_found'          => __('Không có đơn đặt tour nào.', 'iz-tour-engine'),
            'not_found_in_trash' => __('Không có đơn nào trong thùng rác.', 'iz-tour-engine'),
        ];

        $booking_args = [
            'labels'              => $booking_labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => 'edit.php?post_type=tour',
            'show_in_nav_menus'   => false,
            'show_in_admin_bar'   => false,
            'exclude_from_search' => true,
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'hierarchical'        => false,
            'supports'            => ['title'],
            'show_in_rest'        => false,
        ];

        register_post_type('tour_booking', $booking_args);
    }

    /**
     * Register Taxonomies: tour_destination and tour_type.
     *
     * WHY: Hierarchical taxonomies allow multi-tier destinations (e.g., Miền Trung -> Đà Nẵng)
     * and categorical clustering for SEO Silos and facet filtering.
     */
    public function register_taxonomies(): void
    {
        // 1. Destination Taxonomy
        $destination_labels = [
            'name'              => _x('Điểm Đến', 'taxonomy general name', 'iz-tour-engine'),
            'singular_name'     => _x('Điểm Đến', 'taxonomy singular name', 'iz-tour-engine'),
            'search_items'      => __('Tìm Điểm Đến', 'iz-tour-engine'),
            'all_items'         => __('Tất Cả Điểm Đến', 'iz-tour-engine'),
            'parent_item'       => __('Điểm Đến Cha', 'iz-tour-engine'),
            'parent_item_colon' => __('Điểm Đến Cha:', 'iz-tour-engine'),
            'edit_item'         => __('Chỉnh Sửa Điểm Đến', 'iz-tour-engine'),
            'update_item'       => __('Cập Nhật Điểm Đến', 'iz-tour-engine'),
            'add_new_item'      => __('Thêm Điểm Đến Mới', 'iz-tour-engine'),
            'new_item_name'     => __('Tên Điểm Đến Mới', 'iz-tour-engine'),
            'menu_name'         => __('Điểm Đến', 'iz-tour-engine'),
        ];

        register_taxonomy('tour_destination', ['tour'], [
            'hierarchical'      => true,
            'labels'            => $destination_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'diem-den', 'with_front' => false],
            'show_in_rest'      => true,
        ]);

        // 2. Tour Type Taxonomy
        $type_labels = [
            'name'              => _x('Loại Tour', 'taxonomy general name', 'iz-tour-engine'),
            'singular_name'     => _x('Loại Tour', 'taxonomy singular name', 'iz-tour-engine'),
            'search_items'      => __('Tìm Loại Tour', 'iz-tour-engine'),
            'all_items'         => __('Tất Cả Loại Tour', 'iz-tour-engine'),
            'parent_item'       => __('Loại Tour Cha', 'iz-tour-engine'),
            'parent_item_colon' => __('Loại Tour Cha:', 'iz-tour-engine'),
            'edit_item'         => __('Chỉnh Sửa Loại Tour', 'iz-tour-engine'),
            'update_item'       => __('Cập Nhật Loại Tour', 'iz-tour-engine'),
            'add_new_item'      => __('Thêm Loại Tour Mới', 'iz-tour-engine'),
            'new_item_name'     => __('Tên Loại Tour Mới', 'iz-tour-engine'),
            'menu_name'         => __('Loại Tour', 'iz-tour-engine'),
        ];

        register_taxonomy('tour_type', ['tour'], [
            'hierarchical'      => true,
            'labels'            => $type_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'loai-tour', 'with_front' => false],
            'show_in_rest'      => true,
        ]);
    }

    /**
     * Seeds initial taxonomy terms upon plugin activation.
     *
     * WHY: Ensures default business categories are ready immediately
     * without requiring manual entry by webmasters.
     */
    public static function seed_default_terms(): void
    {
        $default_types = [
            'Trong nước'   => 'trong-nuoc',
            'Quốc tế'      => 'quoc-te',
            'Combo'        => 'combo',
            'Teambuilding' => 'teambuilding',
        ];

        foreach ($default_types as $name => $slug) {
            if (!term_exists($slug, 'tour_type')) {
                wp_insert_term($name, 'tour_type', ['slug' => $slug]);
            }
        }
    }
}
