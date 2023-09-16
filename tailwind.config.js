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
      backgroundImage: {
        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
      },
      dropShadow: {
        '3xl': '0 20px 20px rgba(0, 0, 0, 1)',
      },
      fontSize: {
        '3xs': ['0.55rem', '0.7rem'],
        '2xs' : ['0.65rem', '0.8rem'],
      },
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
          300: '#404040',
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
      },
      transitionProperty: {
        width: ['width'],
      },
      keyframes: {
        fadeIn: {
          '0%': { 'opacity': '0' },
          '100%': { 'opacity': '1' }
        },
        fadeOut: {
          '0%': { 'opacity': '1' },
          '100%': { 'opacity': '0' }
        },
        moveBackground: {
          '0%': { 'background-position-x': '0px' },
          '100%': { 'background-position-x': '-100px' }
        },
        moveUp: {
          '0%': {'transform': 'translateY(80px)'},
          '100%': {'transform': 'translateY(0)'}
        },
        zoomIn: {
          '0%': {
            'opacity': '0',
            'transform': 'scale(0.8)'
          },
          '100%': {
            'opacity': '1',
            'transform': 'scale(1)'
          }
        },
        zoomOut: {
          '0%': {
            'opacity': '1',
            'transform': 'scale(1)'
          },
          '100%': {
            'opacity': '0',
            'transform': 'scale(0.8)'
          }
        },
        popIn: {
          '0%': {
            'opacity': '0',
            'transform': 'scale(1.1)'
          },
          '100%': {
            'opacity': '1',
            'transform': 'scale(1)'
          }
        },
        scaleIn: {
          '0%': {
            'transform': 'scale(0)'
          },
          '100%': {
            'transform': 'scale(1)'
          }
        },
        scaleOut: {
          '0%': {
            'transform': 'scale(1)'
          },
          '100%': {
            'transform': 'scale(0)'
          }
        },
        animateDown: {
          '0%': {
            'opacity': '0',
            'transform': 'translateY(-300px)'
          },
          '100%': {
            'opacity': '1',
            'transform': 'translateY(0px)'
          }
        },
        animateUp: {
          '0%': {
            'opacity': '0',
            'transform': 'translateY(300px)'
          },
          '100%': {
            'opacity': '1',
            'transform': 'translateY(0px)'
          }
        }


      },
      animation: {
        'fadeIn': 'fadeIn 0.3s forwards cubic-bezier(0.51, 0.92, 0.24, 1)',
        'fadeOut': 'fadeOut 0.3s forwards cubic-bezier(0.51, 0.92, 0.24, 1)',
        'zoomIn': 'zoomIn 0.2s forwards cubic-bezier(0.51, 0.92, 0.24, 1)',
        'zoomOut': 'zoomOut 0.2s forwards cubic-bezier(0.51, 0.92, 0.24, 1)',
        'moveUp': 'moveUp 0.3s forwards cubic-bezier(0.51, 0.92, 0.24, 1)',
        'scaleIn': 'scaleIn 0.3s forwards cubic-bezier(0.51,0.92,0.24,1.2)',
        'scaleOut': 'scaleOut 0.3s forwards cubic-bezier(0.51,0.92,0.24,1.2)',

        'slowFadeIn': 'fadeIn 2s forwards cubic-bezier(0.51, 0.92, 0.24, 1)',
        'slowFadeOut': 'fadeOut 2s forwards cubic-bezier(0.51, 0.92, 0.24, 1)',
        'slowZoomIn': 'zoomIn 2s forwards cubic-bezier(0.51, 0.92, 0.24, 1)',
        'slowZoomOut': 'zoomOut 2s forwards cubic-bezier(0.51, 0.92, 0.24, 1)',

        'slowMoveUp': 'moveUp 2s forwards cubic-bezier(0.51, 0.92, 0.24, 1)',
        'slowPopIn': 'popIn 2s forwards cubic-bezier(0.51, 0.92, 0.24, 1)',

        'landingIntroPopIn': 'popIn 2s forwards ease-in-out',
        'landingIntroFadeOut': 'fadeOut 2s 2s forwards ease-in-out', // delayed by 2s
        'landingSlidesPopIn': 'popIn 2s 3s forwards ease-in-out', // delayed by 2s
        'ladningAnimateDown': 'animateDown 1s 3.1s forwards ease-in-out', 
        'ladningAnimateUp': 'animateUp 1s 3.1s forwards ease-in-out',
        'delayedFadeOut': 'fadeOut 2s 2s forwards ease-in-out'
      }
    },
  },
  plugins: [],
}