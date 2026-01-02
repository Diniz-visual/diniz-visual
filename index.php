<?php get_header(); ?>

<section class="container">
  <h1 class="h4 fw-bold mb-3">Posts</h1>

  <div id="tbz-post-grid" class="row g-3">
    <?php if (have_posts()): while(have_posts()): the_post(); ?>
      <?php get_template_part('template-parts/card-post'); ?>
    <?php endwhile; else: ?>
      <p class="text-muted">Sem posts ainda.</p>
    <?php endif; ?>
  </div>

  <div class="mt-4 d-flex justify-content-center">
    <button id="tbz-load-more" class="btn btn-primary" type="button" data-paged="2">
      Carregar mais (AJAX)
    </button>
  </div>

  <p class="text-muted small mt-3">
    * Botão acima é apenas o exemplo de AJAX (load more). Pode remover quando quiser.
  </p>
</section>

<?php get_footer(); ?>
