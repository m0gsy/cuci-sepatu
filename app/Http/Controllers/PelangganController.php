<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelangganController extends Controller
{
    public function cari(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }
        return response()->json(
            Pelanggan::where('nama', 'like', "%{$q}%")
                ->orWhere('no_hp', 'like', "%{$q}%")
                ->orderBy('nama')
                ->limit(8)
                ->get(['nama', 'no_hp'])
        );
    }

    public function index(Request $request)
    {
        $pelanggans = Pelanggan::withCount('orders')
            ->withSum(['orders as total_belanja' => fn($q) =>
                $q->join('pembayarans', 'orders.id', '=', 'pembayarans.order_id')
                  ->where('pembayarans.status', 'lunas')
            ], 'pembayarans.total')
            ->with(['latestOrder.layanan'])
            ->when($request->cari, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->cari}%")
                  ->orWhere('no_hp', 'like', "%{$request->cari}%");
            }))
            ->orderByDesc('orders_count')
            ->paginate(15)->withQueryString();

        return view('pelanggans.index', compact('pelanggans'));
    }

    public function show(Pelanggan $pelanggan)
    {
        $orders = $pelanggan->orders()
            ->with(['layanan', 'pembayaran', 'lokasi'])
            ->latest()->paginate(10);

        $stats = [
            'total_order'     => $pelanggan->orders()->count(),
            'total_belanja'   => $pelanggan->orders()
                ->join('pembayarans', 'orders.id', '=', 'pembayarans.order_id')
                ->where('pembayarans.status', 'lunas')->sum('pembayarans.total'),
            'total_pasang'    => $pelanggan->orders()->sum('jumlah_pasang'),
            'layanan_favorit' => $pelanggan->layananFavorit(),
        ];

        $rekapLayanan = $pelanggan->orders()
            ->select('layanan_id', DB::raw('count(*) as jumlah'), DB::raw('sum(jumlah_pasang) as total_pasang'))
            ->with('layanan')->groupBy('layanan_id')->orderByDesc('jumlah')->get();

        return view('pelanggans.show', compact('pelanggan', 'orders', 'stats', 'rekapLayanan'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'    => 'required|string|max:100',
            'no_hp'   => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{8,15}$/', 'unique:pelanggans,no_hp'],
            'alamat'  => 'nullable|string|max:200',
            'catatan' => 'nullable|string|max:500',
        ]);
        Pelanggan::create($data);
        return back()->with('success', "Pelanggan {$data['nama']} berhasil ditambahkan.");
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $data = $request->validate([
            'nama'    => 'required|string|max:100',
            'no_hp'   => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{8,15}$/', 'unique:pelanggans,no_hp,' . $pelanggan->id],
            'alamat'  => 'nullable|string|max:200',
            'catatan' => 'nullable|string|max:500',
        ]);
        $pelanggan->update($data);
        return back()->with('success', "Data pelanggan berhasil diperbarui.");
    }
}
