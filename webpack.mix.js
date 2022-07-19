const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/dist/js')
    .sass('resources/sass/app.scss', 'public/dist/css')
    .sass("resources/sass/sub-content.scss", "public/dist/css")
    .sass('resources/sass/component.scss', 'public/dist/css')
    .sass('resources/sass/order.scss', 'public/dist/css')
   .js('resources/js/dashboard.js', 'public/dist/js/dashboard.js')
   .js('resources/js/helpers.js', 'public/dist/js/helpers.js')
   .js('resources/js/components.js', 'public/dist/js/components.js')
   .js('resources/js/navinode.js', 'public/dist/js/navinode.js')
   .js('resources/js/deliveryAudit.js', 'public/dist/js/deliveryAudit.js')
    .sourceMaps();
