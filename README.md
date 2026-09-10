# BP Control

Laravel aplikacia na vyplnanie posudku veduceho bakalarskej prace, spravu evidencie a export do PDF.

## Co je hotove

- admin prihlasenie bez verejnej registracie
- formular pre posudok veduceho bakalarskej prace
- ulozenie do SQLite databazy
- detail posudku a editacia
- export viacstranoveho PDF

## Lokalne spustenie

1. `composer install`
2. `php artisan key:generate`
3. `php artisan migrate --seed`
4. `php artisan serve`

Frontend je nastavany bez JavaScriptu runtime a nacitava staticke CSS z `public/css/app.css`. Pri vyvoji staci jeden server (`php artisan serve`).

## Admin konto

- email: `admin@bpcontrol.local`
- heslo: `admin123456`

Tieto hodnoty mozes zmenit v `.env` cez `ADMIN_NAME`, `ADMIN_EMAIL` a `ADMIN_PASSWORD`, potom spusti `php artisan db:seed --class=AdminUserSeeder`.

## Dalsie upravy

Najblizsi logicky krok je doladit PDF sablonu tak, aby sa co najviac podobala presnemu skolskemu formularu, alebo pridat dalsie typy posudkov pre oponenta.
