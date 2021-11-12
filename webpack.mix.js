const mix = require('laravel-mix');

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

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
    .copyDirectory([
        'resources/images',
    ], 'public/images')
    .copy([
        'resources/favicons/*.*',
    ], 'public/favicons/')
    .combine([
        'node_modules/normalize.css/normalize.css',
        'node_modules/cookieconsent/build/cookieconsent.min.css'
    ], 'public/css/vendor.css')
    .sourceMaps()
    .setPublicPath('public')
    .options({
        imgLoaderOptions: {
            enabled: false, // only if server support cjpeg
        }
    })
   /* .webpackConfig({
        module: {
            rules: [
                {
                    test: /^resources\/assets\/images\/(de|en)\/.*\.(png|jpe?g|gif|webp|svg)$/,
                    loader: 'file-loader',
                    options: {
                        name: '[path][name].[ext]?[hash]',
                        context: 'resources/assets/images',
                    }
                }
            ]
        }
    })*/
    .version();
