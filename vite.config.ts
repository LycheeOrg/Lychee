import { fileURLToPath, URL } from 'node:url';
import { defineConfig, loadEnv, UserConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import i18n from 'laravel-vue-i18n/vite';
// import path from "path";

const laravelPlugin = laravel({
    input: [
      'resources/sass/app.scss',
      'resources/js/app.ts',
    ],
    refresh: true,
  })

const baseConfig =   {
  base: './',
  plugins: [
    vue(
    {
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    i18n(),
  ],
  server: {
    watch: {
      ignored: [
        "**/.*/**",
        "**/app/**",
        "**/database/**",
        "**/node_modules/**",
        "**/public/**",
        "**/storage/**",
        "**/tests/**",
        "**/vendor/**",
        "**/presets/**",
      ],
    },
  },
  resolve: {
    alias: {
      // @ts-ignore-next-line
      '@': fileURLToPath(new URL('./resources/js/', import.meta.url)),
      vue: 'vue/dist/vue.esm-bundler.js',
    },
  },
  build: {
    rollupOptions: {
        output:{
            manualChunks(id) {
                if (id.includes('node_modules')) {
                    return id.toString().split('node_modules/')[1].split('/')[0].toString();
                }
            }
        }
    }
  },
  css: {
    preprocessorOptions: {
      scss: {
        api: 'modern'
      },
    }
  }
} as UserConfig;


/** @type {import('vite').UserConfig} */
export default defineConfig(
  ({ command, mode, isSsrBuild, isPreview }) => {
    const config = baseConfig;
    if (command === 'serve') {
      const env = loadEnv(mode, process.cwd(), '')

      console.log("LOCAL VITE MODE detected")
      console.log("api calls will be forwarded to:")
      console.log(env.VITE_HTTP_PROXY_TARGET);
      if (env.VITE_LOCAL_DEV === 'true') {
        if (config.server === undefined) {
          throw new Error('server config is missing');
        }
        config.server.open = 'vite/index.html';
        config.server.proxy =  {
          '/api/': env.VITE_HTTP_PROXY_TARGET,
        }
        return config;
      }

      return {
        // dev specific config
      }
      
    } else { // command === 'build'
      if (config.plugins === undefined) {
        throw new Error('plugins list is missing');
      }

      config.plugins.push(laravelPlugin);
      return config;
    }
  });
  
  
  
//   {
//   base: './',
//   plugins: [
//     // laravel({
//     //   input: [
//     //     'resources/sass/app.scss',
//     //     'resources/js/app.ts',
//     //   ],
//     //   refresh: true,
//     // }),
//     vue(
//     {
//       template: {
//         transformAssetUrls: {
//           base: null,
//           includeAbsolute: false,
//         },
//       },
//     }),
//     i18n(),
//   ],
//   server: {
//     open: 'vite/index.html',
//     proxy: {
//       '/api/': 'https://photography.viguier.nl/',
//       '/vite/uploads/': 'https://photography.viguier.nl/',
//     },
//     watch: {
//       ignored: [
//         "**/.*/**",
//         "**/app/**",
//         "**/database/**",
//         "**/node_modules/**",
//         "**/public/**",
//         "**/storage/**",
//         "**/tests/**",
//         "**/vendor/**",
//         "**/presets/**",
//       ],
//     },
//   },
//   resolve: {
//     alias: {
//       // @ts-ignore-next-line
//       '@': fileURLToPath(new URL('./resources/js/', import.meta.url)),
//       vue: 'vue/dist/vue.esm-bundler.js',
//     },
//   },
//   build: {
//     rollupOptions: {
//         output:{
//             manualChunks(id) {
//                 if (id.includes('node_modules')) {
//                     return id.toString().split('node_modules/')[1].split('/')[0].toString();
//                 }
//             }
//         }
//     }
//   },
//   css: {
//     preprocessorOptions: {
//       scss: {
//         api: 'modern'
//       },
//     }
//   }
// });
