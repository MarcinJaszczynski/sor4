<?php

namespace App\Services\Banking\DTO;

use Carbon\Carbon;

class BankTransactionDTO
{
    public function __construct(
        public string $transactionDate,
        public float $amount,
        public string $senderName,
        public string $senderAccount,
        public string $title,
        public string $currency = 'PLN',
        public ?string $transactionId = null,
    ) {}
}
