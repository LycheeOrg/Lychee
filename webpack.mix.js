let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */
// mix.options({
//     legacyNodePolyfills: true
// });

mix.js('resources/assets/js/app.js', 'public/js')
   .copy('resources/js/vendor/alpine.min.js', 'public/js')
   .copy('resources/js/vendor/filepond.js', 'public/js')
   .js('resources/js/vendor/webauthn/webauthn.js', 'public/js')
   .css('resources/css/filepond.css', 'public/css')
   .sass('resources/assets/scss/app.scss', 'public/css')
   .options({ processCssUrls: false });
