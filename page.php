<?php get_header(); ?>

<section class="<?php echo esc_attr(ciaweb_container_class()); ?> py-14">
  <?php if (have_posts()): while (have_posts()): the_post(); ?>
    <h1 class="text-3xl font-semibold tracking-tight"><?php the_title(); ?></h1>
    <div class="prose prose-invert mt-6 max-w-none">
      <?php the_content(); ?>
    </div>
  <?php endwhile; endif; ?>
</section>

<?php get_footer(); ?>
