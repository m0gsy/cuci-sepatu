<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $token;

    public function __construct()
    {
        $this->token = config('services.fonnte.token', '');
    }

    public function kirim(string $nomorHp, string $pesan): bool
    {
        if (empty($this->token)) {
            Log::warning('WA: FONNTE_TOKEN belum diisi di .env');
            return false;
        }
        try {
            $nomor    = $this->formatNomor($nomorHp);
            $response = Http::withHeaders(['Authorization' => $this->token])
                ->post('https://api.fonnte.com/send', [
                    'target'  => $nomor,
                    'message' => $pesan,
                ]);
            if (!$response->successful()) {
                Log::warning('WA gagal', ['nomor' => $nomor, 'body' => $response->body()]);
                return false;
            }
            return true;
        } catch (\Exception $e) {
            Log::error('WA error: ' . $e->getMessage());
            return false;
        }
    }

    public function kirimInvoiceLink(Order $order): bool
    {
        $link  = route('orders.nota', $order);
        $pesan = $this->pesanInvoice($order) . "\n\nUnduh invoice PDF:\n" . $link;
        return $this->kirim($order->no_hp, $pesan);
    }

    public function pesanInvoice(Order $order): string
    {
        $total       = 'Rp ' . number_format($order->pembayaran?->total ?? 0, 0, ',', '.');
        $hargaSat    = 'Rp ' . number_format($order->harga_efektif, 0, ',', '.');
        $tglOrder    = $order->created_at->isoFormat('D MMMM Y');
        $metode      = ucfirst($order->pembayaran?->metode ?? 'tempo');
        $statusBayar = strtoupper($order->pembayaran?->status ?? 'belum');
        $iconStatus  = ($order->pembayaran?->status === 'lunas') ? 'LUNAS' : 'BELUM LUNAS';
        $lokasi      = $order->lokasi ? $order->lokasi->nama : '-';

        return "Invoice - " . $order->no_order . "\n\n"
            . "Kepada  : " . $order->nama_pelanggan . "\n"
            . "Tanggal : " . $tglOrder . "\n\n"
            . "Rincian Layanan:\n"
            . "Layanan  : " . $order->layanan->nama . "\n"
            . "Lokasi   : " . $lokasi . "\n"
            . "Jenis    : " . $order->jenis_sepatu . "\n"
            . "Jumlah   : " . $order->jumlah_pasang . " pasang\n\n"
            . "Harga/pasang : " . $hargaSat . "\n"
            . "Jumlah pasang: x " . $order->jumlah_pasang . "\n"
            . "Total        : " . $total . "\n\n"
            . "Metode bayar : " . $metode . "\n"
            . "Status bayar : " . $iconStatus . "\n\n"
            . "Terima kasih telah mempercayakan sepatu Anda kepada kami!\n"
            . "_Step Shine Works_";
    }

    public function pesanOrderMasuk(Order $order): string
    {
        $total    = 'Rp ' . number_format($order->pembayaran?->total ?? 0, 0, ',', '.');
        $estimasi = $order->estimasi_selesai?->isoFormat('D MMMM Y') ?? '—';
        $link     = route('status.order', $order->token_publik);
        $metode   = ucfirst($order->pembayaran?->metode ?? 'tempo');
        $lokasi   = $order->lokasi ? $order->lokasi->nama : null;

        return "Halo " . $order->nama_pelanggan . "!\n\n"
            . "Order sepatu Anda di *Step Shine Works* sudah kami terima.\n\n"
            . "Detail Order:\n"
            . "No. Order    : " . $order->no_order . "\n"
            . "Layanan      : " . $order->layanan->nama . "\n"
            . ($lokasi ? "Lokasi       : " . $lokasi . "\n" : '')
            . "Jumlah       : " . $order->jumlah_pasang . " pasang\n"
            . "Total        : " . $total . "\n"
            . "Metode bayar : " . $metode . "\n"
            . "Est. selesai : " . $estimasi . "\n\n"
            . "Pantau status order Anda di:\n"
            . $link . "\n\n"
            . "Terima kasih!\n"
            . "_Step Shine Works_";
    }

    public function pesanMulaiDicuci(Order $order): string
    {
        $link = route('status.order', $order->token_publik);
        $lokasi = $order->lokasi ? $order->lokasi->nama : null;

        return "Halo " . $order->nama_pelanggan . "!\n\n"
            . "Kabar terbaru dari *Step Shine Works*: sepatu Anda sudah mulai proses pencucian.\n\n"
            . "No. Order : " . $order->no_order . "\n"
            . "Layanan   : " . $order->layanan->nama . "\n"
            . ($lokasi ? "Lokasi    : " . $lokasi . "\n" : '')
            . "\nPantau status: " . $link . "\n\n"
            . "_Step Shine Works_";
    }

    public function pesanOrderSelesai(Order $order): string
    {
        $total       = 'Rp ' . number_format($order->pembayaran?->total ?? 0, 0, ',', '.');
        $link        = route('status.order', $order->token_publik);
        $poin        = $order->poin;
        $statusBayar = strtoupper($order->pembayaran?->status ?? 'belum');

        $lokasi = $order->lokasi ? $order->lokasi->nama : null;

        $msg = "Halo " . $order->nama_pelanggan . "!\n\n"
            . "Sepatu Anda sudah selesai dicuci dan siap diambil!\n\n"
            . "Detail Order:\n"
            . "No. Order    : " . $order->no_order . "\n"
            . "Layanan      : " . $order->layanan->nama . "\n"
            . ($lokasi ? "Lokasi       : " . $lokasi . "\n" : '')
            . "Total        : " . $total . "\n"
            . "Status bayar : " . $statusBayar . "\n";

        if ($poin > 0) {
            $msg .= "Poin earned  : +" . $poin . " poin\n";
        }

        $msg .= "\nPantau status: " . $link . "\n\n"
            . "Silakan ambil di toko kami.\n"
            . "Terima kasih sudah mempercayakan sepatu Anda kepada kami!\n"
            . "_Step Shine Works_";

        return $msg;
    }

    protected function formatNomor(string $nomor): string
    {
        $nomor = preg_replace('/[\s\-\+]/', '', $nomor);
        if (str_starts_with($nomor, '0')) {
            $nomor = '62' . substr($nomor, 1);
        }
        return $nomor;
    }
}
