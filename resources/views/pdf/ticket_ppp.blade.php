<!DOCTYPE html>
<html>
<head>
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 8pt; line-height: 1.2; }

        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-bottom: -1px; }
        th, td { border: 1px solid black; padding: 2px 4px; vertical-align: top; }

        /* Helper Classes */
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .bg-gray { background-color: #f0f0f0; font-weight: bold; font-size: 7pt; }
        .no-border-top { border-top: none; }
        .box { width: 12px; height: 12px; border: 1px solid black; display: inline-block; vertical-align: middle; text-align: center; line-height: 10px; font-weight: bold; }

        /* Layout Specific */
        .header-title { font-size: 11pt; }
        .section-title { font-size: 7pt; letter-spacing: 1px; }
        .tiny-text { font-size: 6pt; }
    </style>
</head>
<body>

    <table>
        <tr>
            <td colspan="4" class="text-center" style="border-bottom: none;">
                <div style="font-size: 9pt;">SENTRAL BAHANA EKATAMA</div>
                <div class="header-title font-bold">PEMELIHARAAN PEMERIKSAAN PERBAIKAN</div>
            </td>
            <td style="width: 30%;">
                <div class="font-bold" style="font-size: 10pt;">No.: <span style="margin-left: 20px;">{{ $ticket->ticket_number }}</span></div>
            </td>
        </tr>
        <tr>
            <td colspan="5" class="text-center bg-gray section-title">DIISI OLEH PENGGUNA</td>
        </tr>
    </table>

    <table>
    <tr>
        <td style="width: 15%;">Jenis<sup>(1)</sup></td>
        <td style="width: 2%;">:</td>
        <td style="width: 43%;">
            <div class="box">{{ $ticket->category == 'Mesin' ? 'v' : '' }}</div> Mesin
            <div class="box" style="margin-left: 10px;">{{ $ticket->category == 'Peralatan' ? 'v' : '' }}</div> Peralatan
            <div class="box" style="margin-left: 10px;">{{ !in_array($ticket->category, ['Mesin','Peralatan']) ? 'v' : '' }}</div> Lainnya
        </td>
        <td style="width: 20%;" class="bg-gray">Kategori Tindakan <sup>(1)</sup></td>
        <td style="width: 20%;" class="text-center">Pengguna,</td>
    </tr>
    <tr>
        <td>Resource / Tipe</td>
        <td>:</td>
        <td>{{ $ticket->asset->name ?? '-' }}</td>
        <td><div class="box"></div> Pemeliharaan</td>
        <td rowspan="3" style="vertical-align: bottom; height: 60px;">
            <div class="text-center">
                <div style="margin-bottom: 40px;"></div> <hr style="border: 0.5px solid black; width: 80%; margin: 0 auto;">
                <span class="tiny-text">Nama & TTD</span>
            </div>
        </td>
    </tr>
    <tr>
        <td>Deskripsi</td>
        <td>:</td>
        <td>{{ $ticket->subject }}</td>
        <td><div class="box"></div> Pemeriksaan</td>
    </tr>
    <tr>
        <td>Tanggal Permintaan</td>
        <td>:</td>
        <td>{{ $ticket->created_at->format('d - m - Y | H : i') }}</td>
        <td><div class="box">v</div> Perbaikan</td>
    </tr>

    <tr>
        <td colspan="3" style="padding: 0; vertical-align: top;">
            <table style="border: none; width: 100%; height: 25px;">
                <tr>
                    <td style="border: none; width: 80px; font-size: 8pt;">Problem Detail:</td>
                    <td style="border: none; width: 10px;">:</td>
                    <td style="border: none;   vertical-align: top;">
                        {{ $ticket->problem_detail ?? '-' }}
                    </td>
                </tr>
            </table>
        </td>
        <td style="padding: 0; vertical-align: top;">
            <table style="border: none; width: 100%;">
                <tr><td class="bg-gray text-center" style="border: none; border-bottom: 1px solid black;">Waktu yang diberikan <sup>(1)</sup></td></tr>
                <tr><td style="border: none; border-bottom: 1px solid black; height: 25px;">Maks. 24 jam</td></tr>
                <tr><td style="border: none; border-bottom: 1px solid black; height: 25px;">Maks. 2 x 24 jam</td></tr>
                <tr><td style="border: none; height: 25px;">Selesaikan tanggal:</td></tr>
            </table>
        </td>
        <td style="vertical-align: top; padding: 0;">
            <table style="border: none; width: 100%; height: 20px;">
                <tr><td class="text-center bg-gray section-title" style="border: none; border-bottom: 1px solid black; font-size: 6.5pt;">Mengetahui atasan,</td></tr>
                <tr>
                    <td style="border: none; height: 120px; vertical-align: bottom;" class="text-center">
                        <div style="margin-bottom: 40px;"></div>
                        <hr style="border: 0.5px solid black; width: 80%; margin: 0 auto;">
                        <span class="tiny-text">Nama & TTD</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width: 15%;">Emergency Action:</td>
        <td style="width: 2%;">:</td>
        <td colspan="3" style="height: 25px;"> {{ $ticket->emergency_action ?? '-' }}</td>
    </tr>
</table>

    <table>
        <tr><td colspan="3" class="text-center bg-gray section-title">DIISI OLEH PLANT ENGINEERING, ITE ATAU MAINTENANCE</td></tr>
        <tr>
            <td style="width: 20%;">Rencana tindakan yang diambil:</td>
            <td style="width: 60%; height: 40px;"> {{ $ticket->action_plan ?? '-' }}</td>
            <td style="width: 20%;" class="text-center">
                <div class="tiny-text font-bold">PE / ITE / MTN Spv. <sup>(2)</sup></div>
                <div style="margin-top: 25px;"><hr style="border: 0.5px solid black; width: 80%;"></div>
                <div class="tiny-text">Nama & TTD</div>
            </td>
        </tr>
        <tr>
            <td>Nama Teknisi : {{ $ticket->technician->name ?? '-' }}</td>
            <td colspan="2">
                {{-- Jadwal Pelaksanaan : <span style="margin-left: 20px;">.... / .... / ........</span> --}}
                Jadwal Pelaksanaan : {{ $ticket->schedule_date ? date('d/m/Y', strtotime($ticket->schedule_date)) : '-' }}
            </td>
        </tr>
    </table>

    <table>
        <tr><td colspan="2" class="text-center bg-gray section-title">DIISI OLEH TEKNISI PELAKSANA</td></tr>
        <tr>
            <td style="width: 80%;">
                Analisa penyebab kerusakan <sup>(3)</sup>: <br>
                <div style="height: 80px; font-style: italic;">{{ $ticket->damage_analysis ?? '' }}</div>
                <div class="tiny-text" style="font-style: italic;">(3) Wajib melampirkan 4M + 1E & 5 WHY Analysis sesuai form FMQAS05...</div>
            </td>
            <td style="width: 20%;" class="text-center">
                <div class="tiny-text font-bold">PE / ITE / MTN Teknisi <sup>(2)</sup></div>
                <div style="margin-top: 70px;"><hr style="border: 0.5px solid black; width: 80%;"></div>
                <div class="tiny-text">Nama & TTD</div>
            </td>
        </tr>
        <tr>
            <td>Tindakan perbaikan sementara yang sudah dilakukan: <div style="height: 30px;">
                {{ $ticket->temp_action ?? '-' }}
                </div></td>
            <td class="text-center">
                <div class="tiny-text font-bold">PE / ITE / MTN Teknisi <sup>(2)</sup></div>
                <div style="margin-top: 70px;"><hr style="border: 0.5px solid black; width: 80%;"></div>
                <div class="tiny-text">Nama & TTD</div>
            </td>
        </tr>
        <tr>
            <td>Tindakan perbaikan permanen dilakukan: <div style="height: 80px;">{{ $ticket->perm_action ?? '-' }}</div></td>
            <td class="text-center">
                <div class="tiny-text font-bold">PE / ITE / MTN Teknisi <sup>(2)</sup></div>
                <div style="margin-top: 70px;"><hr style="border: 0.5px solid black; width: 80%;"></div>
                <div class="tiny-text">Nama & TTD</div>
            </td>
        </tr>
        <tr>
            <td>Tindakan pencegahan: <div style="height: 80px;">{{ $ticket->preventive_action ?? '-' }}</div></td>
            <td class="text-center">
                <div class="tiny-text font-bold">PE / ITE / MTN Spv. <sup>(2)</sup></div>
                <div style="margin-top: 70px;"><hr style="border: 0.5px solid black; width: 80%;"></div>
                <div class="tiny-text">Nama & TTD</div>
            </td>
        </tr>
    </table>

    <table>
        <tr><td colspan="5" class="text-center bg-gray section-title">PENINJAUAN HASIL <sup>(4)</sup></td></tr>
        <tr>
            <td colspan="2">Tanggal selesai PPP : <span>.... / .... / ........</span></td>
            <td>Total down time <sup>(5)</sup>:</td>
            <td colspan="2">........ jam ........ menit</td>
        </tr>
        <tr class="bg-gray text-center tiny-text">
            <td style="width: 30%;">Hasil <sup>(1)</sup></td>
            <td style="width: 17.5%;">Pengguna</td>
            <td style="width: 17.5%;">Atasan Pengguna</td>
            <td style="width: 17.5%;">Manager</td>
            <td style="width: 17.5%;">Direktur</td>
        </tr>
        <tr>
            <td>
                <div class="box"></div> Berhasil<br>
                <div class="box"></div> Gunakan sementara<sup>(6)</sup><br>
                <div class="box"></div> Gagal / Tidak bisa diperbaiki<sup>(7)</sup>
            </td>
            <td style="vertical-align: bottom;" class="text-center"><span class="tiny-text">Nama & TTD</span></td>
            <td style="vertical-align: bottom;" class="text-center"><span class="tiny-text">Nama & TTD</span></td>
            <td style="vertical-align: bottom;" class="text-center"><span class="tiny-text">Nama & TTD</span></td>
            <td style="vertical-align: bottom;" class="text-center"><span class="tiny-text">Nama & TTD</span></td>
        </tr>
    </table>

    <div class="tiny-text font-bold" style="margin-top: 5px;">Catatan :</div>
    <div class="tiny-text">
        <sup>(1)</sup> Pilih salah satu dan isi dengan tanda "v" ; <sup>(2)</sup> Coret yang tidak sesuai ; <sup>(4)</sup> Pengguna wajib koordinasi dengan PPC...
    </div>
    <div class="tiny-text font-bold">{{ $ticket->ticket_number ?? '' }}</div>

</body>
</html>
