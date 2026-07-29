<?php

namespace Database\Seeders;

use App\Models\WaTemplate;
use Illuminate\Database\Seeder;

class WaTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'kode' => 'order_masuk',
                'nama' => 'Order Baru Masuk',
                'template' => "Halo {nama_pelanggan}!\n\nOrder Anda di *Step Shine Works* sudah kami terima.\n\nDetail Order\nNo. Order    : {no_order}\nDetail Sepatu\n{layanan}\n\nLokasi       : {lokasi}\nTotal        : {total}\nMetode bayar : {metode_bayar}\nEst. selesai : {estimasi_selesai}\n\nPantau status order Anda di:\n{link_status}\n\nTerima kasih!\n_Step Shine Works_",
            ],
            [
                'kode' => 'mulai_dicuci',
                'nama' => 'Sedang Diproses',
                'template' => "Halo {nama_pelanggan}!\n\nKabar terbaru dari *Step Shine Works*: sepatu Anda sedang diproses oleh tim kami.\n\nNo. Order : {no_order}\nDetail Sepatu\n{layanan}\n\nLokasi    : {lokasi}\n\nPantau status: {link_status}\n\n_Step Shine Works_",
            ],
            [
                'kode' => 'order_selesai',
                'nama' => 'Siap Diambil',
                'template' => "Halo {nama_pelanggan}!\n\nSepatu Anda sudah selesai dan siap diambil!\n\nNo. Order     : {no_order}\nDetail Sepatu\n{layanan}\n\nLokasi        : {lokasi}\nTotal         : {total}\nStatus bayar  : {status_bayar}\nEstimasi poin : {poin}\n\nPoin ditambahkan setelah order selesai.\nPantau status: {link_status}\n\nSilakan ambil di toko kami.\nTerima kasih sudah mempercayakan sepatu Anda kepada kami!\n_Step Shine Works_",
            ],
            [
                'kode' => 'invoice',
                'nama' => 'Invoice',
                'template' => "Invoice - {no_order}\n\nKepada  : {nama_pelanggan}\nTanggal : {tanggal}\n\nRincian Order\n{layanan}\n\nLokasi       : {lokasi}\nTotal        : {total}\n\nMetode bayar : {metode_bayar}\nStatus bayar : {status_bayar}\n\nTerima kasih telah mempercayakan sepatu Anda kepada kami!\n_Step Shine Works_",
            ],
        ];

        foreach ($templates as $data) {
            WaTemplate::updateOrCreate(['kode' => $data['kode']], $data);
        }
    }
}
