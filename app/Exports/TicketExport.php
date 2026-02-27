<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TicketExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $query;

    public function __construct($query) { $this->query = $query; }

    public function query() { return $this->query; }

    public function headings(): array {
        return [
            ['SENTRAL BAHANA EKATAMA PT'],
            ['MASTERLIST PEMELIHARAAN PEMERIKSAAN PERBAIKAN'],
            [''],
            ['No.', 'Nomor PPP', 'Jenis & Kategori', 'Tanggal Permintaan', 'Resource / Tipe', 'Deskripsi', 'Problem Detail', 'Analisa Penyebab', 'Sementara', 'Permanen', 'Pencegahan', 'Nama Teknisi', 'Tanggal Selesai', 'Durasi', 'Total Downtime', 'Hasil']
        ];
    }

    public function map($t): array {
        static $no = 1;
        return [
            $no++,
            $t->ticket_number,
            $t->category,
            $t->created_at->format('d M y H.i'),
            $t->aset->nama_aset ?? '-',
            $t->problem_detail,
            $t->problem_detail,
            $t->damage_analysis,
            '',
            $t->perm_action,
            $t->preventive_action,
            $t->technician->name ?? '-',
            $t->completion_date ? $t->completion_date->format('d M y H.i') : '-',
            $t->completion_date ? $t->created_at->diff($t->completion_date)->format('%H:%I:%S') : '-',
            '',
            $t->status == 'closed' ? 'Berhasil' : $t->status
        ];
    }

    public function styles(Worksheet $sheet) {
        // Merging header
        $sheet->mergeCells('A1:P1');
        $sheet->mergeCells('A2:P2');

        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4:P4')->getFont()->setBold(true);

        // Kolom Durasi Warna Hijau (Kolom N)
        $sheet->getStyle('N4:N100')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('00FF00');

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12, 'underline' => true]],
            4 => ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]],
        ];
    }
}

?>
