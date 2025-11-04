<?php
get_header();
?>
<div class="container content-area with-sidebar">
    <div class="content-area__main">
        <header class="archive-header">
            <h1 class="archive-title"><?php the_archive_title(); ?></h1>
            <div class="archive-description"><?php the_archive_description(); ?></div>
        </header>
        <?php if ( have_posts() ) : ?>
            <div class="posts-grid">
                <?php
                while ( have_posts() ) :
                    the_post();
                    get_template_part( 'template-parts/content', get_post_type() );
                endwhile;
                ?>
            </div>
            <div class="pagination">
                <?php the_posts_pagination(); ?>
            </div>
        <?php else : ?>
            <p><?php esc_html_e( 'لا توجد نتائج.', 'kooragoal' ); ?></p>
        <?php endif; ?>
    </div>
    <?php get_sidebar(); ?>
</div>
<?php
get_footer();
