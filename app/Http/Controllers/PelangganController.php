<?php

namespace App\Http\Controllers;

use App\Http\Middleware\NormalizePhoneNumber;
use App\Models\OrderItem;
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
        $normalized = NormalizePhoneNumber::normalize($q);

        return response()->json(
            Pelanggan::where(function ($query) use ($q, $normalized) {
                $query->where('nama', 'like', "%{$q}%")
                    ->orWhere('no_hp', 'like', "%{$q}%");
                if ($normalized) {
                    $query->orWhere('no_hp', 'like', "%{$normalized}%");
                }
            })
                ->orderBy('nama')
                ->limit(8)
                ->get(['id', 'nama', 'no_hp', 'poin'])
        );
    }

    /**
     * GET /pelanggans/poin?no_hp=xxx
     * Returns poin balance for a customer by phone number (normalized).
     * Used by the order create form's point redemption UI.
     */
    public function getPoinByPhone(Request $request)
    {
        $noHp = NormalizePhoneNumber::normalize(
            trim($request->input('no_hp', ''))
        );

        if (! $noHp) {
            return response()->json(['poin' => 0, 'nilai_rupiah' => 0, 'found' => false]);
        }

        $pelanggan = Pelanggan::where('no_hp', $noHp)->first();

        if (! $pelanggan) {
            return response()->json(['poin' => 0, 'nilai_rupiah' => 0, 'found' => false]);
        }

        return response()->json([
            'found' => true,
            'nama' => $pelanggan->nama,
            'poin' => $pelanggan->poin,
            'nilai_rupiah' => $pelanggan->nilaiPoin(),
        ]);
    }

    public function index(Request $request)
    {
        $pelanggans = Pelanggan::withCount('orders')
            ->withSum(['orders as total_belanja' => fn ($q) => $q->join('pembayarans', 'orders.id', '=', 'pembayarans.order_id')
                ->where('pembayarans.status', 'selesai'),
            ], 'pembayarans.total')
            ->with(['latestOrder.items.layanan', 'latestOrder.layanan'])
            ->when($request->cari, function ($q) use ($request) {
                $cari = $request->cari;
                $normalized = NormalizePhoneNumber::normalize($cari);
                $q->where(function ($q) use ($cari, $normalized) {
                    $q->where('nama', 'like', "%{$cari}%")
                        ->orWhere('no_hp', 'like', "%{$cari}%");
                    if ($normalized) {
                        $q->orWhere('no_hp', 'like', "%{$normalized}%");
                    }
                });
            })
            ->orderByDesc('orders_count')
            ->paginate(15)->withQueryString();

        return view('pelanggans.index', compact('pelanggans'));
    }

    public function show(Pelanggan $pelanggan)
    {
        $orders = $pelanggan->orders()
            ->with(['items.layanan', 'pembayaran', 'lokasi'])
            ->latest()->paginate(10);

        $stats = [
            'total_order' => $pelanggan->orders()->count(),
            'total_belanja' => $pelanggan->orders()
                ->join('pembayarans', 'orders.id', '=', 'pembayarans.order_id')
                ->where('pembayarans.status', 'selesai')->sum('pembayarans.total'),
            'total_pasang' => OrderItem::query()
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.pelanggan_id', $pelanggan->id)
                ->where('orders.status', '!=', 'batal')
                ->sum('order_items.jumlah_pasang'),
            'layanan_favorit' => $pelanggan->layananFavorit(),
        ];

        // Rekap layanan dari order_items (mendukung multi-item order)
        $rekapLayanan = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.pelanggan_id', $pelanggan->id)
            ->where('orders.status', '!=', 'batal')
            ->select(
                'order_items.layanan_id',
                DB::raw('COUNT(DISTINCT order_items.order_id) as jumlah'),
                DB::raw('SUM(order_items.jumlah_pasang) as total_pasang')
            )
            ->with('layanan')
            ->groupBy('order_items.layanan_id')
            ->orderByDesc('jumlah')
            ->get();

        return view('pelanggans.show', compact('pelanggan', 'orders', 'stats', 'rekapLayanan'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'no_hp' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{8,15}$/', 'unique:pelanggans,no_hp'],
            'alamat' => 'nullable|string|max:200',
            'catatan' => 'nullable|string|max:500',
        ]);
        Pelanggan::create($data);

        return back()->with('success', "Pelanggan {$data['nama']} berhasil ditambahkan.");
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'no_hp' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{8,15}$/', 'unique:pelanggans,no_hp,'.$pelanggan->id],
            'alamat' => 'nullable|string|max:200',
            'catatan' => 'nullable|string|max:500',
        ]);
        $pelanggan->update($data);

        return back()->with('success', 'Data pelanggan berhasil diperbarui.');
    }
}
