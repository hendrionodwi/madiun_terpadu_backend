<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi Timbulan Sampah Kota Madiun - DLH</title>
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

        /* Toolbar Layar */
        .toolbar {
            width: 210mm;
            margin: 0 auto 16px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #047857;
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

        .btn-print {
            background: #ffffff;
            color: #065f46;
        }
        .btn-print:hover { background: #e6f4ea; }

        .btn-csv {
            background: #0284c7;
            color: #ffffff;
        }
        .btn-csv:hover { background: #0369a1; }

        .btn-back {
            background: transparent;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
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

        .kop-text h2 {
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .kop-text h1 {
            font-size: 17pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
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
            color: #065f46;
            margin-top: 2px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 14px;
        }

        table.data-table th, table.data-table td {
            border: 1px solid #64748b;
            padding: 5px 8px;
        }

        table.data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
        }

        table.data-table td.text-right { text-align: right; }
        table.data-table td.text-center { text-align: center; }

        /* TANDA TANGAN */
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
            body {
                background: none;
                padding: 0;
            }
            .paper {
                box-shadow: none;
                padding: 0;
                width: 100%;
                min-height: auto;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 15mm;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar Layar Non-Print -->
    <div class="toolbar no-print">
        <div>
            <strong>Laporan Resmi DLH Kota Madiun</strong>
            <span style="opacity: 0.8; font-size: 12px; margin-left: 8px;">(Pratinjau Cetak / PDF)</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="btn-print" onclick="window.print()">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak / Simpan PDF
            </button>
            <a href="{{ route('dashboard.admin.reports.export', ['format' => 'csv', 'start_date' => $startDate, 'end_date' => $endDate, 'kecamatan' => $kecamatanFilter]) }}" class="btn-csv">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Unduh CSV / Excel
            </a>
            <a href="{{ route('dashboard.admin') }}" class="btn-back">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="paper">
        <!-- Kop Surat Resmi -->
        <div class="kop-header">
            <svg class="kop-logo" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="46" stroke="#065F46" stroke-width="4" fill="#F0FDF4"/>
                <path d="M50 16L76 34V66L50 84L24 66V34L50 16Z" fill="#047857" stroke="#064E3B" stroke-width="2"/>
                <circle cx="50" cy="50" r="18" fill="#FDE047" stroke="#CA8A04" stroke-width="2"/>
                <path d="M42 50L48 56L60 44" stroke="#15803D" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <div class="kop-text">
                <h2>Pemerintah Kota Madiun</h2>
                <h1>Dinas Lingkungan Hidup</h1>
                <p>Jalan Salak No. 51, Kecamatan Taman, Kota Madiun, Jawa Timur 63131</p>
                <p>Telepon: (0351) 464223 | Laman: dlh.madiunkota.go.id | Pos-el: dlh@madiunkota.go.id</p>
            </div>
        </div>

        <!-- Judul Laporan -->
        <div class="doc-title">
            <h3>Laporan Rekapitulasi Timbulan & Neraca Pengelolaan Sampah Kota Madiun</h3>
            <p>Nomor Dokumen: {{ $docNumber }}</p>
            <p>Periode Pelaporan: <strong>{{ \Illuminate\Support\Carbon::parse($startDate)->translatedFormat('d F Y') }}</strong> s/d <strong>{{ \Illuminate\Support\Carbon::parse($endDate)->translatedFormat('d F Y') }}</strong></p>
        </div>

        <!-- Section 1: Ringkasan Makro & Kinerja -->
        <h4 class="section-heading">I. Indikator Kinerja & Neraca Pengelolaan Sampah Kota</h4>
        <div class="stat-grid">
            <div class="stat-box">
                <div class="label">Total Sampah Terkelola</div>
                <div class="val">{{ number_format($totalSampahTerkelolaKg, 1, ',', '.') }} kg</div>
                <div style="font-size: 8pt; color: #64748b;">({{ number_format($totalSampahTerkelolaTon, 3, ',', '.') }} Ton)</div>
            </div>
            <div class="stat-box">
                <div class="label">Porsi Kompos Organik</div>
                <div class="val">{{ $composition['organik']['percentage'] }}%</div>
                <div style="font-size: 8pt; color: #64748b;">{{ number_format($composition['organik']['weight'], 1, ',', '.') }} kg</div>
            </div>
            <div class="stat-box">
                <div class="label">Porsi Daur Ulang</div>
                <div class="val">{{ $composition['anorganik_daur_ulang']['percentage'] }}%</div>
                <div style="font-size: 8pt; color: #64748b;">{{ number_format($composition['anorganik_daur_ulang']['weight'], 1, ',', '.') }} kg</div>
            </div>
            <div class="stat-box">
                <div class="label">Residu Masuk TPA</div>
                <div class="val">{{ $composition['residu']['percentage'] }}%</div>
                <div style="font-size: 8pt; color: #64748b;">{{ number_format($composition['residu']['weight'], 1, ',', '.') }} kg</div>
            </div>
        </div>

        <!-- Section 2: Rekap Wilayah Kecamatan -->
        <h4 class="section-heading">II. Rekapitulasi per Wilayah Kecamatan Kota Madiun</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 25%;">Kecamatan</th>
                    <th style="width: 18%;">Timbangan Warga (Kg)</th>
                    <th style="width: 18%;">Setoran TPS3R (Kg)</th>
                    <th style="width: 18%;">Total Akumulasi (Kg)</th>
                    <th style="width: 16%;">Pengaduan Teratasi</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($wilayahSummary as $w)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td><strong>{{ $w['nama'] }}</strong></td>
                    <td class="text-right">{{ number_format($w['timbangan_warga_kg'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($w['setoran_tps3r_kg'], 1, ',', '.') }}</td>
                    <td class="text-right"><strong>{{ number_format($w['total_sampah_kg'], 1, ',', '.') }}</strong></td>
                    <td class="text-center">{{ $w['total_aduan_selesai'] }} / {{ $w['total_aduan'] }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td colspan="2" class="text-center">TOTAL KOTA MADIUN</td>
                    <td class="text-right">{{ number_format($totalTimbanganKg, 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totalSetoranTps3rKg, 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totalSampahTerkelolaKg, 1, ',', '.') }}</td>
                    <td class="text-center">{{ $totalAduanSelesai }} / {{ $totalAduan }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Section 3: Data Log Setoran Terkini -->
        <h4 class="section-heading">III. Sampel Catatan Log Pengiriman Armada & TPS3R</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 17%;">No. Resi</th>
                    <th style="width: 14%;">Tanggal & Jam</th>
                    <th style="width: 22%;">TPS3R Tujuan</th>
                    <th style="width: 16%;">Petugas</th>
                    <th style="width: 14%;">Jenis</th>
                    <th style="width: 12%;">Berat (Kg)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allDeposits->take(12) as $index => $dep)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><code>{{ $dep->nomor_resi }}</code></td>
                    <td class="text-center">{{ $dep->tanggal_setor }} {{ $dep->waktu_setor ? substr($dep->waktu_setor, 0, 5) : '' }}</td>
                    <td>{{ $dep->tps3rProfile->nama_tps3r ?? 'TPS3R Madiun' }}</td>
                    <td>{{ $dep->petugas->name ?? 'Petugas' }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $dep->jenis_sampah)) }}</td>
                    <td class="text-right"><strong>{{ number_format((float) $dep->berat_kg, 1, ',', '.') }}</strong></td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 12px; color: #64748b;">Belum ada log setoran tercatat pada rentang tanggal ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Section 4: Pengesahan -->
        <div class="signature-section">
            <div class="signature-box">
                <p>Mengetahui / Memeriksa,</p>
                <p><strong>Pengelola Data Persampahan</strong></p>
                <div class="signature-space"></div>
                <p class="signature-name">{{ auth()->user()->name ?? 'Administrator DLH' }}</p>
                <p>NIP. 19880415 201101 1 008</p>
            </div>

            <div class="signature-box">
                <p>Kota Madiun, {{ now()->translatedFormat('d F Y') }}</p>
                <p><strong>Kepala Dinas Lingkungan Hidup Kota Madiun</strong></p>
                <div class="signature-space"></div>
                <p class="signature-name">drh. ZAINAL ARIFIN</p>
                <p>Pembina Utama Muda</p>
                <p>NIP. 19750812 199903 1 004</p>
            </div>
        </div>
    </div>

</body>
</html>
