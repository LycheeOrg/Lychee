/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        dark: {
          // 50: '#fdf8f6',
          // 100: '#f2e8e5',
          // 200: '#eaddd7',
          // 300: '#e0cec7',
          // 400: '#d2bab0',
          // 500: '#bfa094',
          // 600: '#a18072',
          200: '#c0c0c0',
          400: '#303030',
          500: '#252525',
          600: '#202020',
          700: '#1d1d1d',
          800: '#1a1a1a',
          850: '#101010',
          900: '#0f0f0f',
        },
      },
      flexShrink: {
        2: '2'
      }
    },
  },
  plugins: [],
}