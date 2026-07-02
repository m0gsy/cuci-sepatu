<?php

namespace App\Http\Controllers;

use App\Models\Order;

class StatusController extends Controller
{
    public function show(string $token)
    {
        $order = Order::where('token_publik', $token)
            ->with(['layanan', 'pembayaran', 'review', 'lokasi'])
            ->firstOrFail();

        return view('status.show', compact('order'));
    }
}
