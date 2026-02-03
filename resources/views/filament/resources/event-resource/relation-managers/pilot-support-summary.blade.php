<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs text-gray-500">Potrzebuje (wg walut)</div>
            <div class="mt-2 flex flex-wrap gap-2">
                @forelse($neededTotals as $currency => $total)
                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">
                        {{ number_format($total, 2, ',', ' ') }} {{ $currency }}
                    </span>
                @empty
                    <span class="text-sm text-gray-400">—</span>
                @endforelse
            </div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs text-gray-500">Otrzymał (wg walut)</div>
            <div class="mt-2 flex flex-wrap gap-2">
                @forelse($givenTotals as $currency => $total)
                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">
                        {{ number_format($total, 2, ',', ' ') }} {{ $currency }}
                    </span>
                @empty
                    <span class="text-sm text-gray-400">—</span>
                @endforelse
            </div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs text-gray-500">Wydatki zgłoszone (wg walut)</div>
            <div class="mt-2 flex flex-wrap gap-2">
                @forelse($expenseTotals as $currency => $total)
                    <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">
                        {{ number_format($total, 2, ',', ' ') }} {{ $currency }}
                    </span>
                @empty
                    <span class="text-sm text-gray-400">—</span>
                @endforelse
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
        <div class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Ostatnie wydatki pilota</div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-gray-500">
                    <tr>
                        <th class="text-left py-2">Data</th>
                        <th class="text-left py-2">Punkt programu</th>
                        <th class="text-left py-2">Opis</th>
                        <th class="text-right py-2">Kwota</th>
                        <th class="text-left py-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($latestExpenses as $expense)
                        <tr>
                            <td class="py-2 text-gray-700 dark:text-gray-200">{{ optional($expense->expense_date)->format('d.m.Y') }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-200">
                                {{ $expense->eventProgramPoint?->templatePoint?->name ?? $expense->eventProgramPoint?->name ?? '—' }}
                            </td>
                            <td class="py-2 text-gray-700 dark:text-gray-200">{{ $expense->description ?: '—' }}</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-200">
                                {{ number_format($expense->amount, 2, ',', ' ') }} {{ $expense->currency }}
                            </td>
                            <td class="py-2">
                                @php
                                    $status = $expense->status ?? 'pending';
                                    $label = match ($status) {
                                        'approved' => 'Zatwierdzony',
                                        'rejected' => 'Odrzucony',
                                        default => 'Oczekuje',
                                    };
                                    $class = match ($status) {
                                        'approved' => 'bg-emerald-100 text-emerald-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                        default => 'bg-yellow-100 text-yellow-700',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold {{ $class }}">
                                    {{ $label }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-2 text-gray-500" colspan="5">Brak wydatków.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
