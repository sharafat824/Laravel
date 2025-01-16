<?php

namespace App\Http\Controllers;

use App\Models\ExchangePrice;
use Illuminate\Http\Request;
use App\Jobs\InsertExchangePricesJob;
use App\Models\Exchange;
use Illuminate\Support\Carbon;
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

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {

        // Retrieve the exchange by the provided code
        $row = Exchange::where("code", $request->code)->first();
        if ($row) {
            // Check if the 'has_price' field is less than 24 hours old
            if ($row->has_price && Carbon::parse($row->has_price)->diffInHours(Carbon::now()) < 24) {
                // If 'has_price' is within 24 hours, fetch data from the database
                $data = ExchangePrice::where('exchange_id', $row->id)->get();
                return response()->json($data); // Return the data from the database
            } else {
                // If 'has_price' is older than 24 hours, fetch data from the live API
                    $apiKey = env('EODHD_API_KEY');
                    $url = "https://eodhd.com/api/eod/{$request->code}.{$request->country}";

                    // Make an HTTP GET request to the API
                        $response = Http::get($url, [
                        'api_token' => $apiKey,
                        'fmt' => "json",
                    ]);

                // Check if the request was successful
                    if ($response->successful()) {
                        $data = $response->json();
                        // Transform data into the format suitable for your database
                        $formattedData = array_map(function ($item ) use ($row) {
                        $item['exchange_id'] = $row->id;
                        return $item;
                        }, $data);

                        // Dispatch the job to insert data in chunks
                        InsertExchangePricesJob::dispatch($formattedData);
                        return $formattedData;
                    }

                    // Update the 'has_price' field with the current timestamp to indicate it was updated
                    $row->has_price = Carbon::now();
                    $row->save();
            }
    }

    return response()->json(['error' => 'Exchange not found'], 404);

       $row =  Exchange::where("code",$request->code)->first();
       if($row && $row->has_price < Carbon::now()){
         $data =  ExchangePrice::where('exchange_id',$row->id)->get();
          return $data;
       }
       else
       {
       
       }

    
    return $this->fetchAndSavePrices($row?$row->id:1,$request->code.'.'.$request->country);

    
    return response()->json(['error' => 'Failed to fetch data from API'], 500);

        return $request;
        
        // Dispatch the job
        InsertExchangePricesJob::dispatch($data);
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
