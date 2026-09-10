# BP Control

BP Control je webova aplikacia vytvorena v Laraveli. Sluzi na evidenciu a vyplnanie posudkov veduceho bakalarskej prace.

V aplikacii je mozne vytvorit novy posudok, upravit ho, zobrazit jeho detail a ulozit ho vo formate PDF.

## Funkcie aplikacie

- prihlasenie administratora a pouzivatelov
- vytvorenie posudku bakalarskej prace
- uprava a zobrazenie posudkov
- filtrovanie posudkov
- export posudku do PDF
- sprava pouzivatelov administratorom
- ulozenie udajov v SQLite databaze

## Spustenie projektu

V terminali otvor priecinok projektu a spusti:

```text
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Aplikacia bude dostupna na adrese:

```text
http://127.0.0.1:8000
```

Projekt pouziva serverove Laravel Blade sablony a na spustenie staci PHP server. JavaScriptovy server nie je potrebny.

## Prihlasovacie udaje

Zakladne administratorske konto:

- email: `admin@bpcontrol.local`
- heslo: `admin123456`

Udaje je mozne zmenit v subore `.env` pomocou premennych `ADMIN_NAME`, `ADMIN_EMAIL` a `ADMIN_PASSWORD`.

## Pouzite technologie

- PHP
- Laravel
- Blade
- SQLite
- Tailwind CSS
- Dompdf
