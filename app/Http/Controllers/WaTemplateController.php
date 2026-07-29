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
            'order_masuk' => "Halo {nama_pelanggan}!\n\nOrder Anda di *Step Shine Works* sudah kami terima.\n\nDetail Order\nNo. Order    : {no_order}\nDetail Sepatu\n{layanan}\n\nLokasi       : {lokasi}\nTotal        : {total}\nMetode bayar : {metode_bayar}\nEst. selesai : {estimasi_selesai}\n\nPantau status order Anda di:\n{link_status}\n\nTerima kasih!\n_Step Shine Works_",
            'mulai_dicuci' => "Halo {nama_pelanggan}!\n\nKabar terbaru dari *Step Shine Works*: sepatu Anda sedang diproses oleh tim kami.\n\nNo. Order : {no_order}\nDetail Sepatu\n{layanan}\n\nLokasi    : {lokasi}\n\nPantau status: {link_status}\n\n_Step Shine Works_",
            'order_selesai' => "Halo {nama_pelanggan}!\n\nSepatu Anda sudah selesai dan siap diambil!\n\nNo. Order     : {no_order}\nDetail Sepatu\n{layanan}\n\nLokasi        : {lokasi}\nTotal         : {total}\nStatus bayar  : {status_bayar}\nEstimasi poin : {poin}\n\nPoin ditambahkan setelah order selesai.\nPantau status: {link_status}\n\nSilakan ambil di toko kami.\nTerima kasih sudah mempercayakan sepatu Anda kepada kami!\n_Step Shine Works_",
            'invoice' => "Invoice - {no_order}\n\nKepada  : {nama_pelanggan}\nTanggal : {tanggal}\n\nRincian Order\n{layanan}\n\nLokasi       : {lokasi}\nTotal        : {total}\n\nMetode bayar : {metode_bayar}\nStatus bayar : {status_bayar}\n\nTerima kasih telah mempercayakan sepatu Anda kepada kami!\n_Step Shine Works_",
        ];

        $waTemplate->update(['template' => $defaults[$waTemplate->kode] ?? $waTemplate->template]);

        return back()->with('success', 'Template dikembalikan ke default.');
    }
}
