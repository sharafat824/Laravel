<?php

namespace App\Http\Controllers;

use App\Models\Exchange;
use Illuminate\Http\Request;

class ExchangeController extends Controller
{
    /**
     * Get the list of exchanges.
     */
    public function index()
    {
        // Fetch all exchanges
        $exchanges = Exchange::all();

        // Return as JSON
        return response()->json([
            'success' => true,
            'data' => $exchanges,
        ], 200);
    }

    /**
     * Create a new exchange entry.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'exchange' => 'required|string|max:255',
            'currency' => 'required|string|max:10',
            'type' => 'required|string|max:255',
            'isin' => 'required|string|max:255',
        ]);

        // Create the exchange record
        $exchange = Exchange::create($validatedData);

        // Return the created record as JSON
        return response()->json([
            'success' => true,
            'message' => 'Exchange created successfully',
            'data' => $exchange,
        ], 201);
    }
}
