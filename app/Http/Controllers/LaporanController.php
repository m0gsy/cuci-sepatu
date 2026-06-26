<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Pembayaran;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    protected function getRekapData(string $bulan): array
    {
        [$tahun, $bln] = explode('-', $bulan);

        $rekap = Layanan::withCount(['orders' => fn($q) =>
                $q->whereYear('orders.created_at', $tahun)
                  ->whereMonth('orders.created_at', $bln)
            ])
            ->withSum(['orders as total_jumlah_pasang' => fn($q) =>
                $q->whereYear('orders.created_at', $tahun)
                  ->whereMonth('orders.created_at', $bln)
            ], 'orders.jumlah_pasang')
            ->withSum(['orders as total_pendapatan' => fn($q) =>
                $q->whereYear('orders.created_at', $tahun)
                  ->whereMonth('orders.created_at', $bln)
                  ->join('pembayarans', 'orders.id', '=', 'pembayarans.order_id')
            ], 'pembayarans.total')
            ->get();

        $total_bulan = Pembayaran::whereYear('pembayarans.dibayar_pada', $tahun)
            ->whereMonth('pembayarans.dibayar_pada', $bln)
            ->where('status', 'lunas')->sum('total');

        return compact('rekap', 'total_bulan', 'bulan');
    }

    protected function getData(string $bulan): array
    {
        [$tahun, $bln] = explode('-', $bulan);
        $data = $this->getRekapData($bulan);

        $data['orders'] = \App\Models\Order::with(['layanan', 'pembayaran'])
            ->whereYear('orders.created_at', $tahun)
            ->whereMonth('orders.created_at', $bln)
            ->latest()->get();

        return $data;
    }

    public function index(Request $request)
    {
        $bulan = $request->bulan ?? now()->format('Y-m');
        [$tahun, $bln] = explode('-', $bulan);

        $data           = $this->getRekapData($bulan);
        $data['orders'] = \App\Models\Order::with(['layanan', 'pembayaran'])
            ->whereYear('orders.created_at', $tahun)
            ->whereMonth('orders.created_at', $bln)
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('laporan.index', $data);
    }

    public function exportPdf(Request $request)
    {
        $bulan = $request->bulan ?? now()->format('Y-m');
        $data  = $this->getData($bulan);
        $pdf   = Pdf::loadView('pdf.laporan', $data)->setPaper('a4', 'portrait');
        return $pdf->download("laporan-{$bulan}.pdf");
    }

    public function exportExcel(Request $request)
    {
        $bulan     = $request->bulan ?? now()->format('Y-m');
        $data      = $this->getData($bulan);
        $rekap     = $data['rekap'];
        $orders    = $data['orders'];
        $namaBulan = \Carbon\Carbon::parse($bulan . '-01')->isoFormat('MMMM Y');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        $styleHeader = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '222222']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
        ];
        $styleTotal = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '333333']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
        ];
        $styleBorder = [
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];

        // Sheet 1 — Rekap Bulanan
        $ws1 = $spreadsheet->getActiveSheet()->setTitle('Rekap Bulanan');
        $ws1->setShowGridlines(false);
        $ws1->freezePane('A5');

        $ws1->mergeCells('A1:E1');
        $ws1->setCellValue('A1', 'LAPORAN PENJUALAN — CUCI SEPATU');
        $ws1->getStyle('A1')->getFont()->setBold(true)->setSize(15);
        $ws1->getRowDimension(1)->setRowHeight(28);

        $ws1->mergeCells('A2:E2');
        $ws1->setCellValue('A2', 'Periode: ' . $namaBulan);
        $ws1->getStyle('A2')->getFont()->setSize(10)->getColor()->setRGB('888888');
        $ws1->getRowDimension(3)->setRowHeight(8);

        $headers1 = ['Layanan', 'Harga Satuan (Rp)', 'Jumlah Pasang', 'Total Pendapatan (Rp)', '% dari Total'];
        $widths1   = [26, 22, 16, 26, 14];
        foreach ($headers1 as $i => $h) {
            $col = chr(65 + $i);
            $ws1->setCellValue("{$col}4", $h);
            $ws1->getColumnDimension($col)->setWidth($widths1[$i]);
        }
        $ws1->getStyle('A4:E4')->applyFromArray($styleHeader);
        $ws1->getRowDimension(4)->setRowHeight(22);

        $dataStart   = 5;
        $totalRowIdx = $dataStart + $rekap->count();

        foreach ($rekap as $i => $l) {
            $row = $dataStart + $i;
            $bg  = $i % 2 === 0 ? 'F7F7F5' : 'FFFFFF';
            $ws1->getRowDimension($row)->setRowHeight(20);
            $ws1->setCellValue("A{$row}", $l->nama);
            $ws1->setCellValue("B{$row}", $l->harga);
            $ws1->setCellValue("C{$row}", $l->total_jumlah_pasang ?? 0);
            $ws1->setCellValue("D{$row}", $l->total_pendapatan ?? 0);
            $ws1->setCellValue("E{$row}", "=IFERROR(D{$row}/D{$totalRowIdx},0)");
            $ws1->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorder);
            $ws1->getStyle("A{$row}:E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
            $ws1->getStyle("A{$row}")->getFont()->setBold(true);
            $ws1->getStyle("B{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $ws1->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws1->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $ws1->getStyle("D{$row}")->getFont()->setBold(true);
            $ws1->getStyle("E{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $ws1->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $ws1->getRowDimension($totalRowIdx)->setRowHeight(22);
        $ws1->setCellValue("A{$totalRowIdx}", 'TOTAL');
        $ws1->setCellValue("C{$totalRowIdx}", "=SUM(C{$dataStart}:C" . ($totalRowIdx - 1) . ")");
        $ws1->setCellValue("D{$totalRowIdx}", "=SUM(D{$dataStart}:D" . ($totalRowIdx - 1) . ")");
        $ws1->setCellValue("E{$totalRowIdx}", '100%');
        $ws1->getStyle("A{$totalRowIdx}:E{$totalRowIdx}")->applyFromArray($styleTotal);
        $ws1->getStyle("C{$totalRowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $ws1->getStyle("D{$totalRowIdx}")->getNumberFormat()->setFormatCode('#,##0');
        $ws1->getStyle("E{$totalRowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Sheet 2 — Detail Order
        $ws2 = $spreadsheet->createSheet()->setTitle('Detail Order');
        $ws2->setShowGridlines(false);
        $ws2->freezePane('A3');

        $ws2->mergeCells('A1:G1');
        $ws2->setCellValue('A1', 'DETAIL ORDER — ' . strtoupper($namaBulan));
        $ws2->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $ws2->getRowDimension(1)->setRowHeight(24);

        $headers2 = ['No. Order', 'Tanggal', 'Pelanggan', 'Layanan', 'Jml Pasang', 'Total (Rp)', 'Status'];
        $widths2   = [20, 13, 24, 16, 12, 20, 12];
        foreach ($headers2 as $i => $h) {
            $col = chr(65 + $i);
            $ws2->setCellValue("{$col}2", $h);
            $ws2->getColumnDimension($col)->setWidth($widths2[$i]);
        }
        $ws2->getStyle('A2:G2')->applyFromArray($styleHeader);
        $ws2->getRowDimension(2)->setRowHeight(22);

        $statusColors = [
            'antri'   => ['bg' => 'FEF3C7', 'txt' => '92400E'],
            'proses'  => ['bg' => 'DBEAFE', 'txt' => '1E40AF'],
            'selesai' => ['bg' => 'D1FAE5', 'txt' => '065F46'],
            'diambil' => ['bg' => 'F3F4F6', 'txt' => '444441'],
        ];

        foreach ($orders as $i => $o) {
            $row = $i + 3;
            $bg  = $i % 2 === 0 ? 'F7F7F5' : 'FFFFFF';
            $ws2->getRowDimension($row)->setRowHeight(20);
            $ws2->setCellValue("A{$row}", $o->no_order);
            $ws2->setCellValue("B{$row}", $o->created_at->format('d/m/Y'));
            $ws2->setCellValue("C{$row}", $o->nama_pelanggan);
            $ws2->setCellValue("D{$row}", $o->layanan->nama);
            $ws2->setCellValue("E{$row}", $o->jumlah_pasang);
            $ws2->setCellValue("F{$row}", $o->pembayaran?->total ?? 0);
            $ws2->setCellValue("G{$row}", ucfirst($o->status));
            $ws2->getStyle("A{$row}:G{$row}")->applyFromArray($styleBorder);
            $ws2->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
            $ws2->getStyle("A{$row}")->getFont()->setName('Courier New')->setSize(9)->getColor()->setRGB('666666');
            $ws2->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws2->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws2->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $ws2->getStyle("F{$row}")->getFont()->setBold(true);
            $ws2->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sc = $statusColors[$o->status] ?? ['bg' => 'F3F4F6', 'txt' => '444441'];
            $ws2->getStyle("G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($sc['bg']);
            $ws2->getStyle("G{$row}")->getFont()->setBold(true)->getColor()->setRGB($sc['txt']);
        }

        $totalRow2 = $orders->count() + 3;
        $ws2->getRowDimension($totalRow2)->setRowHeight(22);
        $ws2->setCellValue("A{$totalRow2}", 'TOTAL');
        $ws2->setCellValue("E{$totalRow2}", "=SUM(E3:E" . ($totalRow2 - 1) . ")");
        $ws2->setCellValue("F{$totalRow2}", "=SUM(F3:F" . ($totalRow2 - 1) . ")");
        $ws2->getStyle("A{$totalRow2}:G{$totalRow2}")->applyFromArray($styleTotal);
        $ws2->getStyle("E{$totalRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $ws2->getStyle("F{$totalRow2}")->getNumberFormat()->setFormatCode('#,##0');

        $spreadsheet->setActiveSheetIndex(0);
        $filename = "laporan-{$bulan}.xlsx";
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
