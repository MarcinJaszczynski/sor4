<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use App\Services\Finance\InstallmentAutoGenerator;
use App\Services\Finance\InstallmentTaskSyncService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateContract extends CreateRecord
{
    protected static string $resource = ContractResource::class;

    protected array $installmentAutoData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->installmentAutoData = [
            'enabled' => (bool) ($data['auto_generate_installments'] ?? false),
            'replace_existing' => (bool) ($data['replace_existing_installments'] ?? false),
            'deposit_percent' => (float) ($data['installment_deposit_percent'] ?? 30),
            'deposit_due_date' => $data['installment_deposit_due_date'] ?? null,
            'final_due_days_before_start' => (int) ($data['installment_final_due_days_before_start'] ?? 14),
        ];

        unset(
            $data['auto_generate_installments'],
            $data['replace_existing_installments'],
            $data['installment_deposit_percent'],
            $data['installment_deposit_due_date'],
            $data['installment_final_due_days_before_start'],
        );

        return $data;
    }

    protected function afterCreate(): void
    {
        if (!($this->installmentAutoData['enabled'] ?? false)) {
            return;
        }

        $generator = app(InstallmentAutoGenerator::class);

        $generator->generate($this->record, [
            'replace_existing' => $this->installmentAutoData['replace_existing'] ?? false,
            'deposit_percent' => $this->installmentAutoData['deposit_percent'] ?? 30,
            'deposit_due_date' => $this->installmentAutoData['deposit_due_date'] ?? now()->toDateString(),
            'final_due_days_before_start' => $this->installmentAutoData['final_due_days_before_start'] ?? 14,
        ]);

        app(InstallmentTaskSyncService::class)->sync([
            'days_ahead' => 14,
            'author_id' => Auth::id() ?? 1,
        ]);
    }
}
