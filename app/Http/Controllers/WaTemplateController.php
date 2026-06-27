<?php

namespace App\Http\Controllers;

use App\Models\WaTemplate;
use Illuminate\Http\Request;

class WaTemplateController extends Controller
{
    public function index()
    {
        $templates = WaTemplate::orderBy('id')->get();
        return view('wa-templates.index', compact('templates'));
    }

    public function edit(WaTemplate $waTemplate)
    {
        return view('wa-templates.edit', ['template' => $waTemplate]);
    }

    public function update(Request $request, WaTemplate $waTemplate)
    {
        $request->validate(['template' => 'required|string']);
        $waTemplate->update(['template' => $request->template]);
        return back()->with('success', 'Template WA berhasil disimpan.');
    }

    public function reset(WaTemplate $waTemplate)
    {
        $defaults = [
            'order_masuk'   => "Halo {nama_pelanggan}!\n\nOrder sepatu Anda di *Step Shine Works* sudah kami terima.\n\nDetail Order:\nNo. Order    : {no_order}\nLayanan      : {layanan}\nLokasi       : {lokasi}\nJumlah       : {jumlah_pasang} pasang\nTotal        : {total}\nMetode bayar : {metode_bayar}\nEst. selesai : {estimasi_selesai}\n\nPantau status order Anda di:\n{link_status}\n\nTerima kasih!\n_Step Shine Works_",
            'mulai_dicuci'  => "Halo {nama_pelanggan}!\n\nKabar terbaru dari *Step Shine Works*: sepatu Anda sudah mulai proses pencucian.\n\nNo. Order : {no_order}\nLayanan   : {layanan}\nLokasi    : {lokasi}\n\nPantau status: {link_status}\n\n_Step Shine Works_",
            'order_selesai' => "Halo {nama_pelanggan}!\n\nSepatu Anda sudah selesai dicuci dan siap diambil!\n\nDetail Order:\nNo. Order    : {no_order}\nLayanan      : {layanan}\nLokasi       : {lokasi}\nTotal        : {total}\nStatus bayar : {status_bayar}\nPoin earned  : {poin}\n\nPantau status: {link_status}\n\nSilakan ambil di toko kami.\nTerima kasih sudah mempercayakan sepatu Anda kepada kami!\n_Step Shine Works_",
            'invoice'       => "Invoice - {no_order}\n\nKepada  : {nama_pelanggan}\nTanggal : {tanggal}\n\nRincian Layanan:\nLayanan  : {layanan}\nLokasi   : {lokasi}\nJenis    : {jenis_sepatu}\nJumlah   : {jumlah_pasang} pasang\n\nHarga/pasang : {harga_satuan}\nJumlah pasang: x {jumlah_pasang}\nTotal        : {total}\n\nMetode bayar : {metode_bayar}\nStatus bayar : {status_bayar}\n\nTerima kasih telah mempercayakan sepatu Anda kepada kami!\n_Step Shine Works_",
        ];

        $waTemplate->update(['template' => $defaults[$waTemplate->kode] ?? $waTemplate->template]);
        return back()->with('success', 'Template dikembalikan ke default.');
    }
}
