# Integracja Zadań z Imprezami i Elementami Powiązanymi

## Opis zmian
Rozszerzono system zadań (Tasks) o możliwość dodawania zadań do:
1.  **Punktów Programu Imprezy** (w edycji Imprezy).
2.  **Kosztów** (w edycji Imprezy).
3.  **Umów** (w edycji Imprezy oraz w edycji Umowy).

## Zmodyfikowane pliki
1.  `app/Models/Contract.php`: Dodano relację `tasks()` (polimorficzną).
2.  `app/Filament/Resources/ContractResource.php`: Dodano `TasksRelationManager` do zasobu Umów.
3.  `app/Filament/Resources/EventResource/RelationManagers/ProgramPointsRelationManager.php`: Dodano akcję "Zadania" w tabeli punktów programu.
4.  `app/Filament/Resources/EventResource/RelationManagers/CostsRelationManager.php`: Dodano akcję "Zadania" w tabeli kosztów.
5.  `app/Filament/Resources/EventResource/RelationManagers/ContractsRelationManager.php`: Dodano akcję "Zadania" w tabeli umów.

## Nowe komponenty
1.  `app/Livewire/TaskManager.php`: Uniwersalny komponent Livewire do zarządzania zadaniami w modalu.
2.  `resources/views/livewire/task-manager.blade.php`: Widok komponentu TaskManager.
3.  `resources/views/livewire/task-manager-wrapper.blade.php`: Wrapper do osadzania komponentu w modalu Filamenta.

## Działanie
*   W edycji Imprezy (`EventResource`), w zakładkach "Program", "Koszty", "Umowy", przy każdym wierszu tabeli dostępna jest ikona "Zadania".
*   Kliknięcie ikony otwiera modal, w którym można dodawać, przeglądać i usuwać zadania przypisane do konkretnego elementu (punktu programu, kosztu, umowy).
*   Zadania te są widoczne również na głównej tablicy Kanban (jeśli zostanie odpowiednio skonfigurowana do wyświetlania wszystkich typów zadań).
