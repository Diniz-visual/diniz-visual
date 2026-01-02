<div class="col-12 col-md-6 col-lg-4">
  <a class="d-block p-3 border rounded-3 h-100 text-decoration-none" href="<?php the_permalink(); ?>">
    <h2 class="h6 fw-bold mb-2"><?php the_title(); ?></h2>
    <p class="text-muted small mb-0"><?php echo wp_kses_post(get_the_excerpt()); ?></p>
  </a>
</div>
