<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <a href="<?php the_permalink(); ?>" class="post-card__thumbnail">
            <?php the_post_thumbnail( 'large', [ 'loading' => 'lazy' ] ); ?>
        </a>
    <?php endif; ?>
    <div class="post-card__content">
        <h2 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <div class="post-card__meta">
            <span class="post-card__date"><?php echo esc_html( get_the_date() ); ?></span>
            <span class="post-card__author"><?php the_author_posts_link(); ?></span>
        </div>
        <div class="post-card__excerpt"><?php the_excerpt(); ?></div>
        <a class="post-card__read-more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'قراءة المزيد', 'kooragoal' ); ?></a>
    </div>
</article>
