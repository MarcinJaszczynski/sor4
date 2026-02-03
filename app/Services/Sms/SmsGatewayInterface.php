<?php

namespace App\Services\Sms;

interface SmsGatewayInterface
{
    /**
     * Wyślij wiadomość SMS.
     *
     * @param string $to Numer telefonu odbiorcy
     * @param string $message Treść wiadomości
     * @return bool Status wysyłki
     */
    public function send(string $to, string $message): bool;
}
