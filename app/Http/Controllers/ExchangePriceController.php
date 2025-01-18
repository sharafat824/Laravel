<?php

namespace App\Http\Controllers;

use App\Models\ExchangePrice;
use Illuminate\Http\Request;
use App\Jobs\InsertExchangePricesJob;
use App\Models\Exchange;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
class ExchangePriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function fetchAndSavePrices($exchangeId,$symbolCode)
    {
        // Replace with your API key
        $apiKey = env('EOHDH_API_KEY');
         $url = 'https://eodhd.com/api/eod/'.$symbolCode;

        // Make an HTTP GET request to the API
         $response = Http::get($url, [
            'api_token' => $apiKey,
            'fmt' => "json",
        ]);

        // Check if the request was successful
        if ($response->successful()) {
            $data = $response->json();
            // Transform data into the format suitable for your database
            $formattedData = array_map(function ($item ) use ($exchangeId) {
            $item['exchange_id'] = $exchangeId;
            return $item;
            }, $data);

            // Dispatch the job to insert data in chunks
            InsertExchangePricesJob::dispatch($formattedData);
            return $formattedData;
        }
        // Handle errors
        return [];
    }


    function getStockIndex($stockPrices=[]) {


         // If 'has_price' is older than 24 hours, fetch data from the live API
         $apiKey = env('EODHD_API_KEY');
         $url = "https://eodhd.com/api/eod/GSPC.INDX";

         // Make an HTTP GET request to the API
        $response = Http::get($url, [
             'api_token' => $apiKey,
             'fmt' => "json",
             'from'=>Carbon::today()->subDays(100)->format('Y-m-d'),
             'to'=>Carbon::today()->subDays(0)->format('Y-m-d')
         ]);


         // Check if the request was successful
         if ($response->successful()) {
            $benchmarkIndexex = $response->json();
            $result = [];
            foreach ($stockPrices as $stockPrice) {
                // Search for the matching date in benchmarkIndexex
                foreach ($benchmarkIndexex as $benchmarkIndex) {
                    if ($stockPrice['date'] === $benchmarkIndex['date']) {
                        // Calculate the division of close values
                        $division = ($stockPrice['close'] / $benchmarkIndex['close'])  * ((int)env('RANDOM_VAL',1));
            
                        // Push the result to the new array
                        $result[] = [
                            Carbon::parse($stockPrice['date'])->timestamp,  // Convert date to timestamp
                            $division                                      // The divided value
                        ];
                    }
                }
            }

            return $result;
        }

        
    }
    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {

        $request->validate([
            'code' => 'required|string',
            'country' => 'required|string',
        ]);

        // Retrieve the exchange by the provided code
        $row = Exchange::where("code", $request->code)->first();

        if ($row) {
            // Check if the 'has_price' field is less than 24 hours old
            // if ($row->price_status == 'done' && Carbon::parse($row->price_created_at)->diffInHours(Carbon::now()) < 24) {
            //     // If 'has_price' is within 24 hours, fetch data from the database
            //     $data = ExchangePrice::where('exchange_id', $row->id)->get();
            //     return response()->json($data); // Return the data from the database
            // } else {
                // If 'has_price' is older than 24 hours, fetch data from the live API
                $apiKey = env('EODHD_API_KEY');
                $url = "https://eodhd.com/api/eod/{$request->code}.{$request->country}";

                // Make an HTTP GET request to the API
                $response = Http::get($url, [
                    'api_token' => $apiKey,
                    'fmt' => "json",
                    'from'=>Carbon::today()->subDays(100)->format('Y-m-d'),
                    'to'=>Carbon::today()->subDays(0)->format('Y-m-d')
                ]);

                // Check if the request was successful
                if ($response->successful()) {
                    $data = $response->json();
                    // Transform data into the format suitable for your database
                    $formattedData = array_map(function ($item) {
                        return [
                            strtotime($item['date']), // Convert date to timestamp
                            $item['open'],
                            $item['high'],
                            $item['low'],
                            $item['close'],
                            $item['volume']
                        ];
                    }, $data);

                    // Dispatch the job to insert data in chunks
                    // InsertExchangePricesJob::dispatch($formattedData, $row->id);


                    // Update the 'has_price' field with the current timestamp to indicate it was updated
                    // $row->price_status = "processing";
                    // $row->price_created_at = Carbon::now();
                    // $row->save();


                    $index = $this->getStockIndex($data);
                    return ["stocks"=> $formattedData,"stock_count"=> count(($formattedData)) , "index"=> $index ,'index_count'=>count($index)];

                }

                return $response->json();

            // }
        }

        return response()->json(['error' => 'Exchange not found'], 404);

//        $row = Exchange::where("code", $request->code)->first();
//        if ($row && $row->has_price < Carbon::now()) {
//            $data = ExchangePrice::where('exchange_id', $row->id)->get();
//            return $data;
//        } else {
//
//        }
//
//
//        return $this->fetchAndSavePrices($row ? $row->id : 1, $request->code . '.' . $request->country);
//
//
//        return response()->json(['error' => 'Failed to fetch data from API'], 500);
//
//        return $request;
//
//        // Dispatch the job
//        InsertExchangePricesJob::dispatch($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExchangePrice $exchangePrice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExchangePrice $exchangePrice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExchangePrice $exchangePrice)
    {
        //
    }
}
