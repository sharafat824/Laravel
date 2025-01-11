<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use App\Models\Exchange;
use Illuminate\Http\Request;
use App\Jobs\StoreExchangeDataJob;
use Illuminate\Support\Facades\Http;

class ExchangeController extends Controller
{  
    public function searchExchange(Request $request)
    {
        $type = $request->input('type');   // Optional filter for the 'type' column
        $query = $request->input('query'); // Search term for all columns
    
        // Validate the input
        $request->validate([
            'query' => 'nullable|string|max:255', // Query can be null for fetching all records
            'type' => 'nullable|string|max:255', // Type can be null
        ]);
    
        // Build the query
        $exchanges = Exchange::query();
    
        // Apply the type filter if provided
        if ($type !== 'all') {
            $exchanges->where('type', $type);
        }
    
        // Apply the search term across all columns if query is provided
        if (!empty($query)) {
            $exchanges->where(function ($q) use ($query) {
                $q->where('code', 'like', '%' . $query . '%')
               //   ->orWhere('name', 'like', '%' . $query . '%');
                //   ->orWhere('country', 'like', '%' . $query . '%')
                //   ->orWhere('exchange', 'like', '%' . $query . '%')
                //   ->orWhere('currency', 'like', '%' . $query . '%')
                //   ->orWhere('type', 'like', '%' . $query . '%')
                //   ->orWhere('isin', 'like', '%' . $query . '%');
            });
        }
    
        // Paginate the results (100 records per page)
        $results = $exchanges->paginate(25);
    
        // Return the paginated response
        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }
    
    
    public function fetchAndStore()
    {
        // First, delete all existing records in the Exchange table
        // Exchange::truncate();
        \Log::info('start');
        $url = 'https://eodhd.com/api/exchange-symbol-list/us?api_token=6481a43f1b06b8.89762580&fmt=json';
    
        try {
            $client = new Client();
            $buffer = '';
            $incompleteBuffer = ''; // To store any incomplete JSON data across chunks
    
            $response = $client->request('GET', $url, [
                'stream' => true,
                'timeout' => 300,
            ]);
    
            // Get the body as a stream
            $stream = $response->getBody();
    
            while (!$stream->eof()) {
                $bodyChunk = $stream->read(1024 * 1024);
                $buffer .= $bodyChunk;
    
                // Append any incomplete buffer from the previous chunk
                $buffer = $incompleteBuffer . $buffer;
    
                // Check if the buffer forms a complete JSON array
                while (preg_match('/^\[.*\]$/s', $buffer) && $this->isValidJson($buffer)) {
                    \Log::error('buffer ' . $buffer);
                    $data = json_decode($buffer, true);
                    if (is_array($data)) {
                        collect($data)->chunk(1000)->each(function ($chunk) {
                            StoreExchangeDataJob::dispatch($chunk)->delay(now()->addSeconds(5));
                        });
                    }else{
                        \Log::error('not array');
                    }
                    $buffer = ''; // Clear buffer after successful parsing
                }
    
                // If the buffer is not complete yet, store the last part for the next iteration
                $incompleteBuffer = $this->getIncompleteJson($buffer);
    
                // Clear the complete part of the buffer
                $buffer = '';
            }
    
            return response()->json(['message' => 'Data successfully saved!']);
        } catch (\Exception $e) {
            // Log and return error response
            \Log::error('Error fetching and storing data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch data'], 500);
        }
    }
  
    private function getIncompleteJson($buffer)
{
    $lastOpenBracket = strrpos($buffer, '[');
    $lastCloseBracket = strrpos($buffer, ']');

    if ($lastOpenBracket !== false && $lastCloseBracket === false) {
        // Incomplete JSON: opening bracket without closing one
        return substr($buffer, $lastOpenBracket);
    }

    if ($lastOpenBracket !== false && $lastCloseBracket !== false && $lastCloseBracket > $lastOpenBracket) {
        // Complete JSON: return empty as we have a complete JSON
        return '';
    }

    // If the last close bracket is found before the opening bracket, it's malformed
    return '';
}
    
    private function isValidJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
}
