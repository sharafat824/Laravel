<?php

namespace App\Jobs;

use App\Models\Exchange;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteOldExchangeRecordsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Delete records where updated_at is not today
        $startOfToday = Carbon::today();
        Exchange::where('updated_at', '<', $startOfToday)->delete();
    }
}
