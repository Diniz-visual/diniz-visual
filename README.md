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
