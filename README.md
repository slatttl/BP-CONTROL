# BP Control

BP Control je webova aplikacia vytvorena v Laraveli. Sluzi na evidenciu a vyplnanie posudkov veduceho a oponenta bakalarskych a diplomovych prac.

V aplikacii je mozne vytvorit novy posudok, upravit ho, zobrazit jeho detail a ulozit ho vo formate PDF.

## Funkcie aplikacie

- prihlasenie administratora a pouzivatelov
- **jedna jednotna sablona** posudku: typ prace (BP / DP) a rola (veduci / oponent) sa vyberaju vo formulari
- studijny program ako povinny vyber podla typu prace
- vsetky textove polia su povinne
- akademicky rok sa dopocita automaticky z datumu posudku (rok zacina v septembri)
- meno veduceho / oponenta sa predvyplni z prihlaseneho pouzivatela
- **vysledna znamka sa pocita automaticky** podla vzorcov z oficialnych excelov
- PDF podla vzoru fakulty (3 strany)
- koncepty s automatickym ukladanim, kontrola duplicit, filtre, hromadny export (CSV, ZIP s PDF)
- sprava pouzivatelov administratorom
- ulozenie udajov v SQLite databaze

## Vypocet vyslednej znamky

Stupnica: A=1, B=2, C=3, D=4, E=5, FX=6. Priemer kriterii kazdeho bloku sa vazi:

| Blok | Veduci | Oponent |
|------|--------|---------|
| Aktivita a samostatnost | 10 % | - |
| Kvalita riesenia | 65 % | 65 % |
| Literatura | 15 % | 20 % |
| Formalna uroven | 10 % | 15 % |

Kriticke kriteria (uplnost a kvalita spracovania temy): ak je ktorekolvek FX, vysledok je FX; ak je ktorekolvek E, vysledok nie je lepsi ako E. Prace sa odporuca, ak vysledna znamka nie je FX. Konfiguracia je v `config/review.php`, vypocet v `app/Services/GradeCalculator.php`.
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

### Poziadavky

- PHP 8.3 alebo novsie s rozsireniami `pdo_sqlite`, `mbstring`, `xml`, `gd`, `zip`, `curl` (kvoli exportu PDF a ZIP)
- Composer

Ubuntu 24.04 ma PHP 8.3 v standardnych zdrojoch. Pri starsich vydaniach pridaj repozitar `ppa:ondrej/php`. V Docker kontajneri pouzi obraz s PHP 8.3+ (napr. `php:8.3-cli`) a doinstaluj uvedene rozsirenia.

Ak este nemas `.env` a databazu, vytvor ich pred `php artisan key:generate` (na Windowse `copy .env.example .env`):

```text
cp .env.example .env
touch database/database.sqlite
```

### Desktopova aplikacia (volitelne)

Okno aplikacie sa spusta cez NativePHP:

- Windows: dvojklik na `start-desktop.bat` (ak system nema PHP 8.3, pouzije sa PHP 8.3 zo zavislosti `nativephp/php-bin`)
- Linux: `php artisan native:install` (raz) a potom `php artisan native:run`

Desktopova verzia pouziva vlastnu databazu `database/nativephp.sqlite`.

## Aktualizacia existujucej instalacie

Po stiahnuti novej verzie spusti migracie, inak aplikacia hlasi chybu o chybajucom stlpci `thesis_type`:

```text
php artisan migrate
```

## Prihlasovacie udaje

Zakladne administratorske konto:

- email: `admin@bpcontrol.local`
- heslo: `adm1n123456##..`

Udaje je mozne zmenit v subore `.env` pomocou premennych `ADMIN_NAME`, `ADMIN_EMAIL` a `ADMIN_PASSWORD`.


## Pouzite technologie

- PHP
- Laravel
- Blade
- SQLite
- Tailwind CSS
- Dompdf
