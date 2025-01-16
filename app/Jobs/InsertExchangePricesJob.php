<?php

namespace App\Jobs;

use App\Models\ExchangePrice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InsertExchangePricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The data to be inserted.
     *
     * @var array
     */
    protected $data;

    /**
     * Create a new job instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Chunk the data into chunks of 200 records
        $chunks = array_chunk($this->data, 200);

        foreach ($chunks as $chunk) {
            // Insert the chunk into the database
            ExchangePrice::insert($chunk);
        }
    }
}
