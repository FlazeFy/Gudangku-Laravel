# Gudangku Documentation
https://gudangku.leonardhors.site

========================= Command =========================
# First Run
> composer install
> composer update
> php artisan key:generate
> php artisan storage:link
> php artisan serve

# Run Application
> php artisan serve

# Run Queue Job
> php artisan queue:work

# Run Application On Custom Pors
> php artisan serve # port=****
ex : php artisan serve # port=9000

# Run Migrations
> php artisan migrate

# Run Migrations On Specific file
> php artisan migrate --path=database/migrations/migration/file.php

# Run Seeder
> php artisan db:seed class=DatabaseSeeder 
or
> php artisan db:seed

# Run Scheduler
> php artisan schedule:run

# Make Controller
> php artisan make:controller <NAMA-Controller>Controller --resource

# Make Model
> php artisan make:model <NAMA-Model>

# Make Seeder
> php artisan make:seeder <NAMA-TABEL>Seeder

# Make Factories
> php artisan make:factory <NAMA-TABEL>Factory

# Make Migrations
> php artisan make:migration create_<NAMA-TABEL>_table

# Make Migrations on Specific File
> php artisan migrate # path=/database/migrations/<NAMA-FILE>.php

# Make Middleware
> php artisan make:middleware <NAMA-MIDDLEWARE>

# Make Mail
> php artisan make:mail <NAMA-MAILER>Email

# Make Deploy
> php artisan route:cache
> php artisan cache:clear
> php artisan route:clear