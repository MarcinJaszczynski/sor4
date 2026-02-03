<!-- Zwięzłe wskazówki dla AI-coding-agentów pracujących nad projektem BP RAFA -->
# Copilot instructions — BP RAFA (krótko)

Zadanie agenta: pomagać szybko i bezpiecznie wprowadzć zmiany w aplikacji Laravel znajdującej się w tym repozytorium.

- **Architektura (big picture):** projekt to typowa aplikacja Laravel (app/, routes/, resources/, public/) z panelem administracyjnym zbudowanym w Filament (app/Filament) oraz częścią klienta/pilota (Strefa Klienta). Główne warstwy: Models (app/Models), Services (app/Services), Traits (app/Traits), Filament resources (app/Filament), Livewire/Front-end korzysta z Vite + Tailwind (vite.config.js, tailwind.config.js).

- **Główne konwencje projektu:**
  - Kod domenowy umieszczony w `app/` z klarownymi podfolderami (`Services`, `Traits`, `Observers`, `Notifications`).
  - Ad-hoc scripts i szybkie poprawki znajdują się w katalogu głównym i `scripts/` (np. `quick_fix_*`, `scripts/rewrite_prices.php`) — traktować je jako narzędzia diagnostyczne, nie produkcyjny API.
  - Używane pakiety: Filament, Spatie (permission, sortable), webklex (IMAP), intervention/image, filamnet-kanban. Nie modyfikować vendor/*.

- **Build / dev / test — najważniejsze komendy:**
  - Instalacja zależności PHP: `composer install` (composer.json zawiera post-create/post-update hooks)
  - Frontend: `npm install` i `npm run dev` / `npm run build` (vite)
  - Pełny tryb developerski (używany w composer scripts): `composer run dev` uruchamia serwer, kolejkę i vite równolegle
  - Migracje: `php artisan migrate`
  - Testy: `vendor/bin/pest` lub `php artisan test` (konfiguracja znajduje się w `phpunit.xml`)
  - Synchronizacja e-maili: `php artisan emails:sync` (również scheduler w `app/Console/Kernel.php`)
  - Narzędzia cenowe: `php artisan pricing:backfill` i `php artisan eventtemplate:compare-calc` (zajrzyj do docs/USER_GUIDE.md dla kontekstu)

- **Wzorce kodu / przykłady:**
  - Notyfikacje: `app/Notifications/*` (np. `TaskCommentNotification.php`) — stosuj `toMail`, `toArray` i kolejkuj, jeśli istnieje `ShouldQueue`.
  - Filament Resources: patrz `app/Filament` (Resource + Pages + RelationManagers). Używaj istniejących Resource patterns przy dodawaniu nowych CRUD-ów.
  - Kalkulacje cen: główne klasy i skrypty opisane w `docs/USER_GUIDE.md` — nowy unified price engine jest preferowany.

- **Integracje i punkty uwagi:**
  - IMAP/SMTP: konfiguracja trzyma się w `EmailAccount` (Filament resource). Synchronizacja korzysta z IMAP i zapisuje załączniki w `public`.
  - Kolejki: wiele zadań może być kolejkowanych; w dev często używamy `queue:listen` lub `QUEUE_CONNECTION=sync` w testach (phpunit.xml ustawia `QUEUE_CONNECTION=sync`).
  - Obrazki/kompresja: sprawdź `IMAGE-COMPRESSION.md` i `quick_fix_gallery_images.php` przed wprowadzaniem zmian w procesie uploadu.

- **Zachowaj ostrożność:**
  - Nie edytuj vendor/*; pakiety Filament i Spatie mają swoje zasady.
  - Wiele narzędzi w repo root to szybkie poprawki — jeśli zmieniasz ich zachowanie, dodaj testy lub dokumentację.

- **Gdzie szukać kontekstu:**
  - Struktura aplikacji: `app/` — logika domenowa
  - Panel admina: `app/Filament`
  - Skrypty i debug: pliki `quick_fix_*`, `debug_*` w repo root
  - Dokumentacja funkcjonalna: [docs/USER_GUIDE.md](docs/USER_GUIDE.md)
  - Konfiguracje budowania: [composer.json](composer.json), [package.json](package.json), [phpunit.xml](phpunit.xml)

Zadanie po zmianie kodu: uruchom lokalnie migracje/testy i zbuduj assets (jeśli dotyczy). Po wygenerowaniu patcha zapytaj, czy mam uruchomić testy lokalnie.

Jeśli jakaś część projektu jest niejasna (np. konkretna klasa kalkulacji cen, concurrency w kolejkach, lub reguły przypisywania maili), poproś o wskazanie pliku/ID przykładu, podam dokładne kroki i listę plików do sprawdzenia.

---
Proszę o informację: które sekcje rozwijać (architektura, workflow CI/CD, konwencje kodu, przykłady)?
