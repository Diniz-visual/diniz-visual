document.addEventListener('DOMContentLoaded', () => {
  if (typeof Swiper === 'undefined') return;
  const el = document.querySelector('.tbzHero');
  if (!el) return;

  new Swiper(el, {
    loop: true,
    speed: 600,
    autoplay: { delay: 4500, disableOnInteraction: false },
    pagination: { el: '.swiper-pagination', clickable: true },
  });
});