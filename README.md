# Welcome to MindBridger Open Source AI Library System (Main Repo of Laravel)

## Environment
  - Apache Web Server 2.4.* +
  - MariaDB(MySQL) 10.4.* +
  - PHP 8.0.2 +
  - Laravel 9.52.18

##  First thing to do is installing web/admin program:
### https://github.com/wisesam/mindbridger.git

## Now you can install MindBridger Main Laravel program
1. Main Larabel repo (backend + frontend)
   - https://github.com/wisesam/mindbridger-main.git
   - Adujust directory permission if needed (Linux and MacOS):
     - eg, chown -R apache mindbridger-main, chgrp -R apache mindbridger-main
3. cp .env.example .env  (update needed)
4. cp config/app(default).php config/app.php (update needed)
5. cp config/database (default).php config/database .php (update needed)
6. composer install
7. php artisan key:generate (update .env)
8. DB Migration (Library related table installation)
   - php artisan migrate
   - php artisan db:seed --class=CodeSeeder
   - (If migration gives error like " No such file or directory", then update DB_SOCKET in .env)

9. Create a symbolic link (for cover images)
   - Go to root of mindbridger (eg, c:\xampp\htdocs\mindbridger, /var/www/html/mindbridger)
   - (Windows CMD example)  mklink /D storage "c:\xampp\mindbridger-main\storage\app\public"
   - (Window PowerShell example) New-Item -ItemType SymbolicLink -Path "storage" -Target "C:\xampp\mindbridger-main\storage\app\public"
   - (Linux, MacOS example) ln -s /var/www/mindbridger-main/storage/app/public storage
10. Click Update button from http://localhost/mindbridger/vwmldbm
       
## Demo
<a href="https://wise4edu.net/mindbridger" target="_blank">MindBridger Demo</a>

## Server Side Rendering and Minimun Libraries
- to make it lightweighted to even run fron old smartphones, no webpack, vite were used.

## About Laravel
Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

