<?php

namespace App\Services;

use App\Models\Order;
use App\Models\WaTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $token;

    public function __construct()
    {
        $this->token = config('services.wablas.token', '');
    }

    public function kirim(string $nomorHp, string $pesan): bool
    {
        if (empty($this->token)) {
            Log::warning('WA: WABLAS_TOKEN belum diisi di .env');
            return false;
        }
        try {
            $nomor   = $this->formatNomor($nomorHp);
            $baseUrl = rtrim(config('services.wablas.url', 'https://smg.wablas.com'), '/');
            $secret  = config('services.wablas.secret');
            $payload = ['phone' => $nomor, 'message' => $pesan];
            if ($secret) {
                $payload['secret'] = $secret;
            }
            $response = Http::timeout(15)->withHeaders(['Authorization' => $this->token])
                ->post("{$baseUrl}/api/send-message", $payload);
            if (!$response->successful()) {
                Log::warning('WA gagal', ['nomor' => $nomor, 'body' => $response->body()]);
                return false;
            }
            $body = $response->json();
            if (isset($body['status']) && $body['status'] === false) {
                Log::warning('WA gagal', ['nomor' => $nomor, 'body' => $body]);
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
        // Kirim link status publik — bukan URL staff-only (orders.nota)
        $link  = route('status.order', $order->token_publik);
        $pesan = $this->pesanInvoice($order) . "\n\nCek status order:\n" . $link;
        return $this->kirim($order->no_hp, $pesan);
    }

    public function pesanOrderMasuk(Order $order): string
    {
        return $this->render('order_masuk', [
            'nama_pelanggan'   => $order->nama_pelanggan,
            'no_order'         => $order->no_order,
            'layanan'          => $order->layanan->nama,
            'lokasi'           => $order->lokasi?->nama ?? '',
            'jumlah_pasang'    => $order->jumlah_pasang,
            'total'            => 'Rp ' . number_format($order->pembayaran?->total ?? 0, 0, ',', '.'),
            'metode_bayar'     => ucfirst($order->pembayaran?->metode ?? 'tempo'),
            'estimasi_selesai' => $order->estimasi_selesai?->isoFormat('D MMMM Y') ?? '—',
            'link_status'      => route('status.order', $order->token_publik),
        ]);
    }

    public function pesanMulaiDicuci(Order $order): string
    {
        return $this->render('mulai_dicuci', [
            'nama_pelanggan' => $order->nama_pelanggan,
            'no_order'       => $order->no_order,
            'layanan'        => $order->layanan->nama,
            'lokasi'         => $order->lokasi?->nama ?? '',
            'link_status'    => route('status.order', $order->token_publik),
        ]);
    }

    public function pesanOrderSelesai(Order $order): string
    {
        $poin = $order->poin;
        return $this->render('order_selesai', [
            'nama_pelanggan' => $order->nama_pelanggan,
            'no_order'       => $order->no_order,
            'layanan'        => $order->layanan->nama,
            'lokasi'         => $order->lokasi?->nama ?? '',
            'total'          => 'Rp ' . number_format($order->pembayaran?->total ?? 0, 0, ',', '.'),
            'status_bayar'   => strtoupper($order->pembayaran?->status ?? 'belum'),
            'poin'           => $poin > 0 ? '+' . $poin . ' poin' : '',
            'link_status'    => route('status.order', $order->token_publik),
        ]);
    }

    public function pesanInvoice(Order $order): string
    {
        return $this->render('invoice', [
            'nama_pelanggan' => $order->nama_pelanggan,
            'no_order'       => $order->no_order,
            'tanggal'        => $order->created_at->isoFormat('D MMMM Y'),
            'layanan'        => $order->layanan->nama,
            'lokasi'         => $order->lokasi?->nama ?? '-',
            'jenis_sepatu'   => $order->jenis_sepatu,
            'jumlah_pasang'  => $order->jumlah_pasang,
            'harga_satuan'   => 'Rp ' . number_format($order->harga_efektif, 0, ',', '.'),
            'total'          => 'Rp ' . number_format($order->pembayaran?->total ?? 0, 0, ',', '.'),
            'metode_bayar'   => ucfirst($order->pembayaran?->metode ?? 'tempo'),
            'status_bayar'   => ($order->pembayaran?->status === 'lunas') ? 'LUNAS' : 'BELUM LUNAS',
        ]);
    }

    private function render(string $kode, array $vars): string
    {
        $tmpl = WaTemplate::where('kode', $kode)->value('template') ?? '';

        // Fallback ke hardcoded jika tabel kosong (belum di-seed)
        if (empty($tmpl)) {
            $tmpl = $this->defaultTemplate($kode);
        }

        foreach ($vars as $k => $v) {
            $tmpl = str_replace("{{$k}}", (string) ($v ?? ''), $tmpl);
        }

        // Hapus baris yang nilainya kosong setelah substitusi (misal "Lokasi : ")
        $lines = explode("\n", $tmpl);
        $lines = array_filter($lines, fn($line) => !preg_match('/\w\s*:\s*$/', trim($line)));

        return trim(preg_replace('/\n{3,}/', "\n\n", implode("\n", $lines)));
    }

    private function defaultTemplate(string $kode): string
    {
        return match($kode) {
            'order_masuk'   => "Halo {nama_pelanggan}!\n\nOrder sepatu Anda di *Step Shine Works* sudah kami terima.\n\nDetail Order:\nNo. Order    : {no_order}\nLayanan      : {layanan}\nLokasi       : {lokasi}\nJumlah       : {jumlah_pasang} pasang\nTotal        : {total}\nMetode bayar : {metode_bayar}\nEst. selesai : {estimasi_selesai}\n\nPantau status order Anda di:\n{link_status}\n\nTerima kasih!\n_Step Shine Works_",
            'mulai_dicuci'  => "Halo {nama_pelanggan}!\n\nSepatu Anda sudah mulai proses pencucian.\n\nNo. Order : {no_order}\nLayanan   : {layanan}\nLokasi    : {lokasi}\n\nPantau status: {link_status}\n\n_Step Shine Works_",
            'order_selesai' => "Halo {nama_pelanggan}!\n\nSepatu Anda siap diambil!\n\nNo. Order    : {no_order}\nLayanan      : {layanan}\nLokasi       : {lokasi}\nTotal        : {total}\nStatus bayar : {status_bayar}\nPoin earned  : {poin}\n\nPantau status: {link_status}\n\n_Step Shine Works_",
            'invoice'       => "Invoice - {no_order}\n\nKepada  : {nama_pelanggan}\nTanggal : {tanggal}\n\nLayanan  : {layanan}\nLokasi   : {lokasi}\nJenis    : {jenis_sepatu}\nJumlah   : {jumlah_pasang} pasang\n\nHarga/pasang : {harga_satuan}\nTotal        : {total}\n\nMetode bayar : {metode_bayar}\nStatus bayar : {status_bayar}\n\nTerima kasih!\n_Step Shine Works_",
            default         => '',
        };
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
