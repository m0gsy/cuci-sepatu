<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawans = User::withCount([
                'orders as order_bulan_ini' => fn($q) =>
                    $q->whereYear('orders.created_at', now()->year)
                      ->whereMonth('orders.created_at', now()->month),
                'orders as order_hari_ini' => fn($q) =>
                    $q->whereDate('orders.created_at', today()),
            ])
            ->orderBy('aktif', 'desc')
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $allPermissions    = RolePermission::allPermissions();
        $adminPermissions  = RolePermission::forRole('admin');
        $cleanerPermissions = RolePermission::forRole('cleaner');

        return view('karyawans.index', compact('karyawans', 'allPermissions', 'adminPermissions', 'cleanerPermissions'));
    }

    public function show(User $karyawan)
    {
        $orders = $karyawan->orders()
            ->with(['layanan', 'pembayaran'])
            ->latest()->paginate(10);

        $stats = [
            'total_order'     => $karyawan->orders()->count(),
            'order_bulan_ini' => $karyawan->orders()
                ->whereYear('orders.created_at', now()->year)
                ->whereMonth('orders.created_at', now()->month)->count(),
            'order_hari_ini'  => $karyawan->orders()
                ->whereDate('orders.created_at', today())->count(),
            'total_diproses'  => $karyawan->orders()
                ->join('pembayarans', 'orders.id', '=', 'pembayarans.order_id')
                ->where('pembayarans.status', 'lunas')->sum('pembayarans.total'),
        ];

        $rekapBulanan = $karyawan->orders()
            ->select(
                DB::raw('YEAR(orders.created_at) as tahun'),
                DB::raw('MONTH(orders.created_at) as bulan'),
                DB::raw('COUNT(*) as jumlah')
            )
            ->where('orders.created_at', '>=', now()->subMonths(6))
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun')->orderBy('bulan')->get();

        return view('karyawans.show', compact('karyawan', 'orders', 'stats', 'rekapBulanan'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:owner,admin,cleaner',
            'no_hp'    => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9]{8,15}$/'],
            'alamat'   => 'nullable|string|max:200',
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'no_hp'    => $data['no_hp'] ?? null,
            'alamat'   => $data['alamat'] ?? null,
            'aktif'    => true,
        ]);

        return back()->with('success', "Akun {$data['name']} berhasil dibuat.");
    }

    public function update(Request $request, User $karyawan)
    {
        if ($karyawan->id === auth()->id()) {
            return back()->with('error', 'Gunakan halaman profil untuk edit data sendiri.');
        }

        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'email'  => 'required|email|unique:users,email,' . $karyawan->id,
            'role'   => 'required|in:owner,admin,cleaner',
            'no_hp'  => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9]{8,15}$/'],
            'alamat' => 'nullable|string|max:200',
        ]);

        $karyawan->update($data);
        return back()->with('success', "Data {$karyawan->name} berhasil diperbarui.");
    }

    public function resetPassword(Request $request, User $karyawan)
    {
        $data = $request->validate(['password' => 'required|string|min:8|confirmed']);
        $karyawan->update(['password' => Hash::make($data['password'])]);
        return back()->with('success', "Password {$karyawan->name} berhasil direset.");
    }

    public function savePermissions(Request $request)
    {
        $allKeys = collect(RolePermission::allPermissions())->pluck('key')->toArray();
        $roles   = ['admin', 'cleaner'];

        DB::transaction(function () use ($roles, $allKeys, $request) {
            foreach ($roles as $role) {
                $granted = array_intersect(
                    $request->input("permissions.{$role}", []),
                    $allKeys
                );
                RolePermission::where('role', $role)->delete();
                foreach ($granted as $key) {
                    RolePermission::create(['role' => $role, 'permission' => $key]);
                }
            }
        });

        foreach ($roles as $role) {
            RolePermission::bustCache($role);
        }

        return back()->with('success', 'Hak akses berhasil disimpan.');
    }

    public function toggleAktif(User $karyawan)
    {
        if ($karyawan->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menonaktifkan akun sendiri.');
        }

        if ($karyawan->isOwner() && $karyawan->aktif) {
            $adminAktif = User::where('role', 'owner')->where('aktif', true)->count();
            if ($adminAktif <= 1) {
                return back()->with('error', 'Harus ada minimal 1 owner aktif.');
            }
        }

        $karyawan->update(['aktif' => !$karyawan->aktif]);
        $status = $karyawan->aktif ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "{$karyawan->name} berhasil {$status}.");
    }
}
