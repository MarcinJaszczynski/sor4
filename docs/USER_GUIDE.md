# Dokumentacja systemu BP RAFA — Instrukcja użytkownika

Data: 7 stycznia 2026

Wersja: 1.0

---

## Spis treści

- Wprowadzenie
- Role i uprawnienia
  - Administrator (Biuro)
  - Pilot
  - Uczestnik / Klient
- Logowanie i Strefa Klienta
- Panel Administratora (Filament)
  - Zarządzanie kontami e-mail
  - Zarządzanie wydatkami pilota
  - Obsługa wiadomości e-mail
- Portal Pilota
  - Dodawanie wydatków i załączników
  - Przegląd wiadomości związanych z imprezą
  - Uwagi techniczne przy punktach programu
- Obsługa e-mail w aplikacji
  - Konfiguracja kont IMAP/SMTP
  - Synchronizacja i ręczna synchronizacja
  - Wysyłanie wiadomości z panelu
- FAQ i najczęstsze procedury
- Regeneracja PDF dokumentacji

---

## Wprowadzenie
Aplikacja BP RAFA zawiera panel administracyjny (Filament) oraz Strefę Klienta, w której znajdują się dedykowane widoki dla uczestników, organizatorów i pilotów wycieczek. Dokumentacja opisuje podstawowe operacje dla trzech ról: administratora, pilota oraz uczestnika.

## Role i uprawnienia

### Administrator (Biuro)
- Pełny dostęp do panelu Filament.
- Może zarządzać wydarzeniami (imprezami), użytkownikami, kontami e-mail, zatwierdzać wydatki pilotów i przeglądać wysłane/odebrane wiadomości.
- W Filament:
  - `PilotExpenseResource` — zatwierdzanie/rejestrowanie wydatków zgłoszonych przez pilotów.
  - `EmailAccountResource` — dodawanie i konfiguracja kont e-mail (IMAP/SMTP).
  - `EmailMessageResource` — przegląd i wysyłanie wiadomości.

### Pilot
- Ma dostęp do Strefy Klienta po przypisaniu do wydarzenia z rolą `pilot`.
- Może zgłaszać wydatki poniesione na miejscu oraz dołączać skany/zdjęcia dokumentów.
- Widzi zakładkę `Wiadomości` zawierającą maile powiązane z wydarzeniem oraz `Uwagi dla pilota` przy punktach programu.

### Uczestnik / Klient
- Po aktywacji/akceptacji kodem dostępu widzi program, dokumenty i możliwość kontaktu z biurem.
- Może przesyłać wiadomości przez formularz kontaktowy oraz symulować płatności (jeśli dostępne).

## Logowanie i Strefa Klienta
1. Wejdź na: `/strefa-klienta`.
2. Zaloguj się używając e-mail i hasła (konto musi być przypisane do wydarzenia).
3. Po zalogowaniu sesja trzyma `portal_event_id`, `portal_role` i `portal_user_id`.
4. Dostępne menu zależą od roli: uczestnik widzi program i dokumenty, pilot widzi dodatkowo `Rozliczenia` i `Wiadomości`.

## Panel Administratora (Filament)

### Zarządzanie kontami e-mail
- Lokalizacja: `Poczta -> Konta` (Filament).
- Pola: nazwa konta, e-mail, użytkownik/login, zaszyfrowane hasło, ustawienia IMAP/SMTP, widoczność (private/public/shared), lista użytkowników, którym konto jest udostępnione.
- Po dodaniu konta można synchronizować wiadomości (komenda: `php artisan emails:sync`) lub użyć przycisku `Synchronizuj` w liście wiadomości.

### Zarządzanie wydatkami pilota
- Lokalizacja: `Finanse -> Wydatki Pilotów`.
- Filtracja po statusie: `Oczekuje`, `Zatwierdzony`, `Odrzucony`.
- Możliwość przejrzenia załączników (zdjęć dokumentów) i zmiany statusu.

### Obsługa wiadomości e-mail
- `Poczta -> Wiadomości` wyświetla maile synchronizowane z kont IMAP.
- Można przeglądać, odpowiadać i wysyłać nowe wiadomości z panelu (korzystając z ustawień SMTP z `EmailAccount`).
- Wiadomości można przypinać do rekordów (Imprezy, Zadania) przez Relation Managers.

## Portal Pilota — instrukcja krótkia

### Dodawanie wydatku
1. Zaloguj się do Strefy Klienta jako pilot.
2. W menu wybierz `Rozliczenia`.
3. Kliknij `Wprowadź nowy wydatek`.
4. Wypełnij pola: kwota, waluta, data, opis i dołącz zdjęcie/skan dokumentu (JPEG/PNG/PDF, max 5MB).
5. Po wysłaniu wydatek trafi do bazy ze statusem `Oczekuje`.
6. Biuro zatwierdzi lub odrzuci wydatek w panelu administracyjnym.

### Przegląd wiadomości
- Przejdź do `Wiadomości` — zobaczysz wszystkie e-maile powiązane z imprezą.
- Rozwijaj pojedyncze wiadomości, aby zobaczyć treść HTML lub tekstową.

### Uwagi przy punktach programu
- Jeśli organizator dodał `Uwagi dla pilota` przy punkcie programu, zostaną one wyświetlone w panelu pilota pod opisem punktu.

## Obsługa e-mail w aplikacji — instrukcja techniczna (dla admina)

### Konfiguracja kont IMAP/SMTP
1. W panelu Filament wybierz `Poczta -> Konta`.
2. Dodaj nowe konto z danymi IMAP (host, port, encryption) i SMTP (host, port, encryption).
3. Upewnij się, że hasło jest poprawnie zapisane (szyfrowane w bazie).

### Synchronizacja
- Ręczna synchronizacja: w Filament -> Wiadomości użyj przycisku `Synchronizuj` lub uruchom komendę:

```bash
php artisan emails:sync --days=7
```

- Komenda ściągnie nowe wiadomości z folderu INBOX i zapisze je w `email_messages`.
- Mechanizm automatycznego wiązania spróbuje dopasować temat wiadomości do wzorców `[IMP-123]`, `[ID: 123]`, `[ZAD-456]`, `[TASK-456]` i automatycznie połączy mail z odpowiednią imprezą lub zadaniem.

### Wysyłanie wiadomości
- Z poziomu Filament lub RelationManagerów użyj akcji `Wyślij e-mail` i wybierz konto (EmailAccount). System skonfiguruje tymczasowego mailera i wyśle wiadomość przez SMTP.

### Załączniki w wiadomościach

- System obsługuje załączniki, które są przechowywane na dysku `public` w katalogu `email_attachments`.
- Administrator lub pilot może dodawać załączniki do wiadomości z poziomu Filament (RelationManager "Attachments") lub podczas synchronizacji IMAP pliki dołączone do wiadomości zostaną zapisywane jako rekordy `email_attachments` powiązane z odpowiednim `email_message`.
- W portalu (Strefa Klienta) przy rozwinięciu wiadomości dostępna jest lista załączników z linkami do podglądu/pobrania.

### Harmonogram synchronizacji (scheduler)

- Aby automatycznie synchronizować wiadomości, w `app/Console/Kernel.php` dodano wpis uruchamiający komendę `emails:sync --days=7` co 15 minut.
- Upewnij się, że cron uruchamia scheduler Laravela na serwerze (np. co minutę):

```bash
# wpis w crontab (uruchom jako użytkownik aplikacji):
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Jeśli chcesz inny interwał synchronizacji, zedytuj `app/Console/Kernel.php` i dostosuj harmonogram.

## FAQ i procedury
Q: Co zrobić, gdy pilot przesłał zły plik?
A: Biuro może pobrać załącznik z rekordu `PilotExpense` i poprosić pilota o ponowne przesłanie.

Q: Jak przywrócić synchronizację, gdy IMAP przestał działać?
A: Sprawdź ustawienia konta w `EmailAccountResource`, zweryfikuj host/port/szyfrowanie i hasło. Możesz też uruchomić debug komendą:

```bash
php artisan emails:sync --account=ID
```

## Regeneracja PDF dokumentacji
Aby wygenerować PDF lokalnie (jeśli masz `wkhtmltopdf` lub `pandoc`):

- Przy użyciu `wkhtmltopdf`:

```bash
# w katalogu projektu
php -r "echo file_get_contents('docs/USER_GUIDE.html');" > /tmp/ug.html
wkhtmltopdf /tmp/ug.html docs/USER_GUIDE.pdf
```

- Przy użyciu `pandoc`:

```bash
pandoc docs/USER_GUIDE.md -o docs/USER_GUIDE.pdf --pdf-engine=xelatex
```

Jeśli chcesz, mogę spróbować wygenerować PDF teraz na serwerze deweloperskim (spróbuję użyć `wkhtmltopdf` lub `pandoc`).

---

*Plik wygenerowany automatycznie. W razie potrzeby dopracuję treść i dodam zrzuty ekranu.*
