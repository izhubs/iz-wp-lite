<?php
/**
 * Single Post Template for Blog & Travel Guides.
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

// Fetch hot tours for sidebar
$sidebar_tours = new \WP_Query([
    'post_type'      => 'tour',
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'orderby'        => 'rand',
]);
?>

<?php iz_breadcrumbs(); ?>

<main id="primary" class="site-main iz-single-post">
    <div class="iz-container">
        <div class="iz-post-layout-grid">
            <!-- Article Main Content -->
            <article class="iz-post-article">
                <header class="iz-post-header">
                    <div class="iz-post-cat-wrap">
                        <?php
                        $categories = get_the_category();
                        if (!empty($categories)) {
                            foreach ($categories as $cat) {
                                echo '<a href="' . esc_url(get_category_link($cat->term_id)) . '" class="iz-cat-badge">' . esc_html($cat->name) . '</a> ';
                            }
                        } else {
                            echo '<span class="iz-cat-badge">Cẩm Nang Du Lịch</span>';
                        }
                        ?>
                    </div>

                    <h1 class="iz-post-title"><?php the_title(); ?></h1>

                    <div class="iz-post-meta">
                        <span class="iz-post-date">
                            <svg viewBox="0 0 20 20" width="13" height="13" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                            Ngày đăng: <?php echo esc_html(get_the_date('d/m/Y')); ?>
                        </span>
                        <span class="iz-post-author">
                            Tác giả: <strong><?php echo esc_html(get_the_author()); ?></strong>
                        </span>
                    </div>
                </header>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="iz-post-featured-media">
                        <?php the_post_thumbnail('large', ['class' => 'iz-post-hero-img']); ?>
                    </div>
                <?php endif; ?>

                <div class="iz-entry-content">
                    <?php
                    the_content();

                    wp_link_pages([
                        'before' => '<div class="page-links">' . esc_html__('Trang:', 'iz-tour-theme'),
                        'after'  => '</div>',
                    ]);
                    ?>
                </div>

                <!-- Footer tags & Share -->
                <footer class="iz-post-footer">
                    <div class="iz-post-tags">
                        <?php the_tags('<span class="iz-tag-label">Từ khóa:</span> ', ' '); ?>
                    </div>
                    <div class="iz-post-share">
                        <span>Chia sẻ bài viết:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode(get_permalink()); ?>" target="_blank" rel="noopener noreferrer" class="iz-share-btn fb">Facebook</a>
                        <a href="https://zalo.me/share?url=<?php echo rawurlencode(get_permalink()); ?>" target="_blank" rel="noopener noreferrer" class="iz-share-btn zalo">Zalo</a>
                    </div>
                </footer>
            </article>

            <!-- Sidebar -->
            <aside class="iz-post-sidebar">
                <div class="iz-sidebar-widget">
                    <h3 class="iz-widget-title">Tour Du Lịch Bán Chạy</h3>
                    <div class="iz-sidebar-tours-list">
                        <?php
                        if ($sidebar_tours->have_posts()) {
                            while ($sidebar_tours->have_posts()) {
                                $sidebar_tours->the_post();
                                $p_id = get_the_ID();
                                $price = (int) get_post_meta($p_id, '_tour_price_adult', true);
                                ?>
                                <div class="iz-side-tour-item">
                                    <a href="<?php the_permalink(); ?>" class="iz-side-thumb">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <?php the_post_thumbnail('thumbnail'); ?>
                                        <?php else : ?>
                                            <div class="iz-card-placeholder"><span>Tour</span></div>
                                        <?php endif; ?>
                                    </a>
                                    <div class="iz-side-info">
                                        <h4 class="iz-side-tour-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                        <div class="iz-side-price"><?php echo esc_html(iz_format_price($price)); ?></div>
                                    </div>
                                </div>
                                <?php
                            }
                            wp_reset_postdata();
                        }
                        ?>
                    </div>
                </div>

                <div class="iz-sidebar-help">
                    <span class="iz-help-icon">📞</span>
                    <strong class="iz-help-title">Tư Vấn Tour 24/7</strong>
                    <p class="iz-help-text">Liên hệ Gonatour để nhận lịch khởi hành và ưu đãi tốt nhất:</p>
                    <a href="tel:0784849849" class="iz-help-phone">0784 849 849</a>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php
get_footer();
