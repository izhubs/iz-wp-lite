<?php
/**
 * Theme bootstrap and core helper functions for iZ Tour Theme.
 *
 * PHP version 8.1+
 *
 * @package IZTourTheme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('IZ_THEME_VERSION', '1.0.0');
define('IZ_THEME_URI', get_template_directory_uri());
define('IZ_THEME_PATH', get_template_directory());

/**
 * Configure core theme capabilities and navigation menus.
 *
 * DECISION: Strict HTML5 markup and explicit image dimensions.
 * WHY: Eliminates layout shift (CLS), ensures modern semantic tags for SEO,
 * and maintains unified aspect ratios across cards and hero banners.
 */
function iz_tour_theme_setup(): void
{
    // Let WordPress manage the document title
    add_theme_support('title-tag');

    // Enable Featured Images
    add_theme_support('post-thumbnails');

    // Optimized image sizes for Gonatour card ratios
    add_image_size('iz-tour-card', 640, 420, true);
    add_image_size('iz-tour-banner', 1280, 560, true);
    add_image_size('iz-tour-thumb', 200, 140, true);

    // Switch default core markup to valid HTML5
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    // Align wide support for Gutenberg blocks
    add_theme_support('align-wide');

    // Register theme navigation menus
    register_nav_menus([
        'primary-menu' => __('Menu Chính (Desktop Mega Menu)', 'iz-tour-theme'),
        'top-menu'     => __('Menu Top Bar', 'iz-tour-theme'),
        'footer-menu'  => __('Menu Chân Trang', 'iz-tour-theme'),
    ]);
}
add_action('after_setup_theme', 'iz_tour_theme_setup');

/**
 * Enqueue theme stylesheets and frontend scripts.
 *
 * DECISION: Single combined main.css + tour-frontend.js with zero external CDN.
 * WHY: Guarantees 100% offline self-containment, privacy compliance (GDPR/APEC),
 * and avoids DNS lookups/render blocking from third-party networks.
 */
function iz_tour_enqueue_scripts(): void
{
    // Main stylesheet
    wp_enqueue_style(
        'iz-tour-style',
        get_stylesheet_uri(),
        [],
        IZ_THEME_VERSION
    );

    // Component and layout styling
    wp_enqueue_style(
        'iz-tour-main',
        IZ_THEME_URI . '/assets/css/main.css',
        ['iz-tour-style'],
        IZ_THEME_VERSION
    );

    // Frontend interaction script
    wp_enqueue_script(
        'iz-tour-frontend',
        IZ_THEME_URI . '/assets/js/tour-frontend.js',
        [],
        IZ_THEME_VERSION,
        true
    );

    // Localize parameters for AJAX/REST API consumption
    wp_localize_script('iz-tour-frontend', 'izTourConfig', [
        'restUrl'        => esc_url_raw(rest_url()),
        'bookEndpoint'   => esc_url_raw(rest_url('iz-tour/v1/book')),
        'toursEndpoint'  => esc_url_raw(rest_url('iz-tour/v1/tours')),
        'restNonce'      => wp_create_nonce('wp_rest'),
        'homeUrl'        => esc_url_raw(home_url('/')),
        'bookingPageUrl' => esc_url_raw(home_url('/dat-tour/')),
        'currencySymbol' => 'đ',
        'hotline'        => '0784 849 849',
        'hotlineTel'     => '0784849849',
    ]);
}
add_action('wp_enqueue_scripts', 'iz_tour_enqueue_scripts');

/**
 * Format raw currency numbers to Vietnamese standard notation.
 *
 * WHY: Standardizes price presentation across catalog cards, modals, and checkout.
 *
 * @param int|float|string $amount Raw number in VND.
 * @return string Formatted price string (e.g., "5.990.000 đ" or "Liên hệ").
 */
function iz_format_price(int|float|string $amount): string
{
    $val = (int) $amount;
    if ($val <= 0) {
        return __('Liên hệ', 'iz-tour-theme');
    }
    return number_format($val, 0, ',', '.') . ' đ';
}

/**
 * Compute and return display badge metadata for a given tour.
 *
 * WHY: Highlights promotional discounts, bestseller status, or new departures
 * at the top corner of tour cards.
 *
 * @param int $tour_id Post ID of the tour.
 * @return array Array containing 'label' and 'class'.
 */
function iz_get_tour_badge(int $tour_id): array
{
    $custom_badge = (string) get_post_meta($tour_id, '_tour_badge', true);
    if (!empty($custom_badge)) {
        return [
            'label' => $custom_badge,
            'class' => 'iz-badge-custom',
        ];
    }

    $price_adult = (int) get_post_meta($tour_id, '_tour_price_adult', true);
    $price_sale  = (int) get_post_meta($tour_id, '_tour_price_sale', true);

    if ($price_sale > 0 && $price_adult > $price_sale) {
        $percent = (int) round((($price_adult - $price_sale) / $price_adult) * 100);
        return [
            'label' => sprintf('-%d%%', $percent),
            'class' => 'iz-badge-sale',
        ];
    }

    // Default badge based on tour code or category
    $tour_code = (string) get_post_meta($tour_id, '_tour_code', true);
    if (!empty($tour_code)) {
        return [
            'label' => 'MÃ: ' . $tour_code,
            'class' => 'iz-badge-code',
        ];
    }

    return [
        'label' => __('Giá Tốt', 'iz-tour-theme'),
        'class' => 'iz-badge-hot',
    ];
}

/**
 * Render accessible SVG star rating markup.
 *
 * WHY: Eliminates external icon font downloads while guaranteeing crisp 5-star
 * visual anchors for trust and E-E-A-T presentation.
 *
 * @param float|int $rating Star rating value between 1 and 5.
 * @return string Inline SVG star markup.
 */
function iz_render_stars(float|int $rating = 5.0): string
{
    $clamped = max(1.0, min(5.0, (float) $rating));
    $full_stars = (int) floor($clamped);
    $output = '<div class="iz-stars" aria-label="' . sprintf(esc_attr__('Đánh giá %s trên 5 sao', 'iz-tour-theme'), (string) $clamped) . '">';

    for ($i = 1; $i <= 5; $i++) {
        $color = $i <= $full_stars ? 'var(--iz-amber-gold)' : 'var(--iz-border-dark)';
        $output .= '<svg class="iz-star-icon" viewBox="0 0 20 20" width="14" height="14" fill="' . esc_attr($color) . '" aria-hidden="true">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>';
    }

    $output .= '</div>';
    return $output;
}

/**
 * Retrieve and normalize upcoming departure dates for a given tour.
 *
 * WHY: Parses multi-line textarea metadata into clean array of strings.
 *
 * @param int $tour_id Post ID.
 * @return array List of date strings.
 */
function iz_get_tour_departures(int $tour_id): array
{
    $raw = (string) get_post_meta($tour_id, '_tour_departure_dates', true);
    if (empty($raw)) {
        return [];
    }

    $lines = explode("\n", str_replace("\r", "", $raw));
    $dates = [];
    foreach ($lines as $line) {
        $clean = trim($line);
        if ($clean !== '') {
            $dates[] = $clean;
        }
    }
    return $dates;
}

/**
 * Retrieve structured multi-day itinerary.
 *
 * WHY: Deserializes JSON itinerary structure stored by iz-tour-engine.
 *
 * @param int $tour_id Post ID.
 * @return array Array of days with keys: day, title, desc.
 */
function iz_get_tour_itinerary(int $tour_id): array
{
    $raw = (string) get_post_meta($tour_id, '_tour_itinerary', true);
    if (empty($raw)) {
        return [];
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Render Breadcrumbs navigation for SEO and user orientation.
 *
 * WHY: Generates Google Rich Results Schema BreadcrumbList and semantic markup.
 */
function iz_breadcrumbs(): void
{
    if (is_front_page()) {
        return;
    }

    $home_url   = home_url('/');
    $home_title = __('Trang Chủ', 'iz-tour-theme');

    echo '<nav class="iz-breadcrumbs" aria-label="' . esc_attr__('Đường dẫn trang', 'iz-tour-theme') . '">';
    echo '<div class="iz-container">';
    echo '<ol class="iz-breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">';

    // Item 1: Home
    echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="iz-breadcrumb-item">';
    echo '<a href="' . esc_url($home_url) . '" itemprop="item"><span itemprop="name">' . esc_html($home_title) . '</span></a>';
    echo '<meta itemprop="position" content="1" />';
    echo '<span class="iz-breadcrumb-separator">/</span>';
    echo '</li>';

    $position = 2;

    if (is_singular('tour')) {
        $tour_id = get_the_ID();
        $terms   = get_the_terms($tour_id, 'tour_destination');

        if (!empty($terms) && !is_wp_error($terms)) {
            $term = reset($terms);
            $term_link = get_term_link($term);
            echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="iz-breadcrumb-item">';
            echo '<a href="' . esc_url($term_link) . '" itemprop="item"><span itemprop="name">' . esc_html($term->name) . '</span></a>';
            echo '<meta itemprop="position" content="' . esc_attr((string) $position) . '" />';
            echo '<span class="iz-breadcrumb-separator">/</span>';
            echo '</li>';
            $position++;
        }

        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="iz-breadcrumb-item is-active">';
        echo '<span itemprop="name">' . esc_html(get_the_title()) . '</span>';
        echo '<meta itemprop="position" content="' . esc_attr((string) $position) . '" />';
        echo '</li>';
    } elseif (is_post_type_archive('tour')) {
        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="iz-breadcrumb-item is-active">';
        echo '<span itemprop="name">' . esc_html__('Tất Cả Tour Du Lịch', 'iz-tour-theme') . '</span>';
        echo '<meta itemprop="position" content="' . esc_attr((string) $position) . '" />';
        echo '</li>';
    } elseif (is_tax(['tour_destination', 'tour_type'])) {
        $term = get_queried_object();
        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="iz-breadcrumb-item">';
        echo '<a href="' . esc_url(get_post_type_archive_link('tour')) . '" itemprop="item"><span itemprop="name">' . esc_html__('Tour Du Lịch', 'iz-tour-theme') . '</span></a>';
        echo '<meta itemprop="position" content="' . esc_attr((string) $position) . '" />';
        echo '<span class="iz-breadcrumb-separator">/</span>';
        echo '</li>';
        $position++;

        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="iz-breadcrumb-item is-active">';
        echo '<span itemprop="name">' . esc_html($term->name ?? '') . '</span>';
        echo '<meta itemprop="position" content="' . esc_attr((string) $position) . '" />';
        echo '</li>';
    } elseif (is_single()) {
        $categories = get_the_category();
        if (!empty($categories)) {
            $cat = reset($categories);
            echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="iz-breadcrumb-item">';
            echo '<a href="' . esc_url(get_category_link($cat->term_id)) . '" itemprop="item"><span itemprop="name">' . esc_html($cat->name) . '</span></a>';
            echo '<meta itemprop="position" content="' . esc_attr((string) $position) . '" />';
            echo '<span class="iz-breadcrumb-separator">/</span>';
            echo '</li>';
            $position++;
        }
        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="iz-breadcrumb-item is-active">';
        echo '<span itemprop="name">' . esc_html(get_the_title()) . '</span>';
        echo '<meta itemprop="position" content="' . esc_attr((string) $position) . '" />';
        echo '</li>';
    } elseif (is_page()) {
        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="iz-breadcrumb-item is-active">';
        echo '<span itemprop="name">' . esc_html(get_the_title()) . '</span>';
        echo '<meta itemprop="position" content="' . esc_attr((string) $position) . '" />';
        echo '</li>';
    }

    echo '</ol>';
    echo '</div>';
    echo '</nav>';
}

/**
 * Output reusable Tour Card component.
 *
 * WHY: Centralizes 100% of Gonatour tour card markup to ensure uniform rendering
 * across Homepage sliders/tabs, Search Results, Category archives, and Related tours.
 *
 * @param int|\WP_Post $tour_post Post object or Post ID.
 * @param array        $options   Optional parameters: flash_sale, show_seats.
 */
function iz_render_tour_card(int|\WP_Post $tour_post, array $options = []): void
{
    $post = get_post($tour_post);
    if (!$post instanceof \WP_Post) {
        return;
    }

    $tour_id       = $post->ID;
    $permalink     = get_permalink($tour_id);
    $title         = get_the_title($tour_id);
    $duration      = (string) get_post_meta($tour_id, '_tour_duration', true) ?: '3N2Đ';
    $departure     = (string) get_post_meta($tour_id, '_tour_departure_city', true) ?: 'TP.HCM';
    $transport     = (string) get_post_meta($tour_id, '_tour_transportation', true) ?: 'Máy bay / Ô tô';
    $price_adult   = (int) get_post_meta($tour_id, '_tour_price_adult', true);
    $price_sale    = (int) get_post_meta($tour_id, '_tour_price_sale', true);
    $tour_code     = (string) get_post_meta($tour_id, '_tour_code', true);
    $dates         = iz_get_tour_departures($tour_id);
    $next_date     = !empty($dates) ? $dates[0] : 'Liên hệ';
    $badge         = iz_get_tour_badge($tour_id);
    $is_flash_sale = !empty($options['flash_sale']);
    $seats_left    = $options['seats_left'] ?? wp_rand(2, 6);

    ?>
    <article class="iz-tour-card <?php echo $is_flash_sale ? 'is-flash-sale' : ''; ?>" data-tour-id="<?php echo esc_attr((string) $tour_id); ?>">
        <div class="iz-card-media">
            <a href="<?php echo esc_url($permalink); ?>" aria-label="<?php echo esc_attr($title); ?>">
                <?php if (has_post_thumbnail($tour_id)) : ?>
                    <?php echo get_the_post_thumbnail($tour_id, 'iz-tour-card', ['class' => 'iz-card-img', 'loading' => 'lazy']); ?>
                <?php else : ?>
                    <div class="iz-card-placeholder">
                        <span><?php esc_html_e('Du Lịch Trọn Gói', 'iz-tour-theme'); ?></span>
                    </div>
                <?php endif; ?>
            </a>

            <?php if (!empty($badge['label'])) : ?>
                <span class="iz-card-badge <?php echo esc_attr($badge['class']); ?>">
                    <?php echo esc_html($badge['label']); ?>
                </span>
            <?php endif; ?>

            <?php if ($is_flash_sale) : ?>
                <div class="iz-card-flash-ribbon">
                    <span>⚡ GIỜ CHÓT</span>
                </div>
            <?php endif; ?>

            <div class="iz-card-meta-overlay">
                <span class="iz-meta-item">
                    <svg viewBox="0 0 20 20" width="13" height="13" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                    <?php echo esc_html($duration); ?>
                </span>
                <span class="iz-meta-item">
                    <svg viewBox="0 0 20 20" width="13" height="13" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                    <?php echo esc_html($departure); ?>
                </span>
            </div>
        </div>

        <div class="iz-card-body">
            <?php if (!empty($tour_code)) : ?>
                <div class="iz-card-code">Mã tour: <strong><?php echo esc_html($tour_code); ?></strong></div>
            <?php endif; ?>

            <h3 class="iz-card-title">
                <a href="<?php echo esc_url($permalink); ?>" title="<?php echo esc_attr($title); ?>">
                    <?php echo esc_html($title); ?>
                </a>
            </h3>

            <div class="iz-card-rating-row">
                <?php echo iz_render_stars(5.0); ?>
                <span class="iz-card-reviews">(100% hài lòng)</span>
            </div>

            <div class="iz-card-specs">
                <div class="iz-spec-line">
                    <span class="iz-spec-label">Khởi hành:</span>
                    <span class="iz-spec-value"><?php echo esc_html($next_date); ?></span>
                </div>
                <div class="iz-spec-line">
                    <span class="iz-spec-label">Phương tiện:</span>
                    <span class="iz-spec-value"><?php echo esc_html($transport); ?></span>
                </div>
            </div>

            <?php if ($is_flash_sale) : ?>
                <div class="iz-flash-seats">
                    <div class="iz-seats-bar"><div class="iz-seats-fill" style="width: 75%;"></div></div>
                    <span class="iz-seats-text">Chỉ còn <strong><?php echo esc_html((string) $seats_left); ?></strong> chỗ cuối</span>
                </div>
            <?php endif; ?>

            <div class="iz-card-footer">
                <div class="iz-price-block">
                    <?php if ($price_sale > 0 && $price_adult > $price_sale) : ?>
                        <div class="iz-old-price"><?php echo esc_html(iz_format_price($price_adult)); ?></div>
                        <div class="iz-current-price"><?php echo esc_html(iz_format_price($price_sale)); ?></div>
                    <?php else : ?>
                        <div class="iz-price-label">Giá từ:</div>
                        <div class="iz-current-price"><?php echo esc_html(iz_format_price($price_adult)); ?></div>
                    <?php endif; ?>
                </div>

                <a href="<?php echo esc_url($permalink); ?>" class="iz-card-btn">
                    <span><?php esc_html_e('Đặt Ngay', 'iz-tour-theme'); ?></span>
                    <svg viewBox="0 0 20 20" width="14" height="14" fill="currentColor"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </a>
            </div>
        </div>
    </article>
    <?php
}
