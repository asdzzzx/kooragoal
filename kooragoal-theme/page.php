<?php
get_header();
?>
<div class="container content-area with-sidebar">
    <div class="content-area__main">
        <?php
        while ( have_posts() ) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'page-article' ); ?>>
                <header class="page-article__header">
                    <h1 class="page-article__title"><?php the_title(); ?></h1>
                </header>
                <div class="page-article__content">
                    <?php the_content(); ?>
                </div>
            </article>
            <?php
            if ( comments_open() || get_comments_number() ) {
                comments_template();
            }
        endwhile;
        ?>
    </div>
    <?php get_sidebar(); ?>
</div>
<?php
get_footer();
