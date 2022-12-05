# P6_snowtrick
#symfony insight 
https://insight.symfony.com/projects/bf62984f-07de-4ab4-a0b2-4144eb0dc410/analyses/69
## Prerequisites

* PHP 8.1 
* Symfony CLI

## Installation and configuration
composer install
Duplicate and rename the `.env` file to `.env.local` and modify the necessary information (`APP_ENV`, `APP_SECRET`, ...)
DATABASE_URL="mysql://root:@127.0.0.1:3306/snowtrick?serverVersion=mariadb-10.4.25"
symfony console doctrine:database:create
symfony console doctrine:schema:update --force
symfony serve

## Launch the local server

Run the command `symfony server`.

```Terminal
composer install
symfony console doctrine:database:create
symfony console doctrine:schema:update --force

