# Changelog

Format vychadza z [Keep a Changelog](https://keepachangelog.com/) a projekt dodrziava [Semantic Versioning](https://semver.org/).

## [1.1.0] - Unreleased

### Added
- Jednotna sablona posudku: typ prace (BP/DP) a rola (veduci/oponent) namiesto viacerych sablon.
- Automaticky vypocet vyslednej znamky (`GradeCalculator`) podla vzorcov z excelov, s unit testami.
- Stlpce `thesis_type`, `review_role`, `opponent_name`, `final_score` v tabulke `reviews`.
- Filtre podla typu prace a roly, nove stlpce v CSV exporte.
- Koncepty s automatickym ukladanim, kontrola duplicit, hromadny export.

### Changed
- Studijny program je povinny vyber podla typu prace; vsetky textove polia su povinne.
- Akademicky rok sa pocita z datumu posudku (od septembra).
- Meno veduceho/oponenta sa preberie z prihlaseneho pouzivatela.
- PDF prepracovane podla vzoroveho posudku (3 strany).
- DP veduci: kriteria bloku Kvalita riesenia podla vzoru (Pouzite metody + Aplikacia inzinierskych metod); popisky kriterii sa riadia typom prace a rolou (CriterionLabel).
- Pridane testy vypoctu znamky podla vzorcov z excelov (oponent aj veduci).

### Removed
- Rucny vyber znamky/odporucania a vyhlasenie autora z formulara.

### Notes
- Existujuce posudky sa migruju ako BP / veduci a zachovavaju povodnu rucne zadanu znamku.