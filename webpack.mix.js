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

mix.js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css')
    .copy([
        'resources/assets/images/**/*.jpg',
        'resources/assets/images/**/*.png',
        'resources/assets/images/**/*.svg',
    ], 'public/images/')
    .copy([
        'resources/assets/favicons/*.*',
    ], 'public/favicons/')
    .combine([
        'node_modules/normalize.css/normalize.css',
    ], 'public/css/vendor.css')
    .sourceMaps()
    .setPublicPath('public')
    .options({
        imgLoaderOptions: {
            enabled: false, // only if server support cjpeg
        }
    })
    .version();
