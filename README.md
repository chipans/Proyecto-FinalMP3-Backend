# Proyecto-FinalMP3-Backend
# Backend (Laravel)

## Instala las dependencias de Composer:

cd Backend
composer install

## Si es la primera vez, copia el archivo de entorno:

copy .env.example ".env"

## Genera la clave de la aplicación:

php artisan key:generate

## Si usas base de datos, configura .env con tus credenciales y luego corre las migraciones:

php artisan migrate

## Finalmente, inicia el servidor:

php artisan serve