<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Currency;
use App\Models\Markup;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EventCalculationEngine
{
    /**
     * @param Event $event
     * @param int|array|null $overrides int for participant_count, or ['participant_count' => x, 'gratis_count' => y, 'staff_count' => z, 'driver_count' => w]
     */
    public function calculate(Event $event, int|array|null $overrides = null): array
    {
        $participantCount = $event->participant_count;
        $gratisCount = $event->gratis_count ?? 0;
        $staffCount = (int) ($event->staff_count ?? 0);
        $driverCount = (int) ($event->driver_count ?? 0);

        if (is_int($overrides)) {
            $participantCount = $overrides;
        } elseif (is_array($overrides)) {
            $participantCount = $overrides['participant_count'] ?? $participantCount;
            $gratisCount = $overrides['gratis_count'] ?? $gratisCount;
            $staffCount = $overrides['staff_count'] ?? $staffCount;
            $driverCount = $overrides['driver_count'] ?? $driverCount;
        }

        if ($participantCount <= 0) {
            $participantCount = 1;
        }

        $guideCount = ($event->guide_count ?? null) !== null
            ? (int) $event->guide_count
            : (method_exists($event, 'guides') ? (int) $event->guides()->count() : 0);
        
        // Gratis count does not increase total people; it's a subset of participants (or used for staff/drivers)
        // So TotalCountForCosts is Participants + Staff + Drivers + Guides
        // (Assuming participant_count is "Total Participants" including those who are gratis)
        $totalCountForCosts = $participantCount + $staffCount + $driverCount + $guideCount;
        
        if ($totalCountForCosts <= 0) {
            $totalCountForCosts = 1;
        }

        // --- Distribute Gratis Slots (Driver > Pilot > Staff > Users) ---
        // NEW LOGIC (2026-01-30):
        // Drivers, Pilots, Staff are ALWAYS Gratis (Cost generated, but not paying).
        // They do NOT consume the 'gratis' count of the event (which is for participants).
        // 'Opiekun' (Staff) also combined with Gratis logic if needed, but we keep buckets for room config.
        
        $driversFree = $driverCount;
        $driversPaid = 0;

        $pilotsFree = $guideCount;
        $pilotsPaid = 0;

        $staffFree = $staffCount;
        $staffPaid = 0;

        $usersFree = min($participantCount, $gratisCount);
        $usersPaid = max(0, $participantCount - $usersFree);

        // Gratis Group Size for Room Calculation buckets
        // Logic:
        // 'driver' bucket -> $driverCount
        // 'staff' bucket -> $staffCount + $guideCount
        // 'gratis' bucket -> $usersFree
        // 'qty' bucket -> $usersPaid

        // Maintain old variable names for roomGroups compatibility below
        $gratisGroupSize = $usersFree; 
        
        // However, user said "Combine Opiekun/Gratis".
        // If we want Opiekun to use Gratis rooms, we should add $staffCount to $gratisGroupSize
        // and set staff bucket to 0?
        // Let's assume standard mapping: Staff -> Staff Rooms.
        // User example: "50 users, 3 gratis, 1 driver, 1 pilot".
        // Payer count = 50 - 3 = 47.
        // Cost count = 50 + 1 + 1 = 52.
        
        // 1. Calculate Program Costs
        $programPoints = $event->programPoints()
            ->where('active', true)
            ->where('include_in_calculation', true)
            ->with(['templatePoint.currency'])
            ->get();
            
        // Prefetch explicit currencies
        $currencyIds = $programPoints->pluck('currency_id')->filter()->unique();
        $currenciesMap = collect();
        if ($currencyIds->isNotEmpty()) {
            $currenciesMap = Currency::whereIn('id', $currencyIds)->get()->keyBy('id');
        }

        $plnTotal = 0;
        $currenciesTotals = [];
        $programPointsBreakdown = [];

        foreach ($programPoints as $point) {
            // Determine currency
            $currencyCode = 'PLN';
            $exchangeRate = 1;

            if ($point->currency_id) {
                $currency = $currenciesMap[$point->currency_id] ?? null;
                if ($currency) {
                    $currencyCode = $currency->symbol;
                    $exchangeRate = $point->exchange_rate ?? $currency->exchange_rate;
                }
            } elseif ($point->templatePoint && $point->templatePoint->currency) {
                $currencyCode = $point->templatePoint->currency->symbol;
                $exchangeRate = $point->exchange_rate ?? $point->templatePoint->currency->exchange_rate;
            }

            $groupSize = $point->group_size ?? 1;
            $unitPrice = $point->unit_price ?? 0;
            $quantity = $point->quantity ?? 1;

            // Calculate cost based on group size
            // Template logic: uses Paying Qty ($participantCount)
            $countForProgramPoints = $participantCount;
            if ($groupSize <= 1) {
                // Per person (uczestnicy)
                $cost = $countForProgramPoints * $unitPrice * $quantity;
            } else {
                // Per group (uczestnicy)
                $groupsNeeded = ceil($countForProgramPoints / $groupSize);
                $cost = $groupsNeeded * $unitPrice * $quantity;
            }

            $convertToPln = $point->convert_to_pln ?? false;
            
            // For breakdown purposes
            $originalCost = $cost;
            $finalCostPLN = 0;

            if ($currencyCode === 'PLN' || $convertToPln) {
                if ($currencyCode !== 'PLN' && $convertToPln) {
                    $cost *= $exchangeRate;
                }
                $plnTotal += $cost;
                $finalCostPLN = $cost;
            } else {
                $currenciesTotals[$currencyCode] = ($currenciesTotals[$currencyCode] ?? 0) + $cost;
            }
            
            $programPointsBreakdown[] = [
                'name' => $point->name ?? $point->templatePoint->name ?? 'Punkt bez nazwy',
                'unit_price' => $unitPrice,
                'currency' => $currencyCode,
                'quantity' => $quantity,
                'total_cost_pln' => $finalCostPLN, // 0 if foreign currency not converted
                'total_cost_original' => $cost,
                'count_basis' => $groupSize <= 1 ? 'per_person' : 'per_group',
                'count_value' => $countForProgramPoints,
                'is_child' => false, // Default to false for Event calculation
                'original_currency' => $currencyCode, // Needed for view
                'exchange_rate' => $exchangeRate,
                'convert_to_pln' => $convertToPln
            ];
        }

        // 1b. Insurance Costs (Paying + Gratis)
        // Check if Event has dayInsurances relation populated
        // Note: Event usually copies dayInsurances logic from Template or has its own.
        // Assuming Event has dayInsurances relation.
        $insuranceTotal = 0;
        $insuranceBreakdown = [];
        if (Schema::hasTable('event_day_insurance') && $event->dayInsurances()->exists()) {
            foreach ($event->dayInsurances as $dayInsurance) {
                $insurance = $dayInsurance->insurance;
                if ($insurance && $insurance->insurance_enabled) {
                    if ($insurance->insurance_per_day || $insurance->insurance_per_person) {
                        $countForInsurance = $totalCountForCosts;
                        $insAmount = $insurance->price_per_person * $countForInsurance;
                        $insuranceTotal += $insAmount;
                        $insuranceBreakdown[] = [
                            'name' => $insurance->name,
                            'total' => $insAmount
                        ];
                    }
                }
            }
        }
        $plnTotal += $insuranceTotal;

        // 1c. Accommodation Costs
        $accommodationTotalPLN = 0;
        
        // Try getting event-specific hotel days first
        $hotelDays = $event->hotelDays()->orderBy('day')->get();
        if ($hotelDays->isEmpty() && $event->eventTemplate) {
            $hotelDays = $event->eventTemplate->hotelDays()->orderBy('day')->get();
        }

        $hotelStructure = []; // NEW: Structure tracking initialized outside, so it is always defined

        if ($hotelDays->isNotEmpty()) {
            $isForeignTrip = true;
            if ($event->eventTemplate && method_exists($event->eventTemplate, 'isForeignTrip')) {
                 $isForeignTrip = $event->eventTemplate->isForeignTrip();
            }

            // Optimization: Prefetch all potential rooms to avoid N queries inside loop
            $allRoomIds = [];
            foreach ($hotelDays as $day) {
                if (!empty($day->hotel_room_ids_qty)) $allRoomIds = array_merge($allRoomIds, $day->hotel_room_ids_qty);
                if (!empty($day->hotel_room_ids_gratis)) $allRoomIds = array_merge($allRoomIds, $day->hotel_room_ids_gratis);
                if (!empty($day->hotel_room_ids_staff)) $allRoomIds = array_merge($allRoomIds, $day->hotel_room_ids_staff);
                if (!empty($day->hotel_room_ids_driver)) $allRoomIds = array_merge($allRoomIds, $day->hotel_room_ids_driver);
            }
            $allRoomIds = array_unique($allRoomIds);
            
            $allRoomsMap = collect();
            if (!empty($allRoomIds)) {
                $allRoomsMap = \App\Models\HotelRoom::whereIn('id', $allRoomIds)
                    ->where('people_count', '>', 0)
                    ->get()
                    ->keyBy('id');
            }

            // Optimization: Prefetch currencies
            $currencyRates = [];
            $neededCurrencies = $allRoomsMap->pluck('currency')->filter(fn($c) => $c && $c !== 'PLN')->unique();
            if ($neededCurrencies->isNotEmpty()) {
                $currencyRates = Currency::whereIn('symbol', $neededCurrencies)->pluck('exchange_rate', 'symbol')->toArray();
            }


            // Custom prices logic moved inside loop for per-day granularity

            // Global Custom Configuration (Reverted)
            // Now containing: room_id, quantity, price, currency, people_count
            $globalCustomPrices = [];
            if (!empty($event->custom_hotel_prices)) {
                foreach ($event->custom_hotel_prices as $cp) {
                    if (isset($cp['room_id'])) $globalCustomPrices[$cp['room_id']] = $cp;
                }
            }
            
            // $hotelStructure is already initialized above

            foreach ($hotelDays as $hotelDay) {
                $dayStructureRaw = []; // Map room_id => data

                // Determine buckets for this day
                // 'qty' -> Users Paid
                // 'staff' -> Staff Paid + Pilots Paid
                // 'driver' -> Drivers Paid
                // 'gratis' -> Everyone Free (DriversFree + PilotsFree + StaffFree + UsersFree)
                
                $dayConfigRaw = $hotelDay->custom_config ?? [];
                $customPrices = [];
                
                // If we have specific day configuration, use it to build overrides
                if (!empty($dayConfigRaw)) {
                     foreach ($dayConfigRaw as $roomId => $dayConf) {
                         $customPrices[$roomId] = $dayConf;
                         $customPrices[$roomId]['room_id'] = $roomId;
                     }
                }

                $roomGroups = [
                    'qty' => [
                        'count' => $usersPaid,
                        'room_ids' => $hotelDay->hotel_room_ids_qty ?? [],
                    ],
                    'gratis' => [
                        // User mentioned "combine opiekun/gratis", so maybe staff should go here if not explicitly staff?
                        // But usually staff has own rooms.
                        // Let's keep logic: Staff -> Staff Rooms, Gratis -> Gratis Rooms.
                        // If they want to merge, they should assign same room IDs in UI.
                        'count' => $gratisGroupSize, 
                        'room_ids' => $hotelDay->hotel_room_ids_gratis ?? [],
                    ],
                    'staff' => [
                        'count' => $staffFree + $pilotsFree, // Use Free counts as they are always free now
                        'room_ids' => $hotelDay->hotel_room_ids_staff ?? [],
                    ],
                    'driver' => [
                        'count' => $driversFree, // Use Free count
                        'room_ids' => $hotelDay->hotel_room_ids_driver ?? [],
                    ],
                ];

                foreach ($roomGroups as $groupType => $groupData) {
                    $peopleCount = $groupData['count'];
                    $roomIds = $groupData['room_ids'];
                    if ($peopleCount <= 0) continue;
                    if (empty($roomIds)) continue; // Cannot calculate without valid rooms

                    $rooms = $allRoomsMap->only($roomIds);
                    if ($rooms->isEmpty()) continue;


                    
                    if (false && $manualRooms->isNotEmpty()) { // Manual Override Disabled to enforce optimal calculation
                        foreach ($manualRooms as $r) {
                             $qty = (int)$customPrices[$r->id]['quantity'];
                             if ($qty < 0) $qty = 0;

                             $cPrice = $r->price;
                             $cCurrency = $r->currency;
                             $cCap = $r->people_count;
                             
                             if (isset($customPrices[$r->id])) {
                                  if (isset($customPrices[$r->id]['price']) && $customPrices[$r->id]['price'] !== '') $cPrice = $customPrices[$r->id]['price'];
                                  if (isset($customPrices[$r->id]['currency'])) $cCurrency = $customPrices[$r->id]['currency'];
                                  if (isset($customPrices[$r->id]['people_count']) && $customPrices[$r->id]['people_count'] !== '') $cCap = $customPrices[$r->id]['people_count'];
                             }

                             $rawPrice = $cPrice; // For display
                             
                             if ($cCurrency !== 'PLN' && $cCurrency) {
                                  $rate = $currencyRates[$cCurrency] ?? 1;
                                  $cPrice *= $rate;
                             }
                             
                             $manualCost += $qty * $cPrice;
                             $peopleCoveredByManual += $qty * $cCap;

                             // RECORD ALLOCATION
                             if ($qty > 0) {
                                 if (!isset($dayStructureRaw[$r->id])) {
                                     $dayStructureRaw[$r->id] = [
                                         'name' => $r->name,
                                         'price' => $rawPrice,
                                         'currency' => $cCurrency,
                                         'qty' => 0, 'gratis' => 0, 'staff' => 0, 'driver' => 0,
                                         'total' => 0
                                     ];
                                 }
                                 $dayStructureRaw[$r->id][$groupType] += ($qty * $cCap); // Approx people count assigned? No, view expects room QTY or People?
                                 // The view table shows 'text-right'>Uczestnicy</td> which implies people count or room count?
                                 // Standard hotel structure usually shows ROOM COUNT. 
                                 // But here columns are: "Uczestnicy", "Gratis"... implying allocation of PEOPLE to that room type?
                                 // Or number of rooms assigned for that group?
                                 // Based on EventTemplatePriceTable logic: $alloc[$groupType] = $toAssign. So it tracks PEOPLE assigned.
                                 // Let's track PEOPLE assigned here for simplicity of matching.
                                 // But wait, $qty here is ROOMS count.
                                 // If I have 1 room for 2 people ($qty=1), I assign 2 people.
                                 
                                 $dayStructureRaw[$r->id][$groupType] += ($qty * $cCap); // Assign capacity to this group
                                 $dayStructureRaw[$r->id]['total'] += ($qty * $rawPrice);
                             }
                        }
                        $accommodationTotalPLN += $manualCost;
                        $peopleCount -= $peopleCoveredByManual;
                        $rooms = $rooms->diffKeys($manualRooms);
                    }
                    
                    if ($peopleCount <= 0) continue; 
                    if ($rooms->isEmpty()) continue;


                    // DP Algorithm (PLN only) - Aligned with EventTemplatePriceTable
                    // Match Template Capacity Logic
                    $totalRoomCap = 0;
                    foreach($rooms as $r) {
                        $cap = $r->people_count;
                        if (isset($customPrices[$r->id]['people_count']) && $customPrices[$r->id]['people_count'] !== '') {
                            $cap = $customPrices[$r->id]['people_count'];
                        }
                        $totalRoomCap += $cap;
                    }
                    $maxCapacity = $totalRoomCap * ($peopleCount > 0 ? $peopleCount : 1);
                    if ($maxCapacity < $peopleCount) $maxCapacity = $peopleCount * 10;

                    
                    $dpPln = array_fill(0, $maxCapacity + 1, INF);
                    $dpPln[0] = 0;
                    $choice = array_fill(0, $maxCapacity + 1, null); // Track choices
                    
                    foreach ($rooms as $room) {
                        $cPrice = $room->price;
                        $cCurrency = $room->currency;
                        $cCap = $room->people_count;
                        
                        // Apply Overrides
                        if (isset($customPrices[$room->id])) {
                             if (isset($customPrices[$room->id]['price']) && $customPrices[$room->id]['price'] !== '') 
                                 $cPrice = $customPrices[$room->id]['price'];
                             
                             if (isset($customPrices[$room->id]['currency'])) 
                                 $cCurrency = $customPrices[$room->id]['currency'];
                                 
                             if (isset($customPrices[$room->id]['people_count']) && $customPrices[$room->id]['people_count'] !== '') 
                                 $cCap = $customPrices[$room->id]['people_count'];
                        }
                        
                        // Convert
                        if ($cCurrency !== 'PLN' && $cCurrency) {
                             $rate = $currencyRates[$cCurrency] ?? 1;
                             $cPrice *= $rate;
                        }
                        
                        $pricePln = $cPrice;
                        $peopleCountForRoom = $cCap;
                        
                        for ($i = $peopleCountForRoom; $i <= $maxCapacity; $i++) {
                            if ($dpPln[$i - $peopleCountForRoom] + $pricePln < $dpPln[$i]) {
                                $dpPln[$i] = $dpPln[$i - $peopleCountForRoom] + $pricePln;
                                $choice[$i] = $room->id;
                            }
                        }
                    }
                    
                    $minCostPln = INF;
                    $bestI = 0;
                    for ($i = $peopleCount; $i <= $maxCapacity; $i++) {
                        if ($dpPln[$i] < $minCostPln) {
                            $minCostPln = $dpPln[$i];
                            $bestI = $i;
                        }
                    }

                    if ($minCostPln !== INF) {
                        $accommodationTotalPLN += $minCostPln;

                        // Backtracking to fill structure
                        $curr = $bestI;
                        $allocRooms = [];
                        while($curr > 0 && $choice[$curr] !== null) {
                             $rId = $choice[$curr];
                             $allocRooms[] = $rId;
                             
                             // Find Cap
                             $room = $allRoomsMap[$rId];
                             $ov = $customPrices[$rId] ?? [];
                             $cap = isset($ov['people_count']) && $ov['people_count'] !== '' ? $ov['people_count'] : $room->people_count;
                             
                             $curr -= $cap;
                        }
                        
                        // Count occurrences
                        $roomCounts = array_count_values($allocRooms);
                        
                        // Assign people to these rooms
                        $peopleAssigned = 0;
                        foreach($roomCounts as $rId => $count) {
                             $room = $allRoomsMap[$rId];
                             
                             // Overrides
                             $ov = $customPrices[$rId] ?? [];
                             $cPrice = isset($ov['price']) && $ov['price'] !== '' ? $ov['price'] : $room->price;
                             $cCurrency = isset($ov['currency']) ? $ov['currency'] : $room->currency;
                             $cCap = isset($ov['people_count']) && $ov['people_count'] !== '' ? $ov['people_count'] : $room->people_count;

                             // How many people from this group go into these rooms?
                             // We are just allocating cost, user doesn't care exactly which person is where usually,
                             // but we need to show numbers.
                             // Total capacity = count * cCap.
                             // People to assign = $peopleCount.
                             // Proportional assignment? Or fill up?
                             // Logic: The DP ensured we have enough capacity.
                             // Just assign $toAssign = min($peopleCount - $peopleAssigned, $count * $cCap).
                             // Wait, we need to split this per ROOM instance? No, view aggregates by room name/type.
                             
                             $capacityTotal = $count * $cCap;
                             $taking = min($peopleCount - $peopleAssigned, $capacityTotal);
                             if ($taking < 0) $taking = 0;
                             
                             if (!isset($dayStructureRaw[$rId])) {
                                 $dayStructureRaw[$rId] = [
                                     'name' => $room->name,
                                     'price' => $cPrice,
                                     'currency' => $cCurrency,
                                     'qty' => 0, 'gratis' => 0, 'staff' => 0, 'driver' => 0,
                                     'total' => 0
                                 ];
                             }
                             $dayStructureRaw[$rId][$groupType] += $taking;
                             $dayStructureRaw[$rId]['total'] += ($count * $cPrice); // Full cost of rooms used
                             
                             $peopleAssigned += $taking;
                        }
                    }
                }
                
                // Convert dayStructureRaw to list for view
                $formattedRooms = [];
                foreach ($dayStructureRaw as $rId => $d) {
                    $formattedRooms[] = $d;
                }
                if (!empty($formattedRooms)) {
                    $hotelStructure[] = [
                        'day' => $hotelDay->day,
                        'rooms' => $formattedRooms
                    ];
                }
            }

        }
        $plnTotal += $accommodationTotalPLN;

        // 2. Calculate Transport Costs
        $transportCostPLN = 0;
        if ($event->transport_price_override !== null && $event->transport_price_override !== '') {
            $transportCostPLN = (float) $event->transport_price_override;
        } elseif ($event->bus) {
            $bus = $event->bus;
            
            // --- LOGIKA Z EventTemplateCalculationEngine (Widget) ---
            // 1. Oblicz dystans (d1 + d2 + program)
            
            $startPlaceId = $event->start_place_id ?? null;
            $templateStartId = $event->eventTemplate?->start_place_id ?? null;
            $templateEndId = $event->eventTemplate?->end_place_id ?? null;
            $programKm = $event->program_km ?? 0;

            $d1 = 0;
            $d2 = 0;

            if ($startPlaceId && $templateStartId) {
                if ($startPlaceId === $templateStartId) {
                    $d1 = 0;
                } else {
                    $d1 = \App\Models\PlaceDistance::where('from_place_id', $startPlaceId)
                        ->where('to_place_id', $templateStartId)
                        ->first()?->distance_km ?? 0;
                }
            }

            if ($templateEndId && $startPlaceId) {
                if ($templateEndId === $startPlaceId) {
                    $d2 = 0;
                } else {
                    $d2 = \App\Models\PlaceDistance::where('from_place_id', $templateEndId)
                        ->where('to_place_id', $startPlaceId)
                        ->first()?->distance_km ?? 0;
                }
            }

            // Obsługa manualnego transfer_km (nadpisuje d1+d2)
            $transferPart = ($event->transfer_km && $event->transfer_km > 0) 
                ? $event->transfer_km 
                : ($d1 + $d2);
                
            $basicDistance = $transferPart + $programKm;
            
            // Formuła widgeta: 1.1 * dystans + 50 km
            $defaultTransportKm = 1.1 * $basicDistance + 50;
            
            // 2. Koszt autokaru (pakiet + nadprzebieg)
            $duration = $event->duration_days ?? 1;
            $includedKm = $duration * ($bus->package_km_per_day ?? 0);
            $baseCost = $duration * ($bus->package_price_per_day ?? 0);
            
            $totalKm = $defaultTransportKm;

            if ($totalKm <= $includedKm) {
                $busTransportCost = $baseCost;
            } else {
                $extraKm = $totalKm - $includedKm;
                $busTransportCost = $baseCost + ($extraKm * ($bus->extra_km_price ?? 0));
            }

            // 3. Liczba autokarów
            $busCapacity = ($bus->capacity && $bus->capacity > 0) ? $bus->capacity : 50;
            $busCount = (int) ceil($totalCountForCosts / $busCapacity);
            
            $busTransportCostTotal = $busTransportCost * $busCount;

            // 4. Konwersja waluty
            if ($bus->currency && $bus->currency !== 'PLN') {
                $currency = Currency::where('symbol', $bus->currency)->first();
                $exchangeRate = $currency?->exchange_rate ?? 1;
                $busTransportCostTotal *= $exchangeRate;
            }

            $transportCostPLN = $busTransportCostTotal;
        }

        // Add transport to PLN total base
        $plnTotal += $transportCostPLN;

        // 3. Apply Markups
        $markupPercent = $this->getMarkupPercent($event);
        $markupAmount = $plnTotal * ($markupPercent / 100);

        // Check for minimum markup
        $minMarkup = 0;
        if ($event->markup && $event->markup->min_amount) {
            $minMarkup = $event->markup->min_amount;
        }

        $isMinMarkupApplied = false;
        if ($markupAmount < $minMarkup) {
            $markupAmount = $minMarkup;
            $isMinMarkupApplied = true;
        }

        // 4. Calculate Taxes
        $totalTaxAmount = 0;
        $taxes = collect();

        if ($event->eventTemplate) {
            $taxes = $event->eventTemplate->taxes;
        }

        foreach ($taxes as $tax) {
            if (!$tax->is_active) continue;
            $taxAmount = $tax->calculateTaxAmount($plnTotal, $markupAmount);
            $totalTaxAmount += $taxAmount;
        }

        $priceWithTax = $plnTotal + $markupAmount + $totalTaxAmount;
        
        // Calculate price per paying person (participants only)
        // Paying participants = Total Participants - Free Participants
        // (Free participants are calculated earlier as $usersFree)
        // If staff/drivers are unpaid, they are not in the divisor.
        // We assume price is distributed among PAYING PARTICIPANTS.
        $payingParticipants = max(1, $usersPaid); 
        $pricePerPerson = $priceWithTax / $payingParticipants;

        // 5. Rounding
        $roundingMode = $event->price_rounding_mode ?? 'ceil_5';
        $finalPricePerPerson = $pricePerPerson;

        if ($roundingMode === 'ceil_5') {
            $finalPricePerPerson = ceil($pricePerPerson / 5) * 5;
        } elseif ($roundingMode === 'ceil_1') {
            $finalPricePerPerson = ceil($pricePerPerson);
        }

        return [
            'qty' => $participantCount,
            'name' => $participantCount . ' osób',
            'program_cost' => $plnTotal - $transportCostPLN - $insuranceTotal - $accommodationTotalPLN,
            'program_points_breakdown' => $programPointsBreakdown,
            'accommodation_cost' => $accommodationTotalPLN,
            'insurance_cost' => $insuranceTotal,
            'transport_cost' => $transportCostPLN,
            'markup_amount' => $markupAmount,
            'min_markup_amount' => $minMarkup,
            'is_min_markup_applied' => $isMinMarkupApplied,
            'tax_amount' => $totalTaxAmount,
            'total_cost' => $priceWithTax,
            'cost_per_person' => $pricePerPerson,
            'final_price_per_person' => $finalPricePerPerson,
            'currencies' => $currenciesTotals,
            'currencies_per_person' => $payingParticipants > 0
                ? array_map(fn ($amount) => $amount / $payingParticipants, $currenciesTotals)
                : $currenciesTotals,
            'hotel_structure' => $hotelStructure, // Detailed breakdown
            'insurance_breakdown' => $insuranceBreakdown, // NEW
            'total_count_for_costs' => $totalCountForCosts,
            'participant_count' => $participantCount,
            'gratis_count' => $gratisCount,
            'staff_count' => $staffCount,
            'driver_count' => $driverCount,
            'guide_count' => $guideCount,
        ];
    }

    private function getMarkupPercent(Event $event): float
    {
        if ($event->markup && $event->markup->percent !== null) {
            return $event->markup->percent;
        }

        $default = Markup::where('is_default', true)->first();
        return $default?->percent ?? 20;
    }
}
