<?php

namespace App\Http\Controllers;

use App\Models\BusinessConfig;
use Illuminate\Http\Request;

class ConfigSettingsController extends Controller
{
    public function invoiceConfigSettings()
    {
        try {
            $config = BusinessConfig::where('ConfigID','InvoicePrint')->first();
            return response()->json(['data' => $config], 200);
        } 
        catch (\Exception $e) {
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }

    public function updateConfigOrder(Request $request)
    {
        $order = $request->order;

        foreach ($order as $index => $itemId) {
            BusinessConfig::where('ConfigSL', $itemId)->update(['ParticularOrder' => $index + 1]);
        }

        return response()->json(['message' => 'Order updated successfully']);
    }
}
