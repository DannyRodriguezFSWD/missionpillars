# Mission Pillars
## Table of Contents
  - [Installing](#installing)
    - [Vagrant Box](#vagrant-box)
    - [Dependencies](#dependencies)
    - [Browser Support](#browser-support)
    - [Artisan Composer and Node](#artisan-composer-and-note)
    - [Database](#database)
    - [Subdomains](#subdomains)
  - [Laravel](#laravel])
    
## Installing

### Vagrant Box

We have setup a git repository in Bitbucket with some of the configuration setup. 

```
https://bitbucket.org/ContinueToGive/mp_lamp_ubuntu16.04.git
```
Use the mp_lamp_ubuntu16.04 repository's README file to get the vagrant box running and dependencies loaded.
After you run the following command
```
vagrant up
```
... you can clone this repository in the root of that repository. By convention, he resulting path for this repository will be
```
PATHTOVAGRANTFOLDER\mp_lamp_ubuntu16.04\missionpillarscrm
```
While or after cloning, switch to your branch e.g., YOUR_NAME/workingbranch

### Dependencies
- Laravel Framework: 5.4.36
- PHP: 7.1 (7.0 is what is currently on the Production, Demo, & QA instances)
- NodeJS: 8.15.1, NPM 6.4.1
- CoreUI 3.4.0 https://www.npmjs.com/package/@coreui/coreui-pro/v/3.4.0
- jQuery 3.5.1
- Vue.js 2.1.10

### Browser Support

Our browser support is driven largely by the above dependencies.

_from https://coreui.io/docs/getting-started/browsers-devices/_

    Chrome >= 45
    Firefox >= 38
    Edge >= 12
    Explorer 11
    iOS >= 9
    Safari >= 9
    Android >= 4.4
    Opera >= 30

### Artisan Composer and Node

From within your vagrant box, do the following

```bash 
cd ~/                                   # if not already in home folder
ln -s /vagrant/missionpillarscrm        # make symlink for ease

# cd to the root of your local missionpillars repository
cd ~/missionpillars

# Node / Vue.js compilations

composer install
npm install
npm run dev
```

Note: if you have some issues using this command, then run "git reset --hard" then try again
Note: Any actions, like this one ^, that changes .vue files requires you to also run "npm run dev" once again
Note: to help with this on your local system (not your vagrant box), if you install node, you can run the following command and it will automatically recompile *.vue files as they are modified. It may be a pain to setup on Windows but is super-worth it
```
npm run watch
```


### Database 

#### Create Database
```
mysql -u root -p
# enter password: root

# inside mysql
CREATE DATABASE missionpillars_local;
show databases;
quit;
```

#### Edit .env file 
be sure db connection looks like:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=missionpillars_local
DB_USERNAME=root
DB_PASSWORD=root
```

NOTE: if you changes the maria db ip address and/or port, change it for yours

After updating your .env file, you will want to create the specified database then run the necessary migrations. From within your vagrant box

```
php artisan migrate 
php artisan passport:install
```
This will run several migrations and STOP prior to the need to run seeders. 
Run the seeders and then the migrate  command again

```bash 
php artisan db:seed 
php artisan migrate
```

#### Public images storage

Mission Pillars uses the **storage/app/public/** folder to store files updloaded, in particular, by the Forms and Events user interface. 

To ensure that these are visible for the user, run the following command which creates a public viewable link to the folder

```
php artisan storage:link
```

See https://laravel.com/docs/8.x/filesystem#the-public-disk for more info

### Subdomains

In Mission Pillars, each organization gets there own subdomain, which allows increased separation for sessions. This allows you to have multiple active organizations in one browser. This requires you to modify your local hosts file for each subdomain you want to use. Locally, each subdomain will be applied to **local.missionpillars.com** to differentiate it from production, QA and Demo addresses.

You will want to have one 'app' subdomain. This will allow sign up of a new account and logging in to any organization. You will also need to make a subdomain for each organization you create **prior to** signing up for the new account. For example, to set these to access your local webserver on your computer, add the following to your hosts file:

```
127.0.0.1 app.test.missionpillars.com

127.0.0.1 test.test.missionpillars.com
127.0.0.1 fake1.test.missionpillars.com
127.0.0.1 fake2.test.missionpillars.com
127.0.0.1 your-name.test.missionpillars.com
...
```

in your web browser try http://app.test.missionpillars.com
click on create account
... when signing up, specifying test, fake1,... as the subdomain should work fine. However, there will be an error if another subdomain is used.

 When activating the modules use 4242424242... as test credit card number. If there are any issues please contact Immanuel for any assistance with environment file settings.

# Laravel
[![Laravel Logo](https://laravel.com/assets/img/components/logo-laravel.svg)]

[![Build Status](https://travis-ci.org/laravel/framework.svg)](https://travis-ci.org/laravel/framework)
[![Total Downloads](https://poser.pugx.org/laravel/framework/d/total.svg)](https://packagist.org/packages/laravel/framework)
[![Latest Stable Version](https://poser.pugx.org/laravel/framework/v/stable.svg)](https://packagist.org/packages/laravel/framework)
[![License](https://poser.pugx.org/laravel/framework/license.svg)](https://packagist.org/packages/laravel/framework)

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, yet powerful, providing tools needed for large, robust applications. A superb combination of simplicity, elegance, and innovation give you tools you need to build any application with which you are tasked.

## Learning Laravel

Laravel has the most extensive and thorough documentation and video tutorial library of any modern web application framework. The [Laravel documentation](https://laravel.com/docs) is thorough, complete, and makes it a breeze to get started learning the framework.

If you're not in the mood to read, [Laracasts](https://laracasts.com) contains over 900 video tutorials on a range of topics including Laravel, modern PHP, unit testing, JavaScript, and more. Boost the skill level of yourself and your entire team by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for helping fund on-going Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](http://patreon.com/taylorotwell):

- **[Vehikl](http://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[British Software Development](https://www.britishsoftware.co)**
- **[Styde](https://styde.net)**
- [Fragrantica](https://www.fragrantica.com)
- [SOFTonSOFA](https://softonsofa.com/)

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](http://laravel.com/docs/contributions).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell at taylor@laravel.com. All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
