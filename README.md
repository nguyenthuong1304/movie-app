<p align="center">
    <img src="https://dwglogo.com/wp-content/uploads/2017/09/Vue-logo-002.svg" width="400">
</p>

# About Movies
### Là một web xem phim chùa, được tạo ra do mình quá rảnh :)) 
## require
 - PHP >= 7
 - Laravel >= 5.8
 ## Setup
 - git clone
 - run : composer íntall
 - Genarate key: php artisan key:genarate
 - add config file : cp .env.example .env
 - run migrate : php artisan migrate
 - run seed : php artisan db:seed
 - install npm : npm install
 ## Make account admin
 - php artisan tinker
 - User::create(['name' => 'Admin', 'email' => 'Admin@movie-app.com', 'role' => 0, 'password' => Hash::make(123456)]);
 ## Run project
 - npm run dev
 - php artisan serve
 ## Build docker
 - require docker and docker-compose
 - docker run --rm -v $(pwd):/app composer install
 - sudo chown -R $USER:$USER ~/movie-app
 - docker-compose build
 - docker-compose up -d
 - cp .env.dev .env
   + DB_CONNECTION=mysql
   + DB_HOST=mariadb
   + DB_PORT=3306
   + DB_DATABASE=movies
   + DB_USERNAME=root
DB_PASSWORD=
 - docker-compose exec app vim .env
 - docker-compose exec app php artisan key:generate
 - docker-compose exec app php artisan config:cache
 - docker-compose exec app php artisan migrate
