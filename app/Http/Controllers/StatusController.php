<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class StatusController extends Controller
{
    public function show(string $token)
    {
        $order = Order::where('token_publik', $token)
            ->with(['items.layanan', 'pembayaran', 'review', 'lokasi'])
            ->firstOrFail();

        return view('status.show', compact('order'));
    }

    public function downloadInvoice(string $token)
    {
        $order = Order::where('token_publik', $token)
            ->with(['items.layanan', 'items.jenisBarang', 'pembayaran', 'user', 'voucher', 'pelanggan', 'lokasi'])
            ->firstOrFail();

        $pdf = Pdf::loadView('pdf.invoice', compact('order'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("invoice-{$order->no_order}.pdf");
    }
}
