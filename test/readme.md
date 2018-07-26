Step to run sample project

require environment :
php : 7.1
laravel : 5.6
db config : please follow env.example file

step to run :
-composer update
-create database `homestead`
-php artistan migrate // create users table 
-php artisan serve // to quick check with php server

post man link url :
-/api/auth/login 
-/api/user
-/api/auth/update
-/api/register