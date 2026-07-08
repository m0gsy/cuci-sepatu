<?php

namespace App\Services;

use App\Models\Order;
use App\Models\WaTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public function kirim(string $nomorHp, string $pesan): bool
    {
        $nomor = $this->formatNomor($nomorHp);

        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from  = config('services.twilio.from');

        if (empty($sid) || empty($token) || empty($from)) {
            Log::warning('WA: Konfigurasi Twilio belum lengkap di .env');
            return false;
        }

        try {
            $response = Http::timeout(15)
                ->withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To'   => "whatsapp:+{$nomor}",
                    'From' => "whatsapp:{$from}",
                    'Body' => $pesan,
                ]);

            if (!$response->successful()) {
                Log::warning('WA Twilio gagal', ['nomor' => $nomor, 'body' => $response->body()]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('WA Twilio error: ' . $e->getMessage());
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

    private function compileItemsText(Order $order): string
    {
        if ($order->items->isNotEmpty()) {
            return $order->items->map(function ($item) {
                $detailStr = $item->layanan->nama;
                if ($item->jenisBarang?->nama) {
                    $detailStr .= ' (' . $item->jenisBarang->nama . ')';
                }
                return "- " . $detailStr . ": " . $item->jumlah_pasang . " pasang x Rp " . number_format($item->harga_satuan, 0, ',', '.');
            })->join("\n");
        }
        
        $detailStr = $order->layanan->nama ?? '—';
        if ($order->jenis_sepatu) {
            $detailStr .= ' (' . $order->jenis_sepatu . ')';
        }
        return "- " . $detailStr . ": " . $order->jumlah_pasang . " pasang x Rp " . number_format($order->harga_satuan ?? 0, 0, ',', '.');
    }

    public function pesanOrderMasuk(Order $order): string
    {
        return $this->render('order_masuk', [
            'nama_pelanggan'   => $order->nama_pelanggan,
            'no_order'         => $order->no_order,
            'layanan'          => $this->compileItemsText($order),
            'lokasi'           => $order->lokasi?->nama ?? '',
            'jumlah_pasang'    => $order->jumlah_pasang,
            'total'            => 'Rp ' . number_format($order->pembayaran?->total ?? $order->net_sales, 0, ',', '.'),
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
            'layanan'        => $this->compileItemsText($order),
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
            'layanan'        => $this->compileItemsText($order),
            'lokasi'         => $order->lokasi?->nama ?? '',
            'total'          => 'Rp ' . number_format($order->pembayaran?->total ?? $order->net_sales, 0, ',', '.'),
            'status_bayar'   => ($order->pembayaran?->status === 'selesai') ? 'LUNAS' : 'BELUM LUNAS',
            'poin'           => $poin > 0 ? '+' . $poin . ' poin' : '',
            'link_status'    => route('status.order', $order->token_publik),
        ]);
    }

    public function pesanInvoice(Order $order): string
    {
        return $this->render('invoice', [
            'nama_pelanggan' => $order->nama_pelanggan,
            'no_order'       => $order->no_order,
            'tanggal'        => $order->created_at?->isoFormat('D MMMM Y') ?? now()->isoFormat('D MMMM Y'),
            'layanan'        => $this->compileItemsText($order),
            'lokasi'         => $order->lokasi?->nama ?? '-',
            'total'          => 'Rp ' . number_format($order->pembayaran?->total ?? $order->net_sales, 0, ',', '.'),
            'metode_bayar'   => ucfirst($order->pembayaran?->metode ?? 'tempo'),
            'status_bayar'   => ($order->pembayaran?->status === 'selesai') ? 'LUNAS' : 'BELUM LUNAS',
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
            'order_masuk'   => "Halo {nama_pelanggan}!\n\nOrder Anda di *Step Shine Works* sudah kami terima.\n\nDetail Order\nNo. Order    : {no_order}\nDetail Sepatu\n{layanan}\n\nLokasi       : {lokasi}\nTotal        : {total}\nMetode bayar : {metode_bayar}\nEst. selesai : {estimasi_selesai}\n\nPantau status order Anda di:\n{link_status}\n\nTerima kasih!\n_Step Shine Works_",
            'mulai_dicuci'  => "Halo {nama_pelanggan}!\n\nKabar terbaru dari *Step Shine Works*: sepatu Anda sedang diproses oleh tim kami.\n\nNo. Order : {no_order}\nDetail Sepatu\n{layanan}\n\nLokasi    : {lokasi}\n\nPantau status: {link_status}\n\n_Step Shine Works_",
            'order_selesai' => "Halo {nama_pelanggan}!\n\nSepatu Anda siap diambil!\n\nNo. Order    : {no_order}\nDetail Sepatu\n{layanan}\n\nLokasi       : {lokasi}\nTotal        : {total}\nStatus bayar : {status_bayar}\nPoin earned  : {poin}\n\nPantau status: {link_status}\n\nTerima kasih!\n_Step Shine Works_",
            'invoice'       => "Invoice - {no_order}\n\nKepada  : {nama_pelanggan}\nTanggal : {tanggal}\n\nRincian Order\n{layanan}\n\nLokasi       : {lokasi}\nTotal        : {total}\n\nMetode bayar : {metode_bayar}\nStatus bayar : {status_bayar}\n\nTerima kasih!\n_Step Shine Works_",
            default         => '',
        };
    }

    protected function formatNomor(string $nomor): string
    {
        return \App\Http\Middleware\NormalizePhoneNumber::normalize($nomor);
    }
}
