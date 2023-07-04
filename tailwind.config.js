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
          600: '#222',
          700: '#1d1d1d',
          800: '#1a1a1a',
          850: '#101010',
          900: '#0f0f0f',
        },
        teal: {
          100: '#a6d3f7',
          200: '#7abef3',
          300: '#4ea8ef',
          400: '#2293ec', // default
          500: '#1e84d4',
          600: '#1b75bc',
          700: '#1766a5',
          800: '#14588d',
          900: '#0d3a5e',
        },
        darker: {
          900: '#000000d9',
        }
      },
      flexShrink: {
        2: '2'
      }
    },
  },
  plugins: [],
}