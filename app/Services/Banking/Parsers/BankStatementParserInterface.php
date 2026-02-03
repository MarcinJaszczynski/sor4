<?php

namespace App\Services\Banking\Parsers;

use App\Services\Banking\DTO\BankTransactionDTO;
use Illuminate\Support\Collection;
use SplFileObject;

interface BankStatementParserInterface
{
    /**
     * @return Collection<int, BankTransactionDTO>
     */
    public function parse(string $filePath): Collection;
}
