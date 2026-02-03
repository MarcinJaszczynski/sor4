# Globalna Integracja Zadań

## Opis zmian
Rozszerzono dostępność przycisku "Zadania" na główne widoki list (tabel) dla kluczowych zasobów oraz dodano obsługę podzadań.

## Zmodyfikowane pliki
1.  `app/Filament/Resources/EventResource.php`: Dodano akcję "Zadania" do tabeli listy imprez.
2.  `app/Filament/Resources/EventTemplateResource.php`: Dodano akcję "Zadania" do tabeli listy szablonów.
3.  `app/Filament/Resources/TaskResource.php`: Dodano akcję "Podzadania" do tabeli listy zadań.
4.  `app/Models/Task.php`: Dodano relację `tasks()` (polimorficzną) jako alias dla podzadań lub dla spójności interfejsu `taskable`.

## Działanie
*   **Lista Imprez**: W tabeli imprez, przy każdym wierszu, dostępny jest przycisk "Zadania". Pozwala on na szybkie dodanie zadania do imprezy bez wchodzenia w jej edycję.
*   **Lista Szablonów**: Analogicznie, w tabeli szablonów dostępny jest przycisk "Zadania".
*   **Lista Zadań**: W tabeli zadań (Kanban/Lista) dostępny jest przycisk "Podzadania", który otwiera modal do zarządzania zadaniami podrzędnymi (lub powiązanymi polimorficznie, jeśli używamy `taskable` na `Task`).

## Uwagi
*   System zadań jest teraz spójny i dostępny z poziomu list, edycji oraz dedykowanych menedżerów relacji.
*   Komponent `TaskManager` automatycznie wykrywa kontekst (model) i przypisuje zadania odpowiednio.
