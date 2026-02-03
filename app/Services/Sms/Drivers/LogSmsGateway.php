<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\SmsGatewayInterface;
use Illuminate\Support\Facades\Log;

class LogSmsGateway implements SmsGatewayInterface
{
    public function send(string $to, string $message): bool
    {
        // W trybie demo/log nie wysyłamy prawdziwych SMS, tylko zapisujemy w logach
        Log::info(" [SMS DEMO] Do: {$to} | Treść: {$message}");
        
        return true;
    }
}
