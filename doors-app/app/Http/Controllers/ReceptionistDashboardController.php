<?php

namespace App\Http\Controllers;

use App\Models\PantryOrder;
use Illuminate\Http\Request;

class ReceptionistDashboardController extends Controller
{
    public function index()
    {
        $pendingPantryOrders = PantryOrder::with(['meeting', 'pantryItem'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('dashboards.receptionist', compact('pendingPantryOrders'));
    }

    public function update(Request $request, PantryOrder $pantryOrder)
    {
        $request->validate([
            'status' => 'required|in:preparing,completed',
        ]);

        $pantryOrder->status = $request->status;
        $pantryOrder->save();

        return back()->with('success', 'Pantry order status updated successfully.');
    }
}
