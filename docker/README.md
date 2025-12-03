
Install By Docker
```bash
cd docker
cp .env.example .env
ip = http://172.16.0.93:8126
#nginx
docker compose -p container-simpeg -f docker-compose-nginx.yml up --build -d
#or
#frankenphp
docker compose -p container-simpeg up --build -d
docker compose -p container-simpeg exec app composer install 
docker compose -p container-simpeg exec app composer install --no-dev

#or
docker compose -p container-simpeg exec app composer install  --ignore-platform-reqs
docker compose -p container-simpeg exec app composer install --no-dev --ignore-platform-reqs
```
```bash
#bila mengagunkan frankenphp setelah melakukan composer install
# composer require laravel/octane   
docker compose -p container-simpeg exec app php artisan octane:install --server=frankenphp
# update .env di docker kosongkan jumlah thread akan otomatis di X2 dari jumlah cpu
#FRANKENPHP_CONFIG="worker /app/public/frankenphp-worker.php [jumlah thread]"
# setelah itu build kembali
docker compose -p container-simpeg up --build -d
docker compose -p container-simpeg ps
docker compose -p container-simpeg restart


cd ..
cp .env.example .env
```

[configurasi Env](#env)
```bash
cd docker
docker compose -p container-simpeg exec app php artisan key:generate
docker compose -p container-simpeg exec app chmod o+w -R storage
# or
docker compose -p container-simpeg exec app chmod 777 -R storage
docker compose -p container-simpeg exec app chmod -R 775 storage bootstrap/cache


docker compose -p container-simpeg exec app php artisan migrate
docker compose -p container-simpeg exec app php artisan migrate --database=data_simpeg --path=database/migrations/data_simpeg

docker compose -p container-simpeg exec app php artisan make:filament-user


#clear
docker compose -p container-simpeg exec app  php artisan cache:clear
docker compose -p container-simpeg exec app  php artisan config:clear
docker compose -p container-simpeg exec app  php artisan route:clear
docker compose -p container-simpeg exec app  php artisan event:clear 
docker compose -p container-simpeg exec app  php artisan view:clear
docker compose -p container-simpeg exec app composer dump-autoload

docker compose -p container-simpeg exec app php artisan optimize:clear
docker compose -p container-simpeg restart


#log
docker compose -p container-simpeg logs --tail 10 -f


```

