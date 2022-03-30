<p align="center">
    <a href="https://lumen.laravel.com/" target="_blank">
        <img src="https://raw.githubusercontent.com/adiyansahcode/adiyansahcode/main/assets/lumen-icon.svg" height="100">
    </a>
</p>

# Lumen API AUTH
Rest API project use [Lumen](https://lumen.laravel.com/) with token base [JWT](https://jwt.io/)

## Installation
* Clone this project
* Create .env file `cp .env.example .env`
* Run composer `composer install`
* Generate App key `php artisan key:generate`
* Generate Jwt key `php artisan jwt:secret`
* Optimize `composer run-script optimize`
* Run migration and seeder `php artisan migrate:fresh --seed`
* Run server `php -S localhost:8002 -t public`
* Default user
```
username : user
password: password
```
* Import postman json to your postman `postman.postman_collection.json` & `postman_env.postman_environment`
* done, just try run your project in postman

## Included Packages

- [Dingo API](https://github.com/api-ecosystem-for-laravel/dingo-api)
- [Fractal](https://github.com/thephpleague/fractal)
- [Guzzle Http](https://github.com/guzzle/guzzle)
- [Lumen Generator](https://github.com/flipboxstudio/lumen-generator)
- [PHP-Open-Source-Saver jwt-auth](https://github.com/PHP-Open-Source-Saver/jwt-auth)
