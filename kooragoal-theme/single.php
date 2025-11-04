<?php
get_header();
?>
<div class="container content-area with-sidebar">
    <div class="content-area__main">
        <?php
        while ( have_posts() ) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'single-article' ); ?>>
                <header class="single-article__header">
                    <h1 class="single-article__title"><?php the_title(); ?></h1>
                    <div class="single-article__meta">
                        <span><?php echo esc_html( get_the_date() ); ?></span>
                        <span><?php the_author_posts_link(); ?></span>
                        <span><?php comments_popup_link( __( 'بدون تعليقات', 'kooragoal' ), __( 'تعليق واحد', 'kooragoal' ), __( '% تعليقات', 'kooragoal' ) ); ?></span>
                    </div>
                </header>
                <?php if ( has_post_thumbnail() ) : ?>
                    <figure class="single-article__thumbnail">
                        <?php the_post_thumbnail( 'large', [ 'loading' => 'lazy' ] ); ?>
                    </figure>
                <?php endif; ?>
                <div class="single-article__content">
                    <?php the_content(); ?>
                </div>
                <footer class="single-article__footer">
                    <div class="single-article__tags"><?php the_tags(); ?></div>
                </footer>
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
