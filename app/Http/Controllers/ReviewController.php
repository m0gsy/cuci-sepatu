<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // Admin: daftar semua review
    public function index()
    {
        $reviews = Review::with(['order.layanan', 'pelanggan'])
            ->latest()->paginate(20);
        return view('reviews.index', compact('reviews'));
    }

    // Publik: customer submit review via token
    public function store(Request $request, Order $order)
    {
        if ($order->review) {
            return back()->with('error', 'Review sudah pernah diberikan.');
        }

        if (!$order->isSudahSelesai()) {
            return back()->with('error', 'Order belum selesai.');
        }

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'nullable|string|max:500',
        ]);

        Review::create([
            'order_id'     => $order->id,
            'pelanggan_id' => $order->pelanggan_id,
            'rating'       => $data['rating'],
            'ulasan'       => $data['ulasan'] ?? null,
        ]);

        return back()->with('success', 'Terima kasih atas ulasannya!');
    }
}
