/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class', // This enables dark mode based on the presence of the "dark" class in the HTML tag
  safelist: [
    'opacity-0',
    'group-hover:opacity-100',
    'transition-all',
    'ease-out',
    'hidden',
    'aspect-4/5',
    'aspect-5/4',
    'aspect-2/3',
    'aspect-3/2',
    'aspect-square', // 1x1
    'aspect-video'   // 16x9
  ],
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.ts",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      backgroundImage: {
        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
        'noise': "url('../img/noise.png')"
      },
      dropShadow: {
        '3xl': '0 20px 20px rgba(0, 0, 0, 1)',
      },
      fontSize: {
        '3xs': ['0.55rem', '0.7rem'],
        '2xs': ['0.65rem', '0.8rem'],
      },
      colors: {
        primary: 'var(--primary)',
        // 'primary-inverse': 'rgb(var(--primary-inverse))',
        // 'primary-hover': 'rgb(var(--primary-hover))',
        // 'primary-active-color': 'rgb(var(--primary-active-color))',

        // 'primary-highlight': 'rgb(var(--primary)/var(--primary-highlight-opacity))',
        // 'primary-highlight-inverse': 'rgb(var(--primary-highlight-inverse))',
        // 'primary-highlight-hover': 'rgb(var(--primary)/var(--primary-highlight-hover-opacity))',

        // primary: {
        //   50: 'var(--primary-50)',
        //   100: 'var(--primary-100)',
        //   200: 'var(--primary-200)',
        //   300: 'var(--primary-300)',
        //   400: 'var(--primary-400)',
        //   500: 'var(--primary-500)',
        //   600: 'var(--primary-600)',
        //   700: 'var(--primary-700)',
        //   800: 'var(--primary-800)',
        //   900: 'var(--primary-900)',
        //   950: 'var(--primary-950)',
        // },
        surface: 'var(--surface)',
        // ,{
        //   0: 'var(--surface-0)',
        //   50: 'var(--surface-50)',
        //   100: 'var(--surface-100)',
        //   200: 'var(--surface-200)',
        //   300: 'var(--surface-300)',
        //   400: 'var(--surface-400)',
        //   500: 'var(--surface-500)',
        //   600: 'var(--surface-600)',
        //   700: 'var(--surface-700)',
        //   800: 'var(--surface-800)',
        //   900: 'var(--surface-900)',
        //   950: 'var(--surface-950)',
        // },
        // 'text-main': {
        //   0: 'var(--text-main-0)',
        //   100: 'var(--text-main-100)',
        //   200: 'var(--text-main-200)',
        //   300: 'var(--text-main-300)',
        //   400: 'var(--text-main-400)',
        //   800: 'var(--text-main-800)',
        // },
        // 'text-hover': 'var(--text-hover)',
        // bg: {
        //   50: 'var(--bg-50)',
        //   100: 'var(--bg-100)',
        //   200: 'var(--bg-200)',
        //   300: 'var(--bg-300)',
        //   400: 'var(--bg-400)',
        //   500: 'var(--bg-500)',
        //   600: 'var(--bg-600)',
        //   700: 'var(--bg-700)',
        //   800: 'var(--bg-800)',
        //   900: 'var(--bg-900)',
        //   950: 'var(--bg-950)',
        // },
        ready: {
          400: 'var(--ready)'
        },
        danger: {
          600: 'var(--danger)',
          700: 'var(--danger-dark)',
          800: 'var(--danger-darker)',
        },
        warning: {
          600: 'var(--warning)',
          700: 'var(--warning-dark)',
        },
        create: {
          600: 'var(--create)',
          700: 'var(--create-dark)',
        }
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
          '0%': { 'transform': 'translateY(80px)' },
          '100%': { 'transform': 'translateY(0)' }
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
      },
      aspectRatio: {
        '4/5': '4 / 5',
        '5/4': '5 / 4',
        '2/3': '2 / 3',
        '3/2': '3 / 2',
      },
      typography: {
        DEFAULT: {
          css: {
            a: {
              'text-underline-offset': '0.2rem',
              'text-decoration': 'underline dashed',
            },
          },
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
    require('tailwindcss-primeui'),
  ],
}
