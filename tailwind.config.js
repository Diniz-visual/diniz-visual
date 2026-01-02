/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.php",
    "./assets/js/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        primary: "#134888",
        accent: "#00beff"
      }
    },
  },
  plugins: [],
};
