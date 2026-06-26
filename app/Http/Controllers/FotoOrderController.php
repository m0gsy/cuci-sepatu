<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\FotoOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FotoOrderController extends Controller
{
    public function store(Request $request, Order $order)
    {
        if (auth()->user()->isCleaner()) {
            $request->merge(['tipe' => 'after']);
        }

        $request->validate([
            'tipe'       => 'required|in:before,during,after',
            'foto'       => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'keterangan' => 'nullable|string|max:100',
        ]);

        $path = $request->file('foto')->store("orders/{$order->id}", 'public');

        FotoOrder::create([
            'order_id'   => $order->id,
            'tipe'       => $request->tipe,
            'path'       => $path,
            'keterangan' => $request->keterangan,
        ]);

        return back()->with('success', 'Foto berhasil diupload.');
    }

    public function destroy(FotoOrder $foto)
    {
        abort_if(!auth()->user()->hasPermission('orders.manage'), 403);
        Storage::disk('public')->delete($foto->path);
        $foto->delete();
        return back()->with('success', 'Foto dihapus.');
    }
}
