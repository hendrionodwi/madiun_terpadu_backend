<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Rekapitulasi Neraca Sampah - {{ $profile->nama_tps3r ?? 'TPS3R Kota Madiun' }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', Times, serif;
        }

        body {
            background-color: #f1f5f9;
            color: #0f172a;
            line-height: 1.4;
            padding: 20px;
        }

        .paper {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 20mm 20mm 25mm 20mm;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        }

        .toolbar {
            width: 210mm;
            margin: 0 auto 16px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #0f766e;
            padding: 12px 18px;
            border-radius: 10px;
            color: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .toolbar a, .toolbar button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: background-color 0.15s ease;
        }

        .btn-print { background: #ffffff; color: #115e59; }
        .btn-print:hover { background: #ccfbf1; }

        .btn-csv { background: #0284c7; color: #ffffff; }
        .btn-csv:hover { background: #0369a1; }

        .btn-back { background: transparent; color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.4); }
        .btn-back:hover { background: rgba(255, 255, 255, 0.15); }

        /* KOP SURAT */
        .kop-header {
            display: flex;
            align-items: center;
            border-bottom: 3px double #000000;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .kop-logo {
            width: 75px;
            height: 75px;
            margin-right: 18px;
            flex-shrink: 0;
        }

        .kop-text {
            text-align: center;
            flex-grow: 1;
        }

        .kop-text h3 {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .kop-text h2 {
            font-size: 13.5pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 1px 0;
        }

        .kop-text h1 {
            font-size: 15pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #115e59;
            margin: 2px 0;
        }

        .kop-text p {
            font-size: 9pt;
            margin: 1px 0;
        }

        /* JUDUL DOKUMEN */
        .doc-title {
            text-align: center;
            margin-bottom: 18px;
        }

        .doc-title h3 {
            font-size: 12.5pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .doc-title p {
            font-size: 10pt;
            margin-top: 4px;
        }

        /* TABEL DAN KONTEN */
        h4.section-heading {
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 14px 0 6px 0;
            padding-bottom: 3px;
            border-bottom: 1px solid #94a3b8;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 14px;
        }

        .stat-box {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            background: #f8fafc;
            border-radius: 4px;
            text-align: center;
        }

        .stat-box .label {
            font-size: 8pt;
            color: #475569;
            text-transform: uppercase;
            font-weight: bold;
        }

        .stat-box .val {
            font-size: 13pt;
            font-weight: bold;
            color: #0f766e;
            margin-top: 2px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 14px;
        }

        table.data-table th, table.data-table td {
            border: 1px solid #64748b;
            padding: 5px 7px;
        }

        table.data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
        }

        table.data-table td.text-right { text-align: right; }
        table.data-table td.text-center { text-align: center; }

        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            font-size: 10pt;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
            width: 240px;
        }

        .signature-space {
            height: 65px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }

        @media print {
            body { background: none; padding: 0; }
            .paper { box-shadow: none; padding: 0; width: 100%; min-height: auto; }
            .no-print { display: none !important; }
            @page { size: A4 portrait; margin: 15mm; }
        }
    </style>
</head>
<body>

    <!-- Toolbar Layar Non-Print -->
    <div class="toolbar no-print">
        <div>
            <strong>Berita Acara Rekapitulasi TPS3R</strong>
            <span style="opacity: 0.8; font-size: 12px; margin-left: 8px;">({{ $profile->nama_tps3r ?? 'TPS3R Kota Madiun' }})</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="btn-print" onclick="window.print()">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak / Simpan PDF
            </button>
            <a href="{{ route('dashboard.tps3r.volume-recap.export', ['format' => 'csv', 'start_date' => $startDate, 'end_date' => $endDate, 'tps3r_profile_id' => $profile?->id]) }}" class="btn-csv">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Unduh CSV / Excel
            </a>
            <a href="{{ route('dashboard.tps3r.volume-recap') }}" class="btn-back">
                Kembali ke Analitik TPS3R
            </a>
        </div>
    </div>

    <div class="paper">
        <!-- Kop Surat Resmi TPS3R -->
        <div class="kop-header">
            <svg class="kop-logo" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="46" stroke="#0F766E" stroke-width="4" fill="#F0FDFA"/>
                <path d="M50 20L74 36V64L50 80L26 64V36L50 20Z" fill="#0D9488" stroke="#0F766E" stroke-width="2"/>
                <path d="M38 52L46 60L64 42" stroke="#FFFFFF" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <div class="kop-text">
                <h3>Pemerintah Kota Madiun • Dinas Lingkungan Hidup</h3>
                <h2>Unit Pengolahan Sampah Terpadu 3R (TPS 3R)</h2>
                <h1>{{ strtoupper($profile->nama_tps3r ?? 'FASILITAS TPS 3R KOTA MADIUN') }}</h1>
                <p>Alamat: {{ $profile->alamat ?? 'Kota Madiun' }}, Kel. {{ $profile->kelurahan ?? '-' }}, Kec. {{ $profile->kecamatan ?? '-' }}</p>
                <p>Nomor Registrasi: <strong>{{ $profile->kode_tps3r ?? 'TPS3R-MDN' }}</strong> | Kapasitas Desain: {{ number_format($kapasitasHarian, 0, ',', '.') }} kg/hari</p>
            </div>
        </div>

        <!-- Judul Laporan -->
        <div class="doc-title">
            <h3>Berita Acara Rekapitulasi Timbangan & Neraca Sampah Masuk</h3>
            <p>Nomor: {{ $docNumber }}</p>
            <p>Periode: <strong>{{ \Illuminate\Support\Carbon::parse($startDate)->translatedFormat('d F Y') }}</strong> s/d <strong>{{ \Illuminate\Support\Carbon::parse($endDate)->translatedFormat('d F Y') }}</strong></p>
        </div>

        <!-- Section 1: Neraca Fasilitas -->
        <h4 class="section-heading">I. Neraca Operasional & Tingkat Utilisasi Fasilitas</h4>
        <div class="stat-grid">
            <div class="stat-box">
                <div class="label">Total Sampah Diterima</div>
                <div class="val">{{ number_format($totalVolumeKg, 1, ',', '.') }} kg</div>
                <div style="font-size: 8pt; color: #64748b;">({{ number_format($totalVolumeTon, 3, ',', '.') }} Ton)</div>
            </div>
            <div class="stat-box">
                <div class="label">Organik (Olah Kompos)</div>
                <div class="val">{{ $organikPct }}%</div>
                <div style="font-size: 8pt; color: #64748b;">{{ number_format($organikKg, 1, ',', '.') }} kg</div>
            </div>
            <div class="stat-box">
                <div class="label">Anorganik (Daur Ulang)</div>
                <div class="val">{{ $anorganikPct }}%</div>
                <div style="font-size: 8pt; color: #64748b;">{{ number_format($anorganikKg, 1, ',', '.') }} kg</div>
            </div>
            <div class="stat-box">
                <div class="label">Utilisasi Kapasitas</div>
                <div class="val">{{ $persenUtilisasi }}%</div>
                <div style="font-size: 8pt; color: #64748b;">Rerata {{ number_format($rataRataHarianKg, 1, ',', '.') }} kg/hari</div>
            </div>
        </div>

        <!-- Section 2: Breakdown Sumber Sampah -->
        <h4 class="section-heading">II. Distribusi Penerimaan Berdasarkan Sumber Sampah</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 45%;">Sumber Sampah</th>
                    <th style="width: 15%;">Jumlah Rit / Antrean</th>
                    <th style="width: 20%;">Total Tonase (Kg)</th>
                    <th style="width: 15%;">Kontribusi (%)</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($sumberBreakdown as $srcKey => $src)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td><strong>{{ $src['nama'] }}</strong></td>
                    <td class="text-center">{{ $src['count'] }} kali</td>
                    <td class="text-right">{{ number_format($src['total_kg'], 1, ',', '.') }}</td>
                    <td class="text-center">{{ $src['percentage'] }}%</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td colspan="2" class="text-center">TOTAL PENERIMAAN</td>
                    <td class="text-center">{{ $totalSetoranCount }} rit</td>
                    <td class="text-right">{{ number_format($totalVolumeKg, 1, ',', '.') }}</td>
                    <td class="text-center">100.0%</td>
                </tr>
            </tfoot>
        </table>

        <!-- Section 3: Buku Ekspedisi Setoran -->
        <h4 class="section-heading">III. Buku Ekspedisi Setoran Masuk ke TPS 3R</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 16%;">No. Resi</th>
                    <th style="width: 13%;">Tanggal & Jam</th>
                    <th style="width: 16%;">Petugas Pengantar</th>
                    <th style="width: 15%;">Sumber</th>
                    <th style="width: 12%;">Timbangan (Kg)</th>
                    <th style="width: 14%;">Hasil Pemilahan</th>
                    <th style="width: 10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allDeposits->take(15) as $index => $dep)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><code>{{ $dep->nomor_resi }}</code></td>
                    <td class="text-center">{{ $dep->tanggal_setor }} {{ $dep->waktu_setor ? substr($dep->waktu_setor, 0, 5) : '' }}</td>
                    <td>{{ $dep->petugas->name ?? 'Petugas Lapangan' }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $dep->sumber_sampah)) }}</td>
                    <td class="text-right"><strong>{{ number_format((float) $dep->berat_kg, 1, ',', '.') }}</strong></td>
                    <td style="font-size: 8pt;">
                        O: {{ number_format((float)($dep->berat_organik_pilah_kg ?? 0), 1) }} |
                        A: {{ number_format((float)($dep->berat_anorganik_pilah_kg ?? 0), 1) }} |
                        R: {{ number_format((float)($dep->berat_residu_pilah_kg ?? 0), 1) }}
                    </td>
                    <td class="text-center">
                        <strong style="color: {{ $dep->status === 'diterima' ? '#047857' : ($dep->status === 'menunggu_verifikasi' ? '#b45309' : '#b91c1c') }};">
                            {{ strtoupper($dep->status) }}
                        </strong>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 12px; color: #64748b;">Belum ada catatan setoran sampah pada rentang periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Section 4: Pengesahan -->
        <div class="signature-section">
            <div class="signature-box">
                <p>Petugas Penimbangan & Pemilahan,</p>
                <div class="signature-space"></div>
                <p class="signature-name">PETUGAS OPERASIONAL</p>
                <p>TPS 3R {{ $profile->nama_tps3r ?? 'Kota Madiun' }}</p>
            </div>

            <div class="signature-box">
                <p>Kota Madiun, {{ now()->translatedFormat('d F Y') }}</p>
                <p>Koordinator Pengelola TPS 3R,</p>
                <div class="signature-space"></div>
                <p class="signature-name">{{ auth()->user()->name ?? 'PENGELOLA TPS3R' }}</p>
                <p>KSM / Pengelola Berizin DLH</p>
            </div>
        </div>
    </div>

</body>
</html>
