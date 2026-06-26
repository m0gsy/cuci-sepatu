<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\WhatsappService;
use Illuminate\Console\Command;

class KirimWaTerlambat extends Command
{
    protected $signature   = 'wa:terlambat';
    protected $description = 'Kirim notifikasi WA ke customer yang ordernya lewat estimasi';

    public function handle(WhatsappService $wa): int
    {
        $aktifStatuses = ['diterima', 'inspeksi', 'dicuci', 'kering', 'finishing'];

        $orders = Order::with(['layanan', 'pembayaran', 'lokasi'])
            ->whereIn('status', $aktifStatuses)
            ->where('estimasi_selesai', '<', today())
            ->get();

        if ($orders->isEmpty()) {
            $this->info('Tidak ada order terlambat.');
            return 0;
        }

        $terkirim = 0;
        foreach ($orders as $order) {
            $pesan = $this->pesanTerlambat($order);
            if ($wa->kirim($order->no_hp, $pesan)) {
                $terkirim++;
            }
        }

        $this->info("Notifikasi terkirim: {$terkirim} dari {$orders->count()} order terlambat.");
        return 0;
    }

    private function pesanTerlambat(Order $order): string
    {
        $link     = route('status.order', $order->token_publik);
        $estimasi = $order->estimasi_selesai->isoFormat('D MMMM Y');

        return "Halo {$order->nama_pelanggan}!\n\n"
            . "Mohon maaf, sepatu Anda mengalami sedikit keterlambatan dari estimasi yang kami berikan.\n\n"
            . "No. Order    : {$order->no_order}\n"
            . "Layanan      : {$order->layanan->nama}\n"
            . "Est. selesai : {$estimasi}\n\n"
            . "Kami sedang berupaya menyelesaikannya secepat mungkin. "
            . "Pantau status terkini di:\n{$link}\n\n"
            . "Terima kasih atas pengertian Anda.\n"
            . "_Step Shine Works_";
    }
}
