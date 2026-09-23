<?php
/**
 * Archive Tour Template for iZ Tour Theme.
 *
 * PHP version 8.1+
 *
 * @package IZTourTheme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Extract query parameters for faceted filter form
$cur_departure   = isset($_GET['departure']) ? sanitize_text_field(wp_unslash($_GET['departure'])) : '';
$cur_price_range = isset($_GET['price_range']) ? sanitize_text_field(wp_unslash($_GET['price_range'])) : '';
$cur_duration    = isset($_GET['duration']) ? sanitize_text_field(wp_unslash($_GET['duration'])) : '';
$cur_transport   = isset($_GET['transport']) ? sanitize_text_field(wp_unslash($_GET['transport'])) : '';
$cur_sort        = isset($_GET['sort']) ? sanitize_text_field(wp_unslash($_GET['sort'])) : 'newest';
$cur_search      = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';

// Build title & description
$page_title = __('Danh Sách Tour Du Lịch', 'iz-tour-theme');
$page_desc  = __('Khám phá trọn gói các hành trình du lịch trong nước và quốc tế với dịch vụ tiêu chuẩn, giá tốt nhất cùng Gonatour.', 'iz-tour-theme');

if (is_tax('tour_destination')) {
    $term = get_queried_object();
    if ($term instanceof \WP_Term) {
        $page_title = sprintf(__('Tour Du Lịch %s', 'iz-tour-theme'), $term->name);
        if (!empty($term->description)) {
            $page_desc = $term->description;
        }
    }
} elseif (is_tax('tour_type')) {
    $term = get_queried_object();
    if ($term instanceof \WP_Term) {
        $page_title = sprintf(__('%s Trọn Gói', 'iz-tour-theme'), $term->name);
    }
} elseif (!empty($cur_search)) {
    $page_title = sprintf(__('Kết Quả Tìm Kiếm: "%s"', 'iz-tour-theme'), $cur_search);
}

// Modify global query based on GET facet parameters if applicable
global $wp_query;
$query_args = $wp_query->query_vars;

$meta_queries = [];
if (!empty($cur_departure)) {
    $meta_queries[] = [
        'key'     => '_tour_departure_city',
        'value'   => $cur_departure,
        'compare' => 'LIKE',
    ];
}

if (!empty($cur_transport)) {
    $meta_queries[] = [
        'key'     => '_tour_transportation',
        'value'   => $cur_transport,
        'compare' => 'LIKE',
    ];
}

if (!empty($cur_duration)) {
    $meta_queries[] = [
        'key'     => '_tour_duration',
        'value'   => $cur_duration,
        'compare' => 'LIKE',
    ];
}

if (!empty($cur_price_range)) {
    if ($cur_price_range === 'under_3m') {
        $meta_queries[] = [
            'key'     => '_tour_price_adult',
            'value'   => 3000000,
            'type'    => 'NUMERIC',
            'compare' => '<',
        ];
    } elseif ($cur_price_range === '3m_7m') {
        $meta_queries[] = [
            'key'     => '_tour_price_adult',
            'value'   => [3000000, 7000000],
            'type'    => 'NUMERIC',
            'compare' => 'BETWEEN',
        ];
    } elseif ($cur_price_range === '7m_15m') {
        $meta_queries[] = [
            'key'     => '_tour_price_adult',
            'value'   => [7000000, 15000000],
            'type'    => 'NUMERIC',
            'compare' => 'BETWEEN',
        ];
    } elseif ($cur_price_range === 'above_15m') {
        $meta_queries[] = [
            'key'     => '_tour_price_adult',
            'value'   => 15000000,
            'type'    => 'NUMERIC',
            'compare' => '>',
        ];
    }
}

// Sorting logic
$orderby = 'date';
$order   = 'DESC';
$meta_key = '';

if ($cur_sort === 'price_asc') {
    $orderby  = 'meta_value_num';
    $meta_key = '_tour_price_adult';
    $order    = 'ASC';
} elseif ($cur_sort === 'price_desc') {
    $orderby  = 'meta_value_num';
    $meta_key = '_tour_price_adult';
    $order    = 'DESC';
}

$active_filters = !empty($meta_queries) || !empty($cur_search) || $cur_sort !== 'newest';
if ($active_filters) {
    $custom_query_args = array_merge($query_args, [
        'post_type'   => 'tour',
        'post_status' => 'publish',
        'orderby'     => $orderby,
        'order'       => $order,
    ]);

    if (!empty($meta_key)) {
        $custom_query_args['meta_key'] = $meta_key;
    }

    if (!empty($meta_queries)) {
        $custom_query_args['meta_query'] = array_merge(['relation' => 'AND'], $meta_queries);
    }

    if (!empty($cur_search)) {
        $custom_query_args['s'] = $cur_search;
    }

    $tours_query = new \WP_Query($custom_query_args);
} else {
    $tours_query = $wp_query;
}

$total_tours = $tours_query->found_posts;
?>

<?php iz_breadcrumbs(); ?>

<main id="primary" class="site-main iz-archive-page">
    <div class="iz-container">
        <!-- Archive Header Banner -->
        <header class="iz-archive-header">
            <h1 class="iz-archive-title"><?php echo esc_html($page_title); ?></h1>
            <p class="iz-archive-desc"><?php echo esc_html($page_desc); ?></p>
        </header>

        <!-- 2-Column Catalog Layout -->
        <div class="iz-catalog-layout">
            <!-- Sidebar Facet Filter -->
            <aside class="iz-catalog-sidebar" aria-label="<?php esc_attr_e('Bộ lọc tour', 'iz-tour-theme'); ?>">
                <div class="iz-filter-widget">
                    <div class="iz-filter-header">
                        <span class="iz-filter-heading">
                            <svg viewBox="0 0 20 20" width="16" height="16" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/></svg>
                            Bộ Lọc Tìm Kiếm
                        </span>
                        <?php if ($active_filters) : ?>
                            <a href="<?php echo esc_url(get_post_type_archive_link('tour')); ?>" class="iz-filter-reset">Xóa tất cả</a>
                        <?php endif; ?>
                    </div>

                    <form method="get" class="iz-facet-form" id="iz-facet-form">
                        <!-- Preserve current search keyword -->
                        <?php if (!empty($cur_search)) : ?>
                            <input type="hidden" name="search" value="<?php echo esc_attr($cur_search); ?>" />
                        <?php endif; ?>

                        <!-- Departure City Filter -->
                        <div class="iz-facet-group">
                            <h4 class="iz-facet-title">Nơi Khởi Hành</h4>
                            <select name="departure" class="iz-facet-select" onchange="this.form.submit()">
                                <option value=""><?php esc_html_e('-- Tất cả điểm đi --', 'iz-tour-theme'); ?></option>
                                <option value="TP.HCM" <?php selected($cur_departure, 'TP.HCM'); ?>>TP. Hồ Chí Minh</option>
                                <option value="Hà Nội" <?php selected($cur_departure, 'Hà Nội'); ?>>Hà Nội</option>
                                <option value="Đà Nẵng" <?php selected($cur_departure, 'Đà Nẵng'); ?>>Đà Nẵng</option>
                                <option value="Cần Thơ" <?php selected($cur_departure, 'Cần Thơ'); ?>>Cần Thơ</option>
                                <option value="Hải Phòng" <?php selected($cur_departure, 'Hải Phòng'); ?>>Hải Phòng</option>
                            </select>
                        </div>

                        <!-- Price Range Filter -->
                        <div class="iz-facet-group">
                            <h4 class="iz-facet-title">Khoảng Giá (VND)</h4>
                            <div class="iz-facet-radios">
                                <label class="iz-radio-label">
                                    <input type="radio" name="price_range" value="" <?php checked($cur_price_range, ''); ?> onchange="this.form.submit()" />
                                    <span>Tất cả mức giá</span>
                                </label>
                                <label class="iz-radio-label">
                                    <input type="radio" name="price_range" value="under_3m" <?php checked($cur_price_range, 'under_3m'); ?> onchange="this.form.submit()" />
                                    <span>Dưới 3.000.000 đ</span>
                                </label>
                                <label class="iz-radio-label">
                                    <input type="radio" name="price_range" value="3m_7m" <?php checked($cur_price_range, '3m_7m'); ?> onchange="this.form.submit()" />
                                    <span>3.000.000 đ - 7.000.000 đ</span>
                                </label>
                                <label class="iz-radio-label">
                                    <input type="radio" name="price_range" value="7m_15m" <?php checked($cur_price_range, '7m_15m'); ?> onchange="this.form.submit()" />
                                    <span>7.000.000 đ - 15.000.000 đ</span>
                                </label>
                                <label class="iz-radio-label">
                                    <input type="radio" name="price_range" value="above_15m" <?php checked($cur_price_range, 'above_15m'); ?> onchange="this.form.submit()" />
                                    <span>Trên 15.000.000 đ</span>
                                </label>
                            </div>
                        </div>

                        <!-- Duration Filter -->
                        <div class="iz-facet-group">
                            <h4 class="iz-facet-title">Thời Lượng Tour</h4>
                            <div class="iz-facet-radios">
                                <label class="iz-radio-label">
                                    <input type="radio" name="duration" value="" <?php checked($cur_duration, ''); ?> onchange="this.form.submit()" />
                                    <span>Tất cả thời lượng</span>
                                </label>
                                <label class="iz-radio-label">
                                    <input type="radio" name="duration" value="3" <?php checked($cur_duration, '3'); ?> onchange="this.form.submit()" />
                                    <span>1 - 3 Ngày</span>
                                </label>
                                <label class="iz-radio-label">
                                    <input type="radio" name="duration" value="5" <?php checked($cur_duration, '5'); ?> onchange="this.form.submit()" />
                                    <span>4 - 5 Ngày</span>
                                </label>
                                <label class="iz-radio-label">
                                    <input type="radio" name="duration" value="7" <?php checked($cur_duration, '7'); ?> onchange="this.form.submit()" />
                                    <span>Trên 5 Ngày</span>
                                </label>
                            </div>
                        </div>

                        <!-- Transportation Filter -->
                        <div class="iz-facet-group">
                            <h4 class="iz-facet-title">Phương Tiện Di Chuyển</h4>
                            <div class="iz-facet-radios">
                                <label class="iz-radio-label">
                                    <input type="radio" name="transport" value="" <?php checked($cur_transport, ''); ?> onchange="this.form.submit()" />
                                    <span>Tất cả phương tiện</span>
                                </label>
                                <label class="iz-radio-label">
                                    <input type="radio" name="transport" value="Máy bay" <?php checked($cur_transport, 'Máy bay'); ?> onchange="this.form.submit()" />
                                    <span>Máy bay</span>
                                </label>
                                <label class="iz-radio-label">
                                    <input type="radio" name="transport" value="Ô tô" <?php checked($cur_transport, 'Ô tô'); ?> onchange="this.form.submit()" />
                                    <span>Ô tô du lịch</span>
                                </label>
                                <label class="iz-radio-label">
                                    <input type="radio" name="transport" value="Du thuyền" <?php checked($cur_transport, 'Du thuyền'); ?> onchange="this.form.submit()" />
                                    <span>Du thuyền</span>
                                </label>
                            </div>
                        </div>

                        <noscript>
                            <button type="submit" class="iz-filter-submit">Áp Dụng Lọc</button>
                        </noscript>
                    </form>
                </div>

                <!-- Hotline Assistance Widget -->
                <div class="iz-sidebar-help">
                    <span class="iz-help-icon">📞</span>
                    <strong class="iz-help-title">Cần Tư Vấn Nhanh?</strong>
                    <p class="iz-help-text">Gọi ngay chuyên viên Gonatour để được báo giá và lịch khởi hành nhanh nhất:</p>
                    <a href="tel:0784849849" class="iz-help-phone">0784 849 849</a>
                </div>
            </aside>

            <!-- Main Content Area -->
            <section class="iz-catalog-main">
                <!-- Sorting & Counter Toolbar -->
                <div class="iz-catalog-toolbar">
                    <div class="iz-toolbar-count">
                        Tìm thấy <strong><?php echo esc_html((string) $total_tours); ?></strong> tour phù hợp
                    </div>

                    <div class="iz-toolbar-sort">
                        <label for="catalog-sort" class="iz-sort-label">Sắp xếp:</label>
                        <select id="catalog-sort" name="sort" class="iz-sort-select" onchange="izApplySort(this.value)">
                            <option value="newest" <?php selected($cur_sort, 'newest'); ?>>Mới nhất</option>
                            <option value="price_asc" <?php selected($cur_sort, 'price_asc'); ?>>Giá tăng dần</option>
                            <option value="price_desc" <?php selected($cur_sort, 'price_desc'); ?>>Giá giảm dần</option>
                        </select>
                    </div>
                </div>

                <!-- Tours Grid -->
                <div class="iz-tour-grid iz-grid-3">
                    <?php
                    if ($tours_query->have_posts()) {
                        while ($tours_query->have_posts()) {
                            $tours_query->the_post();
                            iz_render_tour_card(get_the_ID());
                        }
                    } else {
                        // Empty state / Fallback cards
                        ?>
                        <div class="iz-catalog-empty">
                            <svg viewBox="0 0 24 24" width="48" height="48" fill="var(--iz-text-muted)"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                            <h3>Không tìm thấy tour phù hợp tiêu chí</h3>
                            <p>Vui lòng thử điều chỉnh lại bộ lọc hoặc liên hệ hotline <strong>0784 849 849</strong> để được hỗ trợ xếp tour theo yêu cầu.</p>
                            <a href="<?php echo esc_url(get_post_type_archive_link('tour')); ?>" class="iz-btn-primary">Xem tất cả tour</a>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <!-- Pagination -->
                <div class="iz-catalog-pagination">
                    <?php
                    echo paginate_links([
                        'total'     => $tours_query->max_num_pages,
                        'current'   => max(1, get_query_var('paged')),
                        'prev_text' => '&laquo; Trước',
                        'next_text' => 'Sau &raquo;',
                        'type'      => 'list',
                    ]);
                    ?>
                </div>
            </section>
        </div>
    </div>
</main>

<script>
function izApplySort(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', val);
    window.location.href = url.toString();
}
</script>

<?php
if ($active_filters) {
    wp_reset_postdata();
}
get_footer();
