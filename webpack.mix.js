const mix = require('laravel-mix');
const path = require('path');

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
    .vue()
    .sass('resources/sass/app.scss', 'public/css')
    .version()
    .disableNotifications();

// mix.browserSync({
//     proxy: 'http://127.0.0.1:8000',
//     open: false,
//     notify: false,
// });

mix.webpackConfig({
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js')
        },
    },
});
