# Sample Laravel Points Project
## Run Locally

Clone the project

```
  git clone https://github.com/STGehring/laravel_points_Test.git
```

Go to the project directory

```
  cd laravel_points_Test
```

Bring container up

```
  docker-compose up --build
```

In another terminal, shell into laravel:
```
  docker exec -it laravel_test_app_1 /bin/bash
```

Migrate the database:
```
  php artisan migrate
```

Seed the database:
```
  php artisan db:seed 
```

Open browser to [localhost:8000](http://localhost:8000/)
