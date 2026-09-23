<?php
/**
 * Fallback Archive and Blog Index Template.
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
?>

<?php iz_breadcrumbs(); ?>

<main id="primary" class="site-main iz-blog-index">
    <div class="iz-container">
        <header class="iz-archive-header">
            <h1 class="iz-archive-title"><?php esc_html_e('Tin Tức & Cẩm Nang Du Lịch', 'iz-tour-theme'); ?></h1>
            <p class="iz-archive-desc"><?php esc_html_e('Chia sẻ kinh nghiệm thực tế, cẩm nang du lịch và tin tức khuyến mãi mới nhất từ Gonatour.', 'iz-tour-theme'); ?></p>
        </header>

        <div class="iz-news-grid">
            <?php
            if (have_posts()) {
                while (have_posts()) {
                    the_post();
                    ?>
                    <article class="iz-news-card">
                        <div class="iz-news-thumb">
                            <a href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('medium_large', ['class' => 'iz-news-img']); ?>
                                <?php else : ?>
                                    <div class="iz-card-placeholder"><span>Gonatour Blog</span></div>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="iz-news-body">
                            <div class="iz-news-date">
                                <?php echo esc_html(get_the_date('d/m/Y')); ?>
                            </div>
                            <h3 class="iz-news-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <p class="iz-news-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18, '...')); ?></p>
                        </div>
                    </article>
                    <?php
                }
            } else {
                echo '<p>' . esc_html__('Chưa có bài viết nào được xuất bản.', 'iz-tour-theme') . '</p>';
            }
            ?>
        </div>

        <div class="iz-catalog-pagination">
            <?php
            the_posts_pagination([
                'prev_text' => '&laquo; Trước',
                'next_text' => 'Sau &raquo;',
            ]);
            ?>
        </div>
    </div>
</main>

<?php
get_footer();
