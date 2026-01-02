(function(){
  async function post(url, data){
    const body = new URLSearchParams(data);
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body
    });
    return res.json();
  }

  document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('tbz-load-more');
    const grid = document.getElementById('tbz-post-grid');
    if (!btn || !grid || typeof TBZ_AJAX === 'undefined') return;

    btn.addEventListener('click', async () => {
      btn.disabled = true;
      const paged = parseInt(btn.dataset.paged || '2', 10);

      try{
        const json = await post(TBZ_AJAX.url, {
          action: 'tbz_load_more',
          nonce: TBZ_AJAX.nonce,
          paged
        });

        if (json && json.success){
          const html = json.data.html || '';
          if (html.trim()) grid.insertAdjacentHTML('beforeend', html);

          const max = parseInt(json.data.max_pages || '1', 10);
          const next = paged + 1;
          btn.dataset.paged = String(next);

          if (next > max){
            btn.textContent = 'Fim';
            btn.disabled = true;
            btn.classList.add('btn-secondary');
            btn.classList.remove('btn-primary');
            return;
          }

          btn.disabled = false;
        } else {
          btn.disabled = false;
          console.error('AJAX falhou', json);
        }
      } catch (e){
        btn.disabled = false;
        console.error(e);
      }
    });
  });
})();