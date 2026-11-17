<?php

namespace App\Services;

use App\Models\ShowroomSetting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExportService
{
    /**
     * Export sales transactions to XLSX spreadsheet.
     */
    public function exportExcel(Builder $query): StreamedResponse
    {
        $transactions = $query->with('motor')
            ->orderBy('tanggal_transaksi', 'asc')
            ->get();

        $setting = ShowroomSetting::first();
        $showroomName = $setting?->nama_showroom ?? 'Andara Motor';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Transaksi');

        // Document Title
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'LAPORAN REKAP PENJUALAN MOTOR BEKAS');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A2', strtoupper($showroomName));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:L3');
        $sheet->setCellValue('A3', 'Dicetak pada: '.Carbon::now()->translatedFormat('d F Y H:i').' WIB');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Header
        $headers = [
            'A5' => 'No',
            'B5' => 'Kode Transaksi',
            'C5' => 'Tanggal',
            'D5' => 'Unit Motor',
            'E5' => 'Merk / Tipe',
            'F5' => 'Tahun',
            'G5' => 'Nama Pembeli',
            'H5' => 'No. Telepon',
            'I5' => 'Harga Beli (Rp)',
            'J5' => 'Harga Jual (Rp)',
            'K5' => 'Keuntungan (Rp)',
            'L5' => 'Metode Bayar',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']], // Dark Navy
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ];
        $sheet->getStyle('A5:L5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(28);

        // Data Rows
        $row = 6;
        $no = 1;
        foreach ($transactions as $t) {
            $sheet->setCellValue('A'.$row, $no++);
            $sheet->setCellValue('B'.$row, $t->kode_transaksi);
            $sheet->setCellValue('C'.$row, Carbon::parse($t->tanggal_transaksi)->format('d/m/Y'));
            $sheet->setCellValue('D'.$row, $t->motor?->nama_motor ?? '-');
            $sheet->setCellValue('E'.$row, ($t->motor?->merk ?? '-').' / '.($t->motor?->tipe_motor ?? '-'));
            $sheet->setCellValue('F'.$row, $t->motor?->tahun ?? '-');
            $sheet->setCellValue('G'.$row, $t->nama_pembeli);
            $sheet->setCellValue('H'.$row, $t->no_telepon_pembeli);
            $sheet->setCellValue('I'.$row, $t->harga_beli);
            $sheet->setCellValue('J'.$row, $t->harga_jual);
            $sheet->setCellValue('K'.$row, $t->keuntungan);
            $sheet->setCellValue('L'.$row, $t->metode_pembayaran);

            // Number formatting for currency
            $sheet->getStyle('I'.$row.':K'.$row)->getNumberFormat()->setFormatCode('#,##0');

            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('L'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Border for data row
            $sheet->getStyle('A'.$row.':L'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $row++;
        }

        // Total Row
        if ($row > 6) {
            $sheet->mergeCells('A'.$row.':H'.$row);
            $sheet->setCellValue('A'.$row, 'TOTAL KESELURUHAN');
            $sheet->getStyle('A'.$row)->getFont()->setBold(true);
            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->setCellValue('I'.$row, '=SUM(I6:I'.($row - 1).')');
            $sheet->setCellValue('J'.$row, '=SUM(J6:J'.($row - 1).')');
            $sheet->setCellValue('K'.$row, '=SUM(K6:K'.($row - 1).')');

            $sheet->getStyle('I'.$row.':K'.$row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('A'.$row.':L'.$row)->getFont()->setBold(true);
            $sheet->getStyle('A'.$row.':L'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');
            $sheet->getStyle('A'.$row.':L'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        // Auto size columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'laporan_transaksi_'.Carbon::now()->format('Ymd_His').'.xlsx';

        return response()->stream(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export sales transactions to CSV format.
     */
    public function exportCsv(Builder $query): StreamedResponse
    {
        $transactions = $query->with('motor')
            ->orderBy('tanggal_transaksi', 'asc')
            ->get();

        $filename = 'laporan_transaksi_'.Carbon::now()->format('Ymd_His').'.csv';

        return response()->stream(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header
            fputcsv($handle, [
                'No',
                'Kode Transaksi',
                'Tanggal',
                'Unit Motor',
                'Merk',
                'Tipe',
                'Tahun',
                'Nama Pembeli',
                'No. Telepon',
                'Harga Beli',
                'Harga Jual',
                'Keuntungan',
                'Metode Pembayaran',
            ]);

            $no = 1;
            $totalBeli = 0;
            $totalJual = 0;
            $totalUntung = 0;

            foreach ($transactions as $t) {
                $totalBeli += $t->harga_beli;
                $totalJual += $t->harga_jual;
                $totalUntung += $t->keuntungan;

                fputcsv($handle, [
                    $no++,
                    $t->kode_transaksi,
                    Carbon::parse($t->tanggal_transaksi)->format('Y-m-d'),
                    $t->motor?->nama_motor ?? '-',
                    $t->motor?->merk ?? '-',
                    $t->motor?->tipe_motor ?? '-',
                    $t->motor?->tahun ?? '-',
                    $t->nama_pembeli,
                    $t->no_telepon_pembeli,
                    $t->harga_beli,
                    $t->harga_jual,
                    $t->keuntungan,
                    $t->metode_pembayaran,
                ]);
            }

            // Summary row
            fputcsv($handle, [
                'TOTAL',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                $totalBeli,
                $totalJual,
                $totalUntung,
                '',
            ]);

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
