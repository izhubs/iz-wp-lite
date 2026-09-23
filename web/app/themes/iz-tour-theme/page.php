<?php
/**
 * Standard Page Template.
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

<main id="primary" class="site-main iz-standard-page">
    <div class="iz-container">
        <article class="iz-page-card">
            <header class="iz-page-header">
                <h1 class="iz-page-title"><?php the_title(); ?></h1>
            </header>

            <div class="iz-entry-content">
                <?php
                while (have_posts()) {
                    the_post();
                    the_content();
                }
                ?>
            </div>
        </article>
    </div>
</main>

<?php
get_footer();
