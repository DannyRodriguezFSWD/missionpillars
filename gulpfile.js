var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

// vendors path : bower_components

elixir(function(mix) {
    
    /********************/
    /* Copy Stylesheets */
    /********************/

    // Bootstrap
    mix.copy('bower_components/bootstrap/dist/css/bootstrap.min.css', 'public/css/bootstrap.min.css');

    // Font awesome
    mix.copy('bower_components/fontawesome/css/font-awesome.min.css', 'public/css/font-awesome.min.css');

    // Gentelella
    mix.copy('bower_components/simple-line-icons/css/simple-line-icons.css', 'public/css/simple-line-icons.css');

    /****************/
    /* Copy Scripts */
    /****************/

    // jQuery
    mix.copy('bower_components/jquery/dist/jquery.min.js', 'public/js/jquery.min.js');
    
    //Tether
    mix.copy('bower_components/tether/dist/js/tether.min.js', 'public/js/tether.min.js');
    
    // Bootstrap
    mix.copy('bower_components/bootstrap/dist/js/bootstrap.min.js', 'public/js/bootstrap.min.js');

    // Pace
    mix.copy('bower_components/pace/pace.min.js', 'public/js/pace.min.js');
    mix.copy('bower_components/pace/themes/', 'public/js/themes');
    
    //Chart
    mix.copy('bower_components/chart.js/dist/Chart.min.js', 'public/js/Chart.min.js');

    /**************/
    /* Copy Fonts */
    /**************/

    // Bootstrap
    mix.copy('bower_components/fontawesome/fonts/', 'public/fonts');

    // Font awesome
    mix.copy('bower_components/simple-line-icons/fonts/', 'public/fonts');
});
