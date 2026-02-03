# Implementacja Zadań dla Punktów Programu Szablonu

## Opis zmian
Dodano możliwość zarządzania zadaniami (Tasks) bezpośrednio z poziomu edytora drzewa programu szablonu (`EventProgramTreeEditor`).

## Zmodyfikowane pliki
1.  `app/Livewire/EventProgramTreeEditor.php`:
    *   Dodano obsługę modala zadań.
    *   Dodano metody `openTaskModal`, `saveTask`, `deleteTask`.
    *   Zadania są przypisywane do modelu `EventTemplateProgramPoint` (definicja punktu).

2.  `resources/views/livewire/event-program-tree-editor.blade.php`:
    *   Dodano przycisk "Zadania" (ikona listy kontrolnej) przy każdym punkcie programu.
    *   Dodano modal wyświetlający listę zadań i formularz dodawania nowego zadania.

## Działanie
*   Kliknięcie ikony zadań przy punkcie programu otwiera modal.
*   W modalu widoczna jest lista zadań przypisanych do tego konkretnego punktu programu (`EventTemplateProgramPoint`).
*   Można dodać nowe zadanie, określając tytuł, opis, status, priorytet, osobę przypisaną i datę wykonania.
*   Zadania są współdzielone dla danej definicji punktu programu (widoczne we wszystkich szablonach używających tego punktu).
