<!DOCTYPE html>
<html>
<head>
    <style>
        @page { size: landscape; margin: 1cm; }
        body { font-family: Arial, sans-serif; font-size: 8px; }
        .header { text-align: center; margin-bottom: 10px; }
        .title { font-size: 14px; font-weight: bold; text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 3px; }
        th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; }
        .bg-green { background-color: #00ff00; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin:0">SENTRAL BAHANA EKATAMA PT</h2>
        <div class="title">MASTERLIST PEMELIHARAAN PEMERIKSAAN PERBAIKAN</div>
        <table style="border: none; margin-top: 5px;">
            <tr style="border: none;">
                <td style="border: none; text-align: left; width: 50%;">Periode : {{ $startDate }} - {{ $endDate }}</td>
                <td style="border: none; text-align: right;">Printed on : {{ now()->format('d-M-y') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">No.</th>
                <th rowspan="2">Nomor PPP</th>
                <th rowspan="2">Jenis & Kategori</th>
                <th rowspan="2">Tanggal Permintaan</th>
                <th rowspan="2">Resource / Tipe</th>
                <th rowspan="2">Deskripsi</th>
                <th rowspan="2">Problem Detail</th>
                <th rowspan="2">Analisa Penyebab</th>
                <th colspan="3">Tindakan Perbaikan</th>
                <th rowspan="2">Nama Teknisi</th>
                <th rowspan="2">Tanggal Selesai</th>
                <th rowspan="2" class="bg-green">Durasi</th>
                <th rowspan="2">Total Downtime</th>
                <th rowspan="2">Hasil</th>
            </tr>
            <tr>
                <th>Sementara</th>
                <th>Permanen</th>
                <th>Pencegahan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $index => $t)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center"><b>{{ $t->ticket_number }}</b></td>
                <td>{{ $t->category }}</td>
                <td class="text-center">{{ $t->created_at->format('d M y H.i') }}</td>
                <td>{{ $t->aset->nama_aset ?? '-' }}</td>
                <td>{{ $t->problem_detail }}</td>
                <td>{{ $t->problem_detail }}</td>
                <td>{{ $t->damage_analysis ?? '-' }}</td>
                <td>-</td>
                <td>{{ $t->perm_action ?? '-' }}</td>
                <td>{{ $t->preventive_action ?? '-' }}</td>
                <td class="text-center">{{ $t->technician->name ?? '-' }}</td>
                <td class="text-center">{{ $t->completion_date ? $t->completion_date->format('d M y H.i') : '-' }}</td>
                <td class="text-center">
                    @if($t->completion_date)
                        {{ $t->created_at->diff($t->completion_date)->format('%H:%I:%S') }}
                    @else - @endif
                </td>
                <td class="text-center">-</td>
                <td class="text-center font-bold">{{ strtoupper($t->status == 'closed' ? 'Berhasil' : $t->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
