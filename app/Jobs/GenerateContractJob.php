<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\ContractTemplate;
use App\Services\ContractGeneratorService;
use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Notifications\ContractGenerated;

class GenerateContractJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $eventId;
    public int $templateId;
    public string $contractNumber;
    public \DateTime|string $dateIssued;
    public string $placeIssued;

    public function __construct(int $eventId, int $templateId, string $contractNumber, $dateIssued, string $placeIssued)
    {
        $this->eventId = $eventId;
        $this->templateId = $templateId;
        $this->contractNumber = $contractNumber;
        $this->dateIssued = $dateIssued;
        $this->placeIssued = $placeIssued;
    }

    public function handle(ContractGeneratorService $generator)
    {
        try {
            $event = Event::find($this->eventId);
            $template = ContractTemplate::find($this->templateId);

            if (! $event || ! $template) {
                Log::warning('GenerateContractJob: missing event or template', ['event' => $this->eventId, 'template' => $this->templateId]);
                return;
            }

            $content = $generator->generate($event, $template);

            $contract = Contract::create([
                'event_id' => $event->id,
                'contract_template_id' => $template->id,
                'contract_number' => $this->contractNumber,
                'status' => 'generated',
                'content' => $content,
                'date_issued' => $this->dateIssued,
                'place_issued' => $this->placeIssued,
            ]);

            // Notify event creator (if available) and log
            try {
                $creator = $event->creator;
                if ($creator) {
                    $creator->notify(new ContractGenerated($contract->id));
                }
            } catch (\Throwable $e) {
                Log::warning('GenerateContractJob: notification failed', ['error' => $e->getMessage()]);
            }

            Log::info('GenerateContractJob: contract generated', ['contract_id' => $contract->id]);
        } catch (\Throwable $e) {
            Log::error('GenerateContractJob failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }
}
