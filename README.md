# Tema Base Zerado (Tailwind + Bootstrap + Swiper + AJAX)

✅ Tudo configurado, layout propositalmente zerado (placeholders).

## Instalar
- WordPress > Aparência > Temas > Adicionar novo > Enviar tema

## Tailwind (ativar de verdade)
Na pasta do tema:
- `npm i`
- `npm run dev` (dev)
- `npm run build` (produção)

Gera: `assets/css/tailwind.css` (o tema já enfileira automaticamente se existir)

## Bootstrap
Bootstrap CSS via CDN (somente CSS).

## Swiper
O JS já inicializa `.tbzHero` automaticamente (`assets/js/hero.js`).

## AJAX pronto
- Action: `tbz_load_more`
- JS: `assets/js/main.js`
- PHP: `functions.php`
- Exemplo em: `index.php`

## “Agente” (passo a passo)
Quando você mandar **“próximo passo”**, eu sigo:
1) Sitemap + seções
2) Hero (ACF: desktop/mobile) + markup
3) Serviços (ACF repeater) + cards
4) Cases/Portfólio (CPT opcional) + slider/grid
5) Blog + SEO + performance
6) AJAX onde precisar (load more, filtros, busca)

Me diga qual site vamos montar primeiro: **Diniz Visual** ou **Valenet**.


## Updates via GitHub (automático)
Este tema já vem com um updater embutido (sem plugins), apontando para o repositório:

- https://github.com/Diniz-visual/diniz-visual

### Para funcionar
1) Faça **Releases** no GitHub (ex.: `v1.0.1`, `v1.0.2`...)
2) Atualize a linha `Version:` do `style.css` para bater com a release
3) No WordPress, vá em **Painel > Atualizações**

### Dica (limite de API)
Se começar a dar limite do GitHub, adicione no `wp-config.php`:
`define('TBZ_GITHUB_TOKEN', 'SEU_TOKEN');`
