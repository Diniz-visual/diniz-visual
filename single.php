<?php get_header(); ?>

<section class="<?php echo esc_attr(ciaweb_container_class()); ?> py-14">
  <?php if (have_posts()): while (have_posts()): the_post(); ?>
    <article class="max-w-3xl">
      <p class="text-sm text-white/50"><?php echo get_the_date(); ?></p>
      <h1 class="mt-2 text-3xl font-semibold tracking-tight"><?php the_title(); ?></h1>

      <?php if (has_post_thumbnail()): ?>
        <div class="mt-6 overflow-hidden rounded-2xl border border-white/10">
          <?php the_post_thumbnail('large', ['class' => 'h-auto w-full']); ?>
        </div>
      <?php endif; ?>

      <div class="prose prose-invert mt-8 max-w-none">
        <?php the_content(); ?>
      </div>
    </article>
  <?php endwhile; endif; ?>
</section>

<?php get_footer(); ?>
