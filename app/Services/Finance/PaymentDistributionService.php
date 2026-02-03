<?php

namespace App\Services\Finance;

use App\Models\Contract;
use App\Models\Event;
use App\Models\EventCost;
use App\Models\EventPayment;
use Illuminate\Support\Str;

class PaymentDistributionService
{
    /**
     * Automatycznie alokuje środki z płatności na koszty (np. ubezpieczenia).
     * Zgodnie z zasadą Split Payment (logicznego podziału wpłaty).
     */
    public function distribute(EventPayment $payment, ?Contract $contract = null): array
    {
        $distributionLog = [];
        
        if (!$contract) {
            // Jeśli nie podano kontraktu, próbujemy go zgadnąć z opisu (o ile to możliwe)
            // Ale w tym przypadku zakładamy, że wywołujący zna kontekst.
            return ['status' => 'skipped', 'reason' => 'No contract linked'];
        }

        // 1. Sprawdź czy kontrakt ma naliczone ubezpieczenie
        // Założenie: Koszty ubezpieczenia są tworzone jako EventCost z opisem zawierającym numer umowy lub nazwisko
        // LUB po prostu szukamy kosztów typu 'Ubezpieczenie' powiązanych z tym eventem (uproszczenie)
        
        $costs = EventCost::where('event_id', $payment->event_id)
            ->where('is_paid', false)
            ->where('name', 'like', '%Ubezpieczenie%') // Proste wykrywanie kosztów ubezpieczeniowych
            ->get();

        // 2. Logika "Split Payment" - Priorytet dla ubezpieczeń
        // Jeśli wpłata >= koszt ubezpieczenia, oznaczamy koszt jako "Możliwy do opłacenia" lub "Opłacony przez Klienta"
        // W tym modelu EventCost (Wydatki) ma flagę is_paid (Czy MY zapłaciliśmy).
        // Ale "Split Payment" tutaj oznacza: Klient wpłacił -> My mamy kasę na ubezpieczenie.
        // Możemy np. zaktualizować opis kosztu lub ustawić flagę (jeśli dodamy taką w przyszłości).
        
        foreach ($costs as $cost) {
            // Sprawdź czy koszt dotyczy tej umowy (np. w opisie kosztu jest numer umowy)
            if (Str::contains($cost->name, $contract->contract_number)) {
                
                $amountToCover = $cost->amount;
                
                // Tutaj można dodać logikę częściowego pokrycia, ale dla uproszczenia:
                // Logujemy fakt, że wpłata pokrywa ubezpieczenie.
                
                $distributionLog[] = [
                    'cost_id' => $cost->id,
                    'cost_name' => $cost->name,
                    'amount_allocated' => min($payment->amount, $amountToCover),
                    'message' => 'Zidentyfikowano wpłatę na poczet ubezpieczenia. Środki zabezpieczone.',
                ];
                
                // TODO: W przyszłości można tu zmienić status kosztu na "Funded" (Pokryty)
            }
        }

        // Jeśli nie znaleziono kosztów, a jest to zaliczka, można stworzyć automat
        // Scenariusz: Wpłata zaliczki -> Automatyczne utworzenie kosztu ubezpieczenia
        if ($payment->is_advance && $costs->isEmpty()) {
            // Symulacja tworzenia kosztu
            /*
            $newCost = EventCost::create([
                'event_id' => $payment->event_id,
                'name' => 'Ubezpieczenie - Umowa ' . $contract->contract_number,
                'amount' => 50.00, // Przykładowa kwota lub z tabeli Insurance
                'payment_date' => now()->addDays(7), // Płatne do ubezpieczyciela za tydzień
            ]);
            $distributionLog[] = ['action' => 'Created Cost', 'id' => $newCost->id];
            */
        }

        return $distributionLog;
    }
}
