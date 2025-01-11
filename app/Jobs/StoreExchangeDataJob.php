<?php

namespace App\Jobs;

use App\Models\Exchange;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StoreExchangeDataJob implements ShouldQueue
{
    use Queueable,Batchable,Dispatchable;

    /**
     * Create a new job instance.
     */
    public $exchanges;
    public function __construct($exchanges)
    {
        $this->exchanges = $exchanges;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
          foreach ($this->exchanges as $item) {
                    Exchange::updateOrCreate(
                        ['code' => $item['Code']],
                        [
                            'name' => $item['Name'] ?? null,
                            'country' => $item['Country'] ?? null,
                            'exchange' => $item['Exchange'] ?? null,
                            'currency' => $item['Currency'] ?? null,
                            'type' => $item['Type'] ?? null,
                            'isin' => $item['Isin'] ?? null,
                        ]
                    );
                }
    }
}
