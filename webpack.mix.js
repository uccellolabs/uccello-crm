const mix = require('laravel-mix')

const autoload = {
   jquery: [ '$', 'jQuery', 'jquery']
}
mix.autoload(autoload)

mix.setPublicPath('public')

mix.sass('resources/sass/crm.scss', 'public/css')
   .js('resources/js/account/autoloader.js', 'public/js/account')
   .js('resources/js/widgets/calendar.js', 'public/js/widgets')
   .js('resources/js/widgets/tasks.js', 'public/js/widgets')
   .js('resources/js/opportunity/autoloader.js', 'public/js/opportunity')
   .js('resources/js/calendar/modal.js', 'public/js/calendar')
   .version()

// Copy all compiled files into main project (auto publishing)
mix.copyDirectory('public', '../../../public/vendor/uccello/crm');