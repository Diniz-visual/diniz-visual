<?php get_header(); ?>

<section class="<?php echo esc_attr(ciaweb_container_class()); ?> py-16">
  <h1 class="text-3xl font-semibold tracking-tight"><?php the_archive_title(); ?></h1>
  <div class="mt-10 grid gap-6 md:grid-cols-3">
    <?php if (have_posts()): while (have_posts()): the_post(); ?>
      <a href="<?php the_permalink(); ?>" class="group rounded-2xl border border-white/10 bg-white/5 p-6 hover:bg-white/10 transition">
        <h2 class="text-lg font-semibold"><?php the_title(); ?></h2>
        <p class="mt-2 text-sm text-white/60 line-clamp-3"><?php echo wp_kses_post(get_the_excerpt()); ?></p>
      </a>
    <?php endwhile; endif; ?>
  </div>
  <div class="mt-10">
    <?php the_posts_pagination(['mid_size'=>1,'prev_text'=>'←','next_text'=>'→']); ?>
  </div>
</section>

<?php get_footer(); ?>
