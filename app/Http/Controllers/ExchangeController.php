<?php

namespace App\Http\Controllers;

use App\Models\Exchange;
use Illuminate\Http\Request;
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
                  ->orWhere('name', 'like', '%' . $query . '%')
                  ->orWhere('country', 'like', '%' . $query . '%')
                  ->orWhere('exchange', 'like', '%' . $query . '%')
                  ->orWhere('currency', 'like', '%' . $query . '%')
                  ->orWhere('type', 'like', '%' . $query . '%')
                  ->orWhere('isin', 'like', '%' . $query . '%');
            });
        }
    
        // Paginate the results (100 records per page)
        $results = $exchanges->paginate(100);
    
        // Return the paginated response
        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }
    
    
    public function fetchAndStore()
    {
        $url = 'https://eodhd.com/api/exchange-symbol-list/hk?api_token=6481a43f1b06b8.89762580&fmt=json';

        $response = Http::retry(3, 1000)->timeout(120)->get($url);        if ($response->successful()) {
            $data = $response->json();

            foreach ($data as $item) {
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

            return response()->json(['message' => 'Data successfully saved!']);
        } else {
            return response()->json(['error' => 'Failed to fetch data'], 500);
        }
    }
}
