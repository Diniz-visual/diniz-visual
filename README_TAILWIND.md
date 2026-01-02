# Tailwind (opcional) — como ativar de verdade

Este tema já funciona **sem Tailwind** (CSS base em `style.css` + `assets/css/theme.css`).

Se você quiser Tailwind compilado (recomendado para produção):
1) Crie um `package.json` na raiz do tema
2) Instale: `npm i -D tailwindcss postcss autoprefixer`
3) Rode: `npx tailwindcss init -p`
4) Crie `assets/css/input.css` com:
   @tailwind base;
   @tailwind components;
   @tailwind utilities;

5) Configure `tailwind.config.js` para varrer os arquivos PHP:
   content: ["./**/*.php"]

6) Build:
   npx tailwindcss -i ./assets/css/input.css -o ./assets/css/tailwind.css --minify

7) No `functions.php`, troque `assets/css/theme.css` por `assets/css/tailwind.css` (ou enfileire os dois).
