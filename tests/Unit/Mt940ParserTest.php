<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Banking\Parsers\Mt940BankStatementParser;

class Mt940ParserTest extends TestCase
{
    public function test_parse_example_file()
    {
        $parser = new Mt940BankStatementParser();
        $file = base_path('tools/mt940_examples/mt940_event_import_1.txt');
        if (!file_exists($file)) {
            $this->markTestSkipped('Missing MT940 example file: ' . $file);
        }

        $txs = $parser->parse($file);
        $this->assertIsIterable($txs);
        $this->assertGreaterThanOrEqual(1, count($txs));

        $first = $txs[0] ?? null;
        $this->assertNotNull($first);
        $this->assertNotEmpty($first->title);
    }
}
