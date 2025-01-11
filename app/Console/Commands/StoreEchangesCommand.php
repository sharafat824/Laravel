<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use App\Models\Exchange;
use Illuminate\Console\Command;
use App\Jobs\StoreExchangeDataJob;
use App\Jobs\DeleteOldExchangeRecordsJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;

class StoreEchangesCommand extends Command
{
    protected $signature = 'app:store-echanges-command';
    protected $description = 'Store exchanges command';

    public function handle()
    {
        $this->info('Start processing exchange data.');
       // Exchange::truncate();

        $countries = ['us', 'hk']; // Define the countries to process
        $client = new Client();

        try {
            foreach ($countries as $country) {
                $this->processCountryData($client, $country);
            }

            $this->info('All jobs have been dispatched successfully.');
        } catch (\Exception $e) {
            Log::error('Error in command: ' . $e->getMessage());
            $this->error('Error in command: ' . $e->getMessage());
        }
    }

    private function processCountryData(Client $client, string $country)
    {
        $this->info("Processing data for {$country}.");
        $url = "https://eodhd.com/api/exchange-symbol-list/{$country}?api_token=6481a43f1b06b8.89762580&fmt=json";

        $buffer = '';
        $incompleteBuffer = '';

        try {
            $response = $client->request('GET', $url, [
                'stream' => true,
                'timeout' => 600,
            ]);

            $stream = $response->getBody();

            while (!$stream->eof()) {
                $bodyChunk = $stream->read(1024 * 1024);
                $buffer .= $incompleteBuffer . $bodyChunk;

                while (preg_match('/^\[.*\]$/s', $buffer) && $this->isValidJson($buffer)) {
                    $data = json_decode($buffer, true);

                    if (is_array($data)) {

                        Bus::batch(collect($data)->chunk(1000)->map(function ($chunk) {
                            return new StoreExchangeDataJob($chunk);
                        }))->then(function () {
                            Log::info('All jobs completed successfully.');
                            DeleteOldExchangeRecordsJob::dispatch();
                        })->catch(function (\Throwable $e) {
                            Log::error('Batch processing failed: ' . $e->getMessage());
                        })->finally(function () {
                            Log::info('Batch has finished processing.');
                        })->dispatch();
                        
                        
                    } else {
                        Log::error("Invalid data structure for {$country}.");
                    }

                    $buffer = '';
                }

                $incompleteBuffer = $this->getIncompleteJson($buffer);
                $buffer = '';
            }

            $this->info("Finished processing {$country} data.");
        } catch (\Exception $e) {
            Log::error("Error processing {$country}: " . $e->getMessage());
        }
    }

    private function getIncompleteJson($buffer)
    {
        $lastOpenBracket = strrpos($buffer, '[');
        $lastCloseBracket = strrpos($buffer, ']');

        if ($lastOpenBracket !== false && $lastCloseBracket === false) {
            return substr($buffer, $lastOpenBracket);
        }

        if ($lastOpenBracket !== false && $lastCloseBracket !== false && $lastCloseBracket > $lastOpenBracket) {
            return '';
        }

        return '';
    }

    private function isValidJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
