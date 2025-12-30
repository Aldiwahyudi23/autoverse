<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <table class="header-table" style="margin-bottom: 20px; width: 100%;">
        <tr>
            <td style="width: 33%;">
                {{-- <h1 style="font-size: 24px; margin: 0; line-height: 1.2;">
                    <span style="color: #6bdb67;">Auto</span><span style="color: #000000;">Verse</span>
                </h1> --}}
                <img src="{{ public_path('images/logo/Autoverse.png') }}"
                    alt="Logo Autoverse"
                    style="width: 140px; height: auto;">

            </td>
            <td style="width: 34%; text-align: center;">
                <h1 style="font-size: 18px; margin: 0; font-weight: bold;">Laporan Hasil Inspeksi</h1>
            </td>
            <td style="width: 33%; text-align: right; color: #555;">
                {{-- <p style="margin: 0; font-size: 12px;">Tanggal: {{ \Carbon\Carbon::parse($inspection->inspection_date)->format('d-m-Y') }}</p>
                <p style="margin: 0; font-size: 12px;">Waktu: {{ \Carbon\Carbon::parse($inspection->inspection_date)->format('H:i:s') }}</p> --}}
            </td>
        </tr>
    </table>
    
    @php
        $conclusionSettings = $inspection->settings['conclusion'] ?? [];
        $flooded = $conclusionSettings['flooded'] ?? 'no';
        $collision = $conclusionSettings['collision'] ?? 'no';
        $collisionSeverity = $conclusionSettings['collision_severity'] ?? '';
    @endphp

    <table class="main-info-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        @if ($inspection->car_id)
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Nomor Polisi</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $inspection->plate_number }}</td>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">ID Order</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $inspection->order_code }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Merek</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $inspection->car->brand->name }}</td>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Tanggal Inspeksi</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ \Carbon\Carbon::parse($inspection->inspection_date)->format('d-m-Y H:i:s') }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Model</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $inspection->car->model->name }}</td>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Odo Meter</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: KM {{ number_format($dataCarOther['jarakTempuh(KM)']['note'] ,0,',','.') ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Tipe</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $inspection->car->type->name }}</td>
               <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Pajak 1 Tahunan</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $dataCarOther['pajakTahunan']['note'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">CC</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ number_format($inspection->car->cc ,0,',','.') }}</td>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Pajak 5 Tahunan</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $dataCarOther['pajak5Tahunan']['note'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Bahan Bakar</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $inspection->car->fuel_type }}</td>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Pajak Tahunan</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: RP {{ number_format($dataCarOther['pKB']['note'] ,0,',','.') ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Transmisi</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $inspection->car->transmission }}</td>
                 <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Kepemilikan</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $dataCarOther['kepemilikan']['status'] ?? '' }} {{ $dataCarOther['kepemilikan']['note'] ?? '' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Tahun Pembuatan</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $inspection->car->year }}</td>
                 <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Buku Manual</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $dataCarOther['bSBM']['status'] ?? '' }} {{ $dataCarOther['bSBM']['note'] ?? '' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Periode Model</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $inspection->car->production_period ?? '-' }}</td>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;"></td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;"></td>
            </tr>
        @endif
    </table>

    <table style="width: 100%; margin: 15px 0; border-collapse: collapse; text-align: center;">
        <tr>
            <td style="width: 50%; padding: 6px;">
                @if ($flooded == 'yes')
                    <img src="{{ public_path('images/icons/banjir.png') }}" alt="Banjir" style="width: 80px; height: 80px;">
                    <p style="font-weight: bold; font-size: 15px; color: #dc3545;">Bekas Banjir</p>
                @else
                    <img src="{{ public_path('images/icons/aman.png') }}" alt="Aman" style="width: 80px; height: 80px;">
                    <p style="font-weight: bold; font-size: 15px; color: #28a745;">Bebas Banjir</p>
                @endif
            </td>
            <td style="width: 50%; padding: 6px;">
                @if ($collision == 'yes')
                    @php
                        $collisionImage = public_path('images/icons/ringan.png');
                        $collisionText = 'Tabrak Ringan';
                        $collisionColor = '#ff6f00';
                        if ($collisionSeverity === 'moderate') {
                            $collisionImage = public_path('images/icons/beruntun.png');
                            $collisionText = 'Tabrak Beruntun';
                            $collisionColor = '#fd7e14';
                        } elseif ($collisionSeverity === 'heavy') {
                            $collisionImage = public_path('images/icons/berat.png');
                            $collisionText = 'Tabrak Berat';
                            $collisionColor = '#dc3545';
                        }
                    @endphp
                    <img src="{{ $collisionImage }}" alt="Tabrak" style="width: 80px; height: 80px;">
                    <p style="font-weight: bold; font-size: 15px; color: {{ $collisionColor }};">{{ $collisionText }}</p>
                @else
                    <img src="{{ public_path('images/icons/aman.png') }}" alt="Aman" style="width: 80px; height: 80px;">
                    <p style="font-weight: bold; font-size: 15px; color: #28a745;">Bebas Tabrak</p>
                @endif
            </td>
        </tr>
    </table>

    @if($inspection->notes)
        <div style="margin: 15px 0; padding: 12px; background: #f9f9f9; border-left: 4px solid #28a745;">
            <h3 style="margin: 0 0 6px; font-size: 15px;">Kesimpulan Inspeksi:</h3>
            <div style="margin: 0; font-size: 12px; line-height: 1.8;">{!! $inspection->notes !!}</div>
        </div>
    @endif

{{-- ================================================================ --}}
{{-- STEP 1: REGROUP DATA SEBELUM RENDER UTAMA --}}
{{-- ================================================================ --}}
@php
   $groupedMenuPoints = $menu_points
            ->sortBy(function ($point) {
                $component = $point->inspection_point->component;
                return $component->order ?? $component->created_at;
            })
            ->groupBy(function ($point) {
                return $point->inspection_point->component->id ?? 0;
            });
    // Definisikan komponen-komponen yang akan digabung ke Interior
    $interiorSubComponents = ['Kelistrikan', 'AC', 'Fitur'];

    // Definisikan urutan komponen untuk sorting dalam Interior yang digabung
    $componentOrder = [
        'Interior' => 1,
        'Kelistrikan' => 2,
        'AC' => 3,
        'Fitur' => 4,
        'Interior (Validasi Banjir)' => 5,
    ];

    // Definisikan urutan komponen untuk sorting dalam Eksterior yang digabung
    $exteriorComponentOrder = [
        'Eksterior' => 1,
        'Rangka (Validasi Tabrak)' => 2,
    ];
    
    // Array untuk menampung data yang sudah diregroup
    $regroupedData = [];
    
    // Pertama, kita kumpulkan dulu semua data asli berdasarkan nama komponen
    $componentsByName = [];
    foreach ($groupedMenuPoints as $componentId => $points) {
        $componentName = optional($points->first()->inspection_point->component)->name ?? 'Tanpa Komponen';
        $componentsByName[$componentName] = $points;
    }
    
    // Sekarang proses regrouping
    foreach ($componentsByName as $componentName => $points) {
        // Urutkan points berdasarkan order atau created_at
        $sortedPoints = $points->sortBy(function ($point) {
            return $point->order ?? $point->created_at;
        });
        
        // ============================================
        // LOGIKA REGROUPING UTAMA
        // ============================================
        
        // 1. RANGKA (VALIDASI TABRAK) - jika NO, gabung ke Eksterior
        if ($componentName == 'Rangka (Validasi Tabrak)' && $collision == 'no') {
            // Pastikan Eksterior ada di $regroupedData
            if (!isset($regroupedData['Eksterior'])) {
                // Cari komponen Eksterior asli, jika ada
                $eksteriorPoints = $componentsByName['Eksterior'] ?? collect();
                $regroupedData['Eksterior'] = $eksteriorPoints->merge($sortedPoints);
            } else {
                $regroupedData['Eksterior'] = $regroupedData['Eksterior']->merge($sortedPoints);
            }
            // Tandai bahwa Rangka sudah diproses (tidak akan diproses lagi)
            continue;
        }
        
        // 2. INTERIOR (VALIDASI BANJIR) - jika NO, gabung ke Interior
        if ($componentName == 'Interior (Validasi Banjir)' && $flooded == 'no') {
            // Pastikan Interior ada di $regroupedData
            if (!isset($regroupedData['Interior'])) {
                // Cari komponen Interior asli, jika ada
                $interiorPoints = $componentsByName['Interior'] ?? collect();
                $regroupedData['Interior'] = $interiorPoints->merge($sortedPoints);
            } else {
                $regroupedData['Interior'] = $regroupedData['Interior']->merge($sortedPoints);
            }
            // Tandai bahwa Interior Banjir sudah diproses
            continue;
        }
        
        // 3. KOMPONEN YANG DIGABUNG KE INTERIOR (Kelistrikan, AC, Fitur)
        if (in_array($componentName, $interiorSubComponents)) {
            // Selalu gabungkan ke Interior
            if (!isset($regroupedData['Interior'])) {
                // Cari komponen Interior asli, jika ada
                $interiorPoints = $componentsByName['Interior'] ?? collect();
                $regroupedData['Interior'] = $interiorPoints->merge($sortedPoints);
            } else {
                $regroupedData['Interior'] = $regroupedData['Interior']->merge($sortedPoints);
            }
            // Tandai bahwa komponen sudah diproses
            continue;
        }
        
        // 4. KOMPONEN YANG TETAP TERPISAH
        // Rangka (Validasi Tabrak) jika YES - tetap terpisah
        if ($componentName == 'Rangka (Validasi Tabrak)' && $collision == 'yes') {
            $regroupedData[$componentName] = $sortedPoints;
            continue;
        }
        
        // Interior (Validasi Banjir) jika YES - tetap terpisah
        if ($componentName == 'Interior (Validasi Banjir)' && $flooded == 'yes') {
            $regroupedData[$componentName] = $sortedPoints;
            continue;
        }
        
        // 5. EKSTERIOR dan INTERIOR ASLI (jika belum diatur di atas)
        if ($componentName == 'Eksterior') {
            if (!isset($regroupedData['Eksterior'])) {
                $regroupedData['Eksterior'] = $sortedPoints;
            }
            continue;
        }
        
        if ($componentName == 'Interior') {
            if (!isset($regroupedData['Interior'])) {
                $regroupedData['Interior'] = $sortedPoints;
            }
            continue;
        }
        
        // 6. KOMPONEN LAINNYA (Dokumen, Foto Kendaraan, dll)
        $regroupedData[$componentName] = $sortedPoints;
    }
    
    // Urutkan ulang semua points dalam setiap komponen (setelah merge)
    foreach ($regroupedData as $compName => $compPoints) {
        if ($compName === 'Interior') {
            // Untuk Interior yang sudah digabung, urutkan berdasarkan component order terlebih dahulu
            $regroupedData[$compName] = $compPoints->sortBy(function ($point) use ($componentOrder) {
                $componentName = optional($point->inspection_point->component)->name ?? '';
                $compOrder = $componentOrder[$componentName] ?? 999;
                return $compOrder * 1000 + ($point->order ?? $point->created_at);
            })->values(); // Reset keys
        } elseif ($compName === 'Eksterior') {
            // Untuk Eksterior yang sudah digabung, urutkan berdasarkan exterior component order terlebih dahulu
            $regroupedData[$compName] = $compPoints->sortBy(function ($point) use ($exteriorComponentOrder) {
                $componentName = optional($point->inspection_point->component)->name ?? '';
                $compOrder = $exteriorComponentOrder[$componentName] ?? 999;
                return $compOrder * 1000 + ($point->order ?? $point->created_at);
            })->values(); // Reset keys
        } else {
            $regroupedData[$compName] = $compPoints->sortBy(function ($point) {
                return $point->order ?? $point->created_at;
            })->values(); // Reset keys
        }
    }
    
    // Simpan data yang sudah diregroup ke variabel baru untuk loop utama
    $finalGroupedMenuPoints = $regroupedData;
@endphp

{{-- ================================================================ --}}
{{-- STEP 2: LOOP UTAMA DENGAN DATA YANG SUDAH DIREGROUP --}}
{{-- ================================================================ --}}
@foreach($finalGroupedMenuPoints as $componentName => $points)
    @php
        // ============================================
        // LOGIKA UNTUK MENENTUKAN APAKAH KOMPONEN MEMILIKI DATA
        // ============================================
        $hasData = false;
        $hasImagesForComponent = false; // Flag untuk cek apakah komponen memiliki gambar
        
        // 1. KOMPONEN KHUSUS YANG SELALU DITAMPILKAN JIKA ADA DATANYA
        if (in_array($componentName, ['Eksterior', 'Interior'])) {
            // Untuk komponen utama yang sudah digabung, cek apakah ada data hasil
            foreach($points as $point) {
                $result = $point->inspection_point->results->first();
                $hasResult = $result && (!empty($result->status) || !empty($result->note));
                $hasImage = $point->inspection_point->images && $point->inspection_point->images->isNotEmpty();
                
                if ($hasResult) {
                    $hasData = true;
                }
                if ($hasImage) {
                    $hasImagesForComponent = true;
                }
                if ($hasData && $hasImagesForComponent) {
                    break;
                }
            }
        } 
        // 2. RANGKA (VALIDASI TABRAK) - hanya jika YES
        elseif ($componentName == 'Rangka (Validasi Tabrak)') {
            $hasData = ($collision == 'yes');
            // Cek apakah ada gambar untuk komponen ini
            foreach($points as $point) {
                if($point->inspection_point->images && $point->inspection_point->images->isNotEmpty()) {
                    $hasImagesForComponent = true;
                    break;
                }
            }
        }
        // 3. INTERIOR (VALIDASI BANJIR) - hanya jika YES
        elseif ($componentName == 'Interior (Validasi Banjir)') {
            $hasData = ($flooded == 'yes');
            // Cek apakah ada gambar untuk komponen ini
            foreach($points as $point) {
                if($point->inspection_point->images && $point->inspection_point->images->isNotEmpty()) {
                    $hasImagesForComponent = true;
                    break;
                }
            }
        }
        // 4. FOTO KENDARAAN - cek gambar
        elseif ($componentName == 'Foto Kendaraan') {
            $hasData = $points->flatMap(fn($p) => $p->inspection_point->images)->isNotEmpty();
            $hasImagesForComponent = $hasData;
        }
        // 5. DOKUMEN - cek hasil ATAU gambar, tapi dengan logika khusus
        elseif ($componentName == 'Dokumen') {
            foreach ($points as $point) {
                $result = $point->inspection_point->results->first();
                $hasResult = $result && (!empty($result->status) || !empty($result->note));
                $hasImage = $point->inspection_point->images && $point->inspection_point->images->count() > 0;
                
                // PERUBAHAN: Jika input_type adalah 'image', skip dari tabel
                // Tapi komponen tetap dianggap punya data jika ada gambar
                if ($point->input_type === 'image') {
                    if ($hasImage) {
                        $hasData = true;
                        $hasImagesForComponent = true;
                        break;
                    }
                } else {
                    if ($hasResult || $hasImage) {
                        $hasData = true;
                        if ($hasImage) $hasImagesForComponent = true;
                        break;
                    }
                }
            }
        }
        // 6. KOMPONEN LAINNYA - cek hasil dan gambar
        else {
            foreach($points as $point) {
                $result = $point->inspection_point->results->first();
                $hasResult = $result && (!empty($result->status) || !empty($result->note));
                $hasImage = $point->inspection_point->images && $point->inspection_point->images->isNotEmpty();
                
                if ($hasResult) {
                    $hasData = true;
                }
                if ($hasImage) {
                    $hasImagesForComponent = true;
                }
                if ($hasData && $hasImagesForComponent) {
                    break;
                }
            }
        }
    @endphp

    {{-- ============================================ --}}
    {{-- RENDER KOMPONEN JIKA ADA DATA --}}
    {{-- ============================================ --}}
    @if($hasData || $hasImagesForComponent)
        {{-- Page break untuk komponen khusus --}}
        @if(in_array($componentName, ['Dokumen', 'Foto Kendaraan', 'Rangka (Validasi Tabrak)', 'Interior (Validasi Banjir)']))
            <div style="page-break-before: always;"></div>
        @endif
        
        {{-- Container utama komponen --}}
        <div class="section-box" style="border: 1px solid #28a745; border-radius: 10px 10px 0 0; margin-bottom: 20px; overflow: hidden;">
            {{-- Header komponen --}}
            <div style="font-size: 16px; font-weight: bold; background: #28a745; padding: 8px; border-bottom: 1px solid #218838; color: white; position: relative;">
                {{ $componentName ?? 'Tanpa Komponen' }}
            </div>

            {{-- Konten komponen --}}
            <div style="padding: 10px;">
                {{-- ============================================ --}}
                {{-- IKON KHUSUS UNTUK KOMPONEN VALIDASI --}}
                {{-- ============================================ --}}
                
                @if ($componentName == 'Interior (Validasi Banjir)')
                    {{-- Interior Banjir hanya muncul jika flooded == 'yes' --}}
                    <div style="text-align: center; margin-bottom: 15px;">
                        @if ($flooded === 'yes')
                            <img src="{{ public_path('images/icons/banjir.png') }}" alt="Banjir" style="width: 80px; height: 80px;">
                            <p style="font-weight: bold; font-size: 15px; color: #dc3545;">Bekas Banjir</p>
                        @endif
                    </div>
                @endif
                
                @if ($componentName == 'Rangka (Validasi Tabrak)')
                    {{-- Rangka hanya muncul jika collision == 'yes' --}}
                    <div style="text-align: center; margin-bottom: 15px;">
                        @if ($collision == 'yes')
                            @php
                                $collisionImage = public_path('images/icons/ringan.png');
                                $collisionText = 'Tabrak Ringan';
                                $collisionColor = '#ff6f00';
                                if ($collisionSeverity === 'moderate') {
                                    $collisionImage = public_path('images/icons/beruntun.png');
                                    $collisionText = 'Tabrak Beruntun';
                                    $collisionColor = '#fd7e14';
                                } elseif ($collisionSeverity === 'heavy') {
                                    $collisionImage = public_path('images/icons/berat.png');
                                    $collisionText = 'Tabrak Berat';
                                    $collisionColor = '#dc3545';
                                }
                            @endphp
                            <img src="{{ $collisionImage }}" alt="Tabrak" style="width: 80px; height: 80px;">
                            <p style="font-weight: bold; font-size: 15px; color: {{ $collisionColor }};">{{ $collisionText }}</p>
                        @endif
                    </div>
                @endif
                
                {{-- ============================================ --}}
                {{-- LOGIKA RENDER BERDASARKAN JENIS KOMPONEN --}}
                {{-- ============================================ --}}
                
                {{-- TAMPILAN KHUSUS UNTUK DOKUMEN --}}
                @if($componentName == 'Dokumen')
                    @php
                        $displayPoints = [];
                        $hasAnyDataForTable = false; // Flag untuk mengecek apakah ada data untuk tabel
                        
                        foreach($points as $point) {
                            $result = $point->inspection_point->results->first();
                            $hasResult = $result && (!empty($result->status) || !empty($result->note));
                            $hasImage = $point->inspection_point->images && $point->inspection_point->images->count() > 0;

                            // PERUBAHAN: Skip point dengan input_type 'image' dari tabel
                            if ($point->input_type === 'image') {
                                continue; // Lewati dari tabel, tapi gambar tetap akan ditampilkan nanti
                            }

                            // Skip points yang ada di carOtherNames (data sudah ditampilkan di tabel utama)
                            $pointName = $point->inspection_point->name ?? '';
                            if (in_array($pointName, $carOtherNames ?? [])) {
                                continue; // Lewati dari tabel dokumen
                            }

                            if (!$hasResult && !$hasImage) {
                                continue;
                            }

                            $displayPoints[] = [
                                'point' => $point,
                                'result' => $result,
                                'hasResult' => $hasResult,
                                'hasImage' => $hasImage,
                            ];
                            $hasAnyDataForTable = true;
                        }

                        $totalPoints = count($displayPoints);
                        $half = ceil($totalPoints / 2);
                        $leftPoints = array_slice($displayPoints, 0, $half);
                        $rightPoints = array_slice($displayPoints, $half);
                        $maxRows = max(count($leftPoints), count($rightPoints));
                    @endphp

                    {{-- Tabel untuk data Result dokumen --}}
                    {{-- Hanya tampilkan jika ada data untuk tabel --}}
                    @if($hasAnyDataForTable && $totalPoints > 0)
                        <table class="main-info-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                            @for($i = 0; $i < $maxRows; $i++)
                                <tr>
                                    <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">
                                        @if(isset($leftPoints[$i]))
                                            {{ $leftPoints[$i]['point']->inspection_point->name ?? '-' }}
                                        @endif
                                    </td>
                                    <td style="padding: 6px 8px; font-size: 12px; vertical-align: top; width: 35%;">
                                        @if(isset($leftPoints[$i]))
                                            : 
                                            @php
                                                $pointData = $leftPoints[$i];
                                                $result = $pointData['result'];
                                                $formattedNote = $result->note ?? '';
                                            @endphp
                                            
                                            @if(!empty($formattedNote))
                                                {{ $formattedNote }}
                                            @elseif(!empty($result->status))
                                                {{ $result->status }}
                                            @endif
                                        @endif
                                    </td>
                                    
                                    <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">
                                        @if(isset($rightPoints[$i]))
                                            {{ $rightPoints[$i]['point']->inspection_point->name ?? '-' }}
                                        @endif
                                    </td>
                                    <td style="padding: 6px 8px; font-size: 12px; vertical-align: top; width: 35%;">
                                        @if(isset($rightPoints[$i]))
                                            : 
                                            @php
                                                $pointData = $rightPoints[$i];
                                                $result = $pointData['result'];
                                                $formattedNote = $result->note ?? '';
                                            @endphp
                                            
                                            @if(!empty($formattedNote))
                                                {{ $formattedNote }}
                                            @elseif(!empty($result->status))
                                                {{ $result->status }}
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endfor
                        </table>
                    @endif

                    {{-- Gambar untuk dokumen --}}
                    {{-- Termasuk gambar dari point dengan input_type 'image' --}}
                    @php
                        $allDocImages = [];
                        
                        foreach($points as $point) {
                            // PERUBAHAN: Ambil semua gambar, termasuk dari point dengan input_type 'image'
                            if($point->inspection_point->images && $point->inspection_point->images->isNotEmpty()) {
                                $allDocImages = array_merge($allDocImages, $point->inspection_point->images->all());
                            }
                        }

                        $columns = 4;
                        $imageChunks = array_chunk($allDocImages, $columns);
                    @endphp
                    
                    @if(count($imageChunks) > 0)
                        @foreach($imageChunks as $chunk)
                            <table class="image-table" style="margin-bottom: 20px; width: 100%; border-collapse: collapse; table-layout: fixed;">
                                <tr>
                                    @foreach($chunk as $img)
                                        <td style="width: 25%; vertical-align: top; padding: 0 2px 10px 2px; text-align: center;">
                                            <div style="margin-bottom: 5px; height: 35px; overflow: hidden;">
                                                <span style="font-size: 12px; font-weight: bold; color: #333; line-height: 1.2; display: block; text-align: center;">
                                                    {{ $img->point->name ?? 'Untitled' }}
                                                </span>
                                            </div>

                                            <div style="text-align: center; width: 100%;">
                                                @if($img->image_path && file_exists(public_path($img->image_path)))
                                                    <div style="border: 1px solid #ddd; padding: 3px; background-color: #fff; border-radius: 3px; display: inline-block;">
                                                        <img src="{{ public_path($img->image_path) }}" 
                                                            alt="{{ $img->point->name }}" 
                                                            style="width: 150px; height: auto;">
                                                    </div>
                                                @else
                                                    <div style="border: 1px dashed #ccc; padding: 15px; border-radius: 3px; background-color: #f9f9f9; width: 140px; margin: 0 auto;">
                                                        <span style="font-size: 10px; color: #888;">Gambar tidak ditemukan</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach

                                    {{-- Kolom Kosong --}}
                                    @for($i = count($chunk); $i < $columns; $i++)
                                        <td style="width: 25%; vertical-align: top; padding: 0 5px 40px 5px;">
                                            <div style="height: 35px;"></div>
                                            <div style="border: 1px dashed #eee; padding: 15px; border-radius: 3px; background-color: #fafafa; width: 140px; margin: 0 auto;">
                                                <span style="font-size: 10px; color: #ccc;">Kosong</span>
                                            </div>
                                        </td>
                                    @endfor
                                </tr>
                            </table>
                        @endforeach
                    @endif
                
                {{-- TAMPILAN KHUSUS UNTUK FOTO KENDARAAN --}}
                @elseif($componentName == 'Foto Kendaraan')
                    @php
                        $allCarImages = [];
                        
                        // 1. Ambil foto asli dari komponen Foto Kendaraan itu sendiri
                        foreach($points as $point) {
                            if($point->inspection_point->images) {
                                $allCarImages = array_merge($allCarImages, $point->inspection_point->images->all());
                            }
                        }

                        // 2. Ambil foto tambahan dari komponen khusus dengan status valid
                        // PERHATIAN: Gunakan $componentsByName (data asli) bukan $finalGroupedMenuPoints
                        $targetExtra = ['Rangka (Validasi Tabrak)', 'Interior (Validasi Banjir)'];
                        $validStatuses = ['ok', 'normal', 'good', 'baik'];

                        foreach($targetExtra as $compName) {
                            if (isset($componentsByName[$compName])) {
                                foreach($componentsByName[$compName] as $p) {
                                    $result = $p->inspection_point->results->first();
                                    $status = strtolower($result->status ?? '');
                                    
                                    // LOGIKA BARU: 
                                    // - Jika bebas banjir (flooded == 'no') tapi ada catatan selain ok, gambar masuk ke Interior
                                    // - Jika bukan tabrak (collision == 'no') tapi ada catatan selain ok, gambar masuk ke Eksterior
                                    // - Jika status ok, gambar tetap di Foto Kendaraan
                                    if($p->inspection_point->images && $p->inspection_point->images->isNotEmpty()) {
                                        if (in_array($status, $validStatuses)) {
                                            // Status OK - gambar masuk ke Foto Kendaraan
                                            $allCarImages = array_merge($allCarImages, $p->inspection_point->images->all());
                                        } else {
                                            // Status bukan OK - gambar TIDAK masuk ke Foto Kendaraan
                                            // Gambar akan ditampilkan di komponen asalnya (Interior/Eksterior)
                                        }
                                    }
                                }
                            }
                        }

                        // 3. Setup Grid (4 kolom)
                        $columns = 4;
                        $imageChunks = array_chunk($allCarImages, $columns);
                    @endphp
                    
                    @if(count($imageChunks) > 0)
                        @foreach($imageChunks as $chunk)
                            <table class="image-table" style="margin-bottom: 20px; width: 100%; border-collapse: collapse; table-layout: fixed;">
                                <tr>
                                    @foreach($chunk as $img)
                                        <td style="width: 25%; vertical-align: top; padding: 0 2px 10px 2px; text-align: center;">
                                            <div style="margin-bottom: 5px; height: 35px; overflow: hidden;">
                                                <span style="font-size: 12px; font-weight: bold; color: #333; line-height: 1.2; display: block; text-align: center;">
                                                    {{ $img->point->name ?? 'Untitled' }}
                                                </span>
                                            </div>

                                            <div style="text-align: center; width: 100%;">
                                                @if($img->image_path && file_exists(public_path($img->image_path)))
                                                    <div style="border: 1px solid #ddd; padding: 3px; background-color: #fff; border-radius: 3px; display: inline-block;">
                                                        <img src="{{ public_path($img->image_path) }}" 
                                                            alt="{{ $img->point->name }}" 
                                                            style="width: 150px; height: auto;">
                                                    </div>
                                                @else
                                                    <div style="border: 1px dashed #ccc; padding: 15px; border-radius: 3px; background-color: #f9f9f9; width: 140px; margin: 0 auto;">
                                                        <span style="font-size: 10px; color: #888;">Gambar tidak ditemukan</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach

                                    {{-- Kolom Kosong --}}
                                    @for($i = count($chunk); $i < $columns; $i++)
                                        <td style="width: 25%; vertical-align: top; padding: 0 5px 40px 5px;">
                                            <div style="height: 35px;"></div>
                                            <div style="border: 1px dashed #eee; padding: 15px; border-radius: 3px; background-color: #fafafa; width: 140px; margin: 0 auto;">
                                                <span style="font-size: 10px; color: #ccc;">Kosong</span>
                                            </div>
                                        </td>
                                    @endfor
                                </tr>
                            </table>
                        @endforeach
                    @endif
                
                {{-- TAMPILAN KHUSUS UNTUK KOMPONEN VALIDASI YANG TERPISAH --}}
                @elseif(in_array($componentName, ['Rangka (Validasi Tabrak)', 'Interior (Validasi Banjir)']))
                    {{-- Kode untuk komponen validasi yang tetap terpisah --}}
                    @php
                        $displayPoints = [];
                        foreach($points as $point) {
                            $result = $point->inspection_point->results->first();
                            $hasResult = $result && (!empty($result->status) || !empty($result->note));
                            $hasImage = $point->inspection_point->images && $point->inspection_point->images->count() > 0;

                            $inputType = $point->input_type ?? '';
                            $selected = $result->status ?? null;

                            $statusArray = [];
                            if (!empty($selected)) {
                                if (strpos($selected, ',') !== false) {
                                    $statusArray = array_map('trim', explode(',', $selected));
                                } else {
                                    $statusArray = [$selected];
                                }
                            }

                            $settings = $point->settings ?? [];

                            $showImages = false;
                            $validStatuses = ['ok', 'normal', 'good', 'baik'];
                            $currentStatus = strtolower($result->status ?? '');
                            
                            if ($hasImage) {
                                if (!in_array($currentStatus, $validStatuses)) {
                                    if ($inputType === 'image' && $hasImage) {
                                        $showImages = true;
                                    } elseif ($inputType === 'imageTOradio' && $hasImage) {
                                        $showImages = true;
                                    } elseif ($inputType === 'radio') {
                                        foreach ($statusArray as $status) {
                                            $selectedOption = collect($settings['radios'] ?? [])->firstWhere('value', $status);
                                            $showImageUpload = $selectedOption['settings']['show_image_upload'] ?? false;
                                            if ($showImageUpload && $hasImage) {
                                                $showImages = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }

                            $showTextarea = false;
                            if (in_array($inputType, ['radio', 'imageTOradio'])) {
                                foreach ($statusArray as $status) {
                                    $selectedOption = collect($settings['radios'] ?? [])->firstWhere('value', $status);
                                    if ($selectedOption['settings']['show_textarea'] ?? false) {
                                        $showTextarea = true;
                                        break;
                                    }
                                }
                            }

                            $showNote = false;
                            if (in_array($inputType, ['text', 'number', 'date', 'textarea']) && !empty($result->note)) {
                                $showNote = true;
                            }

                            $isRadioType = in_array($inputType, ['radio', 'imageTOradio']);
                            $showStatusBadge = $isRadioType && !empty($statusArray);

                            if (!$hasResult && !$showImages) {
                                continue;
                            }

                            $noteArray = [];
                            $formattedNote = $result->note ?? '';
                            if (!empty($formattedNote)) {
                                if (strpos($formattedNote, ',') !== false) {
                                    $noteArray = array_map('trim', explode(',', $formattedNote));
                                } else {
                                    $noteArray = [$formattedNote];
                                }
                            }

                            $displayNotes = [];
                            foreach ($noteArray as $note) {
                                if ($inputType === 'account' && !empty($note)) {
                                    $currencySymbol = $settings['currency_symbol'] ?? 'Rp';
                                    $thousandsSeparator = $settings['thousands_separator'] ?? '.';
                                    $number = preg_replace('/[^0-9.]/', '', $note);
                                    if (is_numeric($number)) {
                                        $displayNotes[] = $currencySymbol . ' ' . number_format($number, 0, ',', $thousandsSeparator);
                                    } else {
                                        $displayNotes[] = $note;
                                    }
                                } else {
                                    $displayNotes[] = $note;
                                }
                            }

                            $displayPoints[] = [
                                'point' => $point,
                                'result' => $result,
                                'hasResult' => $hasResult,
                                'hasImage' => $hasImage,
                                'inputType' => $inputType,
                                'statusArray' => $statusArray,
                                'settings' => $settings,
                                'showImages' => $showImages,
                                'showTextarea' => $showTextarea,
                                'showNote' => $showNote,
                                'isRadioType' => $isRadioType,
                                'showStatusBadge' => $showStatusBadge,
                                'displayNotes' => $displayNotes,
                                'formattedNote' => $formattedNote,
                            ];
                        }

                        $totalPoints = count($displayPoints);
                        $half = ceil($totalPoints / 2);
                        $leftPoints = array_slice($displayPoints, 0, $half);
                        $rightPoints = array_slice($displayPoints, $half);
                        $maxRows = max(count($leftPoints), count($rightPoints));
                    @endphp

                    {{-- Tabel untuk data Result --}}
                    @if($totalPoints > 0)
                        <table class="main-info-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                            @for($i = 0; $i < $maxRows; $i++)
                                <tr>
                                    <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">
                                        @if(isset($leftPoints[$i]))
                                            {{ $leftPoints[$i]['point']->inspection_point->name ?? '-' }}
                                        @endif
                                    </td>
                                    <td style="padding: 6px 8px; font-size: 12px; vertical-align: top; width: 35%;">
                                        @if(isset($leftPoints[$i]))
                                            : 
                                            @php
                                                $pointData = $leftPoints[$i];
                                                $isRadioType = $pointData['isRadioType'];
                                                $statusArray = $pointData['statusArray'];
                                                $showNote = $pointData['showNote'];
                                                $showTextarea = $pointData['showTextarea'];
                                                $displayNotes = $pointData['displayNotes'];
                                            @endphp
                                            
                                            @if($isRadioType && !empty($statusArray))
                                                @foreach($statusArray as $status)
                                                    @php
                                                        $statusColor = '#ff6f00';
                                                        if (in_array(strtolower($status), ['normal', 'ada', 'baik', 'good', 'ok'])) {
                                                            $statusColor = '#28a745';
                                                        } elseif (in_array(strtolower($status), ['tidak normal', 'tidak ada', 'rusak', 'bad', 'not ok'])) {
                                                            $statusColor = '#dc3545';
                                                        }
                                                    @endphp
                                                    <span style="color: {{ $statusColor }};">{{ $status }}</span>
                                                @endforeach
                                            @endif
                                            
                                            @if ($showNote && (!$isRadioType || $showTextarea))
                                                @foreach($displayNotes as $note)
                                                    {{ $note }}
                                                @endforeach
                                            @endif
                                        @endif
                                    </td>
                                    
                                    <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">
                                        @if(isset($rightPoints[$i]))
                                            {{ $rightPoints[$i]['point']->inspection_point->name ?? '-' }}
                                        @endif
                                    </td>
                                    <td style="padding: 6px 8px; font-size: 12px; vertical-align: top; width: 35%;">
                                        @if(isset($rightPoints[$i]))
                                            : 
                                            @php
                                                $pointData = $rightPoints[$i];
                                                $isRadioType = $pointData['isRadioType'];
                                                $statusArray = $pointData['statusArray'];
                                                $showNote = $pointData['showNote'];
                                                $showTextarea = $pointData['showTextarea'];
                                                $displayNotes = $pointData['displayNotes'];
                                            @endphp
                                            
                                            @if($isRadioType && !empty($statusArray))
                                                @foreach($statusArray as $status)
                                                    @php
                                                        $statusColor = '#ff6f00';
                                                        if (in_array(strtolower($status), ['normal', 'ada', 'baik', 'good', 'ok'])) {
                                                            $statusColor = '#28a745';
                                                        } elseif (in_array(strtolower($status), ['tidak normal', 'tidak ada', 'rusak', 'bad', 'not ok'])) {
                                                            $statusColor = '#dc3545';
                                                        }
                                                    @endphp
                                                    <span style="color: {{ $statusColor }};">{{ $status }}</span>
                                                @endforeach
                                            @endif
                                            
                                            @if ($showNote && (!$isRadioType || $showTextarea))
                                                @foreach($displayNotes as $note)
                                                    {{ $note }}
                                                @endforeach
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endfor
                        </table>
                    @endif

                    {{-- Gambar untuk kerusakan --}}
                    @foreach($points as $point)
                        @php
                            $result = $point->inspection_point->results->first();
                            $hasImage = $point->inspection_point->images && $point->inspection_point->images->count() > 0;

                            if (!$hasImage) {
                                continue;
                            }

                            $inputType = $point->input_type ?? '';
                            $selected = $result->status ?? null;

                            $statusArray = [];
                            if (!empty($selected)) {
                                if (strpos($selected, ',') !== false) {
                                    $statusArray = array_map('trim', explode(',', $selected));
                                } else {
                                    $statusArray = [$selected];
                                }
                            }

                            $settings = $point->settings ?? [];

                            $showImages = false;
                            $validStatuses = ['ok', 'normal', 'good', 'baik'];
                            $currentStatus = strtolower($result->status ?? '');
                            
                            if (!in_array($currentStatus, $validStatuses)) {
                                if ($inputType === 'image' && $hasImage) {
                                    $showImages = true;
                                } elseif ($inputType === 'imageTOradio' && $hasImage) {
                                    $showImages = true;
                                } elseif ($inputType === 'radio') {
                                    foreach ($statusArray as $status) {
                                        $selectedOption = collect($settings['radios'] ?? [])->firstWhere('value', $status);
                                        $showImageUpload = $selectedOption['settings']['show_image_upload'] ?? false;
                                        if ($showImageUpload && $hasImage) {
                                            $showImages = true;
                                            break;
                                        }
                                    }
                                }
                            }

                            if (!$showImages) {
                                continue;
                            }

                            $showTextarea = false;
                            if (in_array($inputType, ['radio', 'imageTOradio'])) {
                                foreach ($statusArray as $status) {
                                    $selectedOption = collect($settings['radios'] ?? [])->firstWhere('value', $status);
                                    if ($selectedOption['settings']['show_textarea'] ?? false) {
                                        $showTextarea = true;
                                        break;
                                    }
                                }
                            }
                        @endphp
                        
                        @if($showImages)
                            <div style="margin-bottom: 15px; border-bottom: 1px dashed #ccc; padding-bottom: 10px;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 35%; padding: 4px; vertical-align: top; font-weight: bold; font-size: 12px;">{{ $point->inspection_point->name ?? '-' }}</td>
                                    </tr>
                                </table>
                                
                                <div style="margin-top: 10px;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        @php
                                            $images = $point->inspection_point->images;
                                            $columns = 5;
                                            $imageChunks = $images->count() > 0 ? array_chunk($images->all(), $columns) : [[]];
                                        @endphp
                                        @foreach($imageChunks as $chunk)
                                            <tr>
                                                @foreach($chunk as $img)
                                                    <td style="width: 20%; text-align: center; padding: 5px;">
                                                        @if($img->image_path && file_exists(public_path($img->image_path)))
                                                            <div style="border: 1px solid #ddd; padding: 3px; background-color: #fff; display: inline-block;">
                                                                <img src="{{ public_path($img->image_path) }}" 
                                                                     alt="{{ $img->point->name ?? 'image' }}" 
                                                                     style="max-width: 120px; max-height: 120px; object-fit: contain;">
                                                            </div>
                                                        @else
                                                            <div style="border: 1px dashed #ccc; padding: 15px; background-color: #f9f9f9; width: 120px; height: 120px; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                                                                <span style="font-size: 10px; color: #888;">Gambar tidak ditemukan</span>
                                                            </div>
                                                        @endif
                                                    </td>
                                                @endforeach
                                                @for ($i = count($chunk); $i < $columns; $i++)
                                                    <td style="width: 20%;">
                                                        <div style="width: 120px; height: 120px; margin: 0 auto;"></div>
                                                    </td>
                                                @endfor
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>

                                @if ($showTextarea && !empty($result->note))
                                    <div style="margin: 10px 0 4px 0; font-style: italic; color: #555; font-size: 12px; padding: 8px; background-color: #f5f5f5; border-radius: 4px;">
                                        <strong>Catatan:</strong> {{ $result->note }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                
                {{-- TAMPILAN UNTUK KOMPONEN LAINNYA (Eksterior, Interior, dll) --}}
                {{-- Termasuk komponen yang sudah digabung data --}}
                @else
                    @php
                        $displayPoints = [];
                        $imagesForComponent = []; // Array untuk menyimpan gambar komponen
                        
                        // Kumpulkan semua gambar untuk komponen ini dengan status bukan OK
                        foreach($points as $point) {
                            $result = $point->inspection_point->results->first();
                            $hasImage = $point->inspection_point->images && $point->inspection_point->images->isNotEmpty();
                            
                            if ($hasImage) {
                                // Cek status
                                $status = strtolower($result->status ?? '');
                                $validStatuses = ['ok', 'normal', 'good', 'baik'];
                                
                                // LOGIKA: Gambar dengan status OK akan ditampilkan di Foto Kendaraan
                                // Gambar dengan status BUKAN OK akan ditampilkan di sini
                                if (!in_array($status, $validStatuses)) {
                                    $imagesForComponent[] = [
                                        'point' => $point,
                                        'images' => $point->inspection_point->images->all(),
                                        'result' => $result
                                    ];
                                }
                            }
                        }
                        
                        // Kumpulkan data untuk tabel
                        foreach($points as $point) {
                            $result = $point->inspection_point->results->first();
                            $hasResult = $result && (!empty($result->status) || !empty($result->note));

                            if (!$hasResult) {
                                continue;
                            }

                            $inputType = $point->input_type ?? '';
                            $selected = $result->status ?? null;

                            $statusArray = [];
                            if (!empty($selected)) {
                                if (strpos($selected, ',') !== false) {
                                    $statusArray = array_map('trim', explode(',', $selected));
                                } else {
                                    $statusArray = [$selected];
                                }
                            }

                            $settings = $point->settings ?? [];

                            $showNote = false;
                            if (in_array($inputType, ['text', 'number', 'date', 'textarea']) && !empty($result->note)) {
                                $showNote = true;
                            }

                            $showTextarea = false;
                            if (in_array($inputType, ['radio', 'imageTOradio'])) {
                                foreach ($statusArray as $status) {
                                    $selectedOption = collect($settings['radios'] ?? [])->firstWhere('value', $status);
                                    if ($selectedOption['settings']['show_textarea'] ?? false) {
                                        $showTextarea = true;
                                        break;
                                    }
                                }
                            }

                            $isRadioType = in_array($inputType, ['radio', 'imageTOradio']);
                            $showStatusBadge = $isRadioType && !empty($statusArray);

                            $noteArray = [];
                            $formattedNote = $result->note ?? '';
                            if (!empty($formattedNote)) {
                                if (strpos($formattedNote, ',') !== false) {
                                    $noteArray = array_map('trim', explode(',', $formattedNote));
                                } else {
                                    $noteArray = [$formattedNote];
                                }
                            }

                            $displayNotes = [];
                            foreach ($noteArray as $note) {
                                if ($inputType === 'account' && !empty($note)) {
                                    $currencySymbol = $settings['currency_symbol'] ?? 'Rp';
                                    $thousandsSeparator = $settings['thousands_separator'] ?? '.';
                                    $number = preg_replace('/[^0-9.]/', '', $note);
                                    if (is_numeric($number)) {
                                        $displayNotes[] = $currencySymbol . ' ' . number_format($number, 0, ',', $thousandsSeparator);
                                    } else {
                                        $displayNotes[] = $note;
                                    }
                                } else {
                                    $displayNotes[] = $note;
                                }
                            }

                            $displayPoints[] = [
                                'point' => $point,
                                'result' => $result,
                                'hasResult' => $hasResult,
                                'inputType' => $inputType,
                                'statusArray' => $statusArray,
                                'settings' => $settings,
                                'showTextarea' => $showTextarea,
                                'showNote' => $showNote,
                                'isRadioType' => $isRadioType,
                                'showStatusBadge' => $showStatusBadge,
                                'displayNotes' => $displayNotes,
                                'formattedNote' => $formattedNote,
                            ];
                        }

                        $totalPoints = count($displayPoints);
                        $half = ceil($totalPoints / 2);
                        $leftPoints = array_slice($displayPoints, 0, $half);
                        $rightPoints = array_slice($displayPoints, $half);
                        $maxRows = max(count($leftPoints), count($rightPoints));
                    @endphp

                    {{-- Tabel untuk data Result --}}
                    @if($totalPoints > 0)
                        <table class="main-info-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                            @for($i = 0; $i < $maxRows; $i++)
                                <tr>
                                    <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">
                                        @if(isset($leftPoints[$i]))
                                            {{ $leftPoints[$i]['point']->inspection_point->name ?? '-' }}
                                        @endif
                                    </td>
                                    <td style="padding: 6px 8px; font-size: 12px; vertical-align: top; width: 35%;">
                                        @if(isset($leftPoints[$i]))
                                            : 
                                            @php
                                                $pointData = $leftPoints[$i];
                                                $isRadioType = $pointData['isRadioType'];
                                                $statusArray = $pointData['statusArray'];
                                                $showNote = $pointData['showNote'];
                                                $showTextarea = $pointData['showTextarea'];
                                                $displayNotes = $pointData['displayNotes'];
                                            @endphp
                                            
                                            @if($isRadioType && !empty($statusArray))
                                                @foreach($statusArray as $status)
                                                    @php
                                                        $statusColor = '#ff6f00';
                                                        if (in_array(strtolower($status), ['normal', 'ada', 'baik', 'good', 'ok'])) {
                                                            $statusColor = '#28a745';
                                                        } elseif (in_array(strtolower($status), ['tidak normal', 'tidak ada', 'rusak', 'bad', 'not ok'])) {
                                                            $statusColor = '#dc3545';
                                                        }
                                                    @endphp
                                                    <span style="color: {{ $statusColor }};">{{ $status }}</span>
                                                @endforeach
                                            @endif
                                            
                                            @if ($showNote && (!$isRadioType || $showTextarea))
                                                @foreach($displayNotes as $note)
                                                    {{ $note }}
                                                @endforeach
                                            @endif
                                        @endif
                                    </td>
                                    
                                    <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">
                                        @if(isset($rightPoints[$i]))
                                            {{ $rightPoints[$i]['point']->inspection_point->name ?? '-' }}
                                        @endif
                                    </td>
                                    <td style="padding: 6px 8px; font-size: 12px; vertical-align: top; width: 35%;">
                                        @if(isset($rightPoints[$i]))
                                            : 
                                            @php
                                                $pointData = $rightPoints[$i];
                                                $isRadioType = $pointData['isRadioType'];
                                                $statusArray = $pointData['statusArray'];
                                                $showNote = $pointData['showNote'];
                                                $showTextarea = $pointData['showTextarea'];
                                                $displayNotes = $pointData['displayNotes'];
                                            @endphp
                                            
                                            @if($isRadioType && !empty($statusArray))
                                                @foreach($statusArray as $status)
                                                    @php
                                                        $statusColor = '#ff6f00';
                                                        if (in_array(strtolower($status), ['normal', 'ada', 'baik', 'good', 'ok'])) {
                                                            $statusColor = '#28a745';
                                                        } elseif (in_array(strtolower($status), ['tidak normal', 'tidak ada', 'rusak', 'bad', 'not ok'])) {
                                                            $statusColor = '#dc3545';
                                                        }
                                                    @endphp
                                                    <span style="color: {{ $statusColor }};">{{ $status }}</span>
                                                @endforeach
                                            @endif
                                            
                                            @if ($showNote && (!$isRadioType || $showTextarea))
                                                @foreach($displayNotes as $note)
                                                    {{ $note }}
                                                @endforeach
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endfor
                        </table>
                    @endif

                    {{-- GAMBAR UNTUK KOMPONEN (ditampilkan seperti di Rangka/Interior Validasi) --}}
                    @if(count($imagesForComponent) > 0)
                        @foreach($imagesForComponent as $pointData)
                            @php
                                $point = $pointData['point'];
                                $result = $pointData['result'];
                                $images = $pointData['images'];
                                
                                // Cek apakah perlu menampilkan gambar berdasarkan input type
                                $inputType = $point->input_type ?? '';
                                $selected = $result->status ?? null;
                                
                                $statusArray = [];
                                if (!empty($selected)) {
                                    if (strpos($selected, ',') !== false) {
                                        $statusArray = array_map('trim', explode(',', $selected));
                                    } else {
                                        $statusArray = [$selected];
                                    }
                                }
                                
                                $settings = $point->settings ?? [];
                                
                                $showImages = false;
                                $validStatuses = ['ok', 'normal', 'good', 'baik'];
                                $currentStatus = strtolower($result->status ?? '');
                                
                                // Logika untuk menentukan apakah gambar ditampilkan
                                if (!in_array($currentStatus, $validStatuses)) {
                                    if ($inputType === 'image') {
                                        $showImages = true;
                                    } elseif ($inputType === 'imageTOradio') {
                                        $showImages = true;
                                    } elseif ($inputType === 'radio') {
                                        foreach ($statusArray as $status) {
                                            $selectedOption = collect($settings['radios'] ?? [])->firstWhere('value', $status);
                                            $showImageUpload = $selectedOption['settings']['show_image_upload'] ?? false;
                                            if ($showImageUpload) {
                                                $showImages = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                                
                                $showTextarea = false;
                                if (in_array($inputType, ['radio', 'imageTOradio'])) {
                                    foreach ($statusArray as $status) {
                                        $selectedOption = collect($settings['radios'] ?? [])->firstWhere('value', $status);
                                        if ($selectedOption['settings']['show_textarea'] ?? false) {
                                            $showTextarea = true;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            
                            @if($showImages && count($images) > 0)
                                <div style="margin-bottom: 15px; border-bottom: 1px dashed #ccc; padding-bottom: 10px;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr>
                                            <td style="width: 35%; padding: 4px; vertical-align: top; font-weight: bold; font-size: 12px;">
                                                {{ $point->inspection_point->name ?? '-' }}
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <div style="margin-top: 10px;">
                                        <table style="width: 100%; border-collapse: collapse;">
                                            @php
                                                $columns = 5;
                                                $imageChunks = array_chunk($images, $columns);
                                            @endphp
                                            @foreach($imageChunks as $chunk)
                                                <tr>
                                                    @foreach($chunk as $img)
                                                        <td style="width: 20%; text-align: center; padding: 5px;">
                                                            @if($img->image_path && file_exists(public_path($img->image_path)))
                                                                <div style="border: 1px solid #ddd; padding: 3px; background-color: #fff; display: inline-block;">
                                                                    <img src="{{ public_path($img->image_path) }}" 
                                                                         alt="{{ $img->point->name ?? 'image' }}" 
                                                                         style="max-width: 120px; max-height: 120px; object-fit: contain;">
                                                                </div>
                                                            @else
                                                                <div style="border: 1px dashed #ccc; padding: 15px; background-color: #f9f9f9; width: 120px; height: 120px; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                                                                    <span style="font-size: 10px; color: #888;">Gambar tidak ditemukan</span>
                                                                </div>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                    @for ($i = count($chunk); $i < $columns; $i++)
                                                        <td style="width: 20%;">
                                                            <div style="width: 120px; height: 120px; margin: 0 auto;"></div>
                                                        </td>
                                                    @endfor
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>

                                    @if ($showTextarea && !empty($result->note))
                                        <div style="margin: 10px 0 4px 0; font-style: italic; color: #555; font-size: 12px; padding: 8px; background-color: #f5f5f5; border-radius: 4px;">
                                            <strong>Catatan:</strong> {{ $result->note }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    @endif
                @endif
            </div>
        </div>
    @endif
@endforeach

{{-- Filter Status untuk semua data dengan logika khusus --}}
@php
    $allPointsWithImages = [];
    $excludedComponents = ['Dokumen', 'Foto Kendaraan'];
    
    // Filter status yang tidak ingin ditampilkan - HANYA untuk 2 komponen khusus
    $excludedStatuses = ['ok', 'baik', 'normal', 'good'];
    
    // Loop melalui semua menu points untuk mengumpulkan data dengan gambar
    foreach ($menu_points as $point) {
        $componentName = optional($point->inspection_point->component)->name ?? 'Tanpa Komponen';
        
        // 1. Skip jika component termasuk dalam excluded components (selalu)
        if (in_array($componentName, $excludedComponents)) {
            continue;
        }
        
        // 2. Skip Rangka (Validasi Tabrak) jika collision == 'yes'
        if ($componentName == 'Rangka (Validasi Tabrak)' && $collision == 'yes') {
            continue;
        }
        
        // 3. Skip Interior (Validasi Banjir) jika flooded == 'yes'
        if ($componentName == 'Interior (Validasi Banjir)' && $flooded == 'yes') {
            continue;
        }
        
        $result = $point->inspection_point->results->first();
        $hasImage = $point->inspection_point->images && $point->inspection_point->images->count() > 0;
        
        if (!$hasImage) {
            continue;
        }
        
        $inputType = $point->input_type ?? '';
        $selected = $result->status ?? null;
        
        // 4. FILTER STATUS HANYA UNTUK 2 KOMPONEN KHUSUS dalam kondisi tertentu
        $isSpecialComponent = in_array($componentName, ['Rangka (Validasi Tabrak)', 'Interior (Validasi Banjir)']);
        $shouldFilterStatus = false;
        
        // Cek apakah komponen ini harus difilter statusnya
        if ($isSpecialComponent) {
            if ($componentName == 'Rangka (Validasi Tabrak)' && $collision == 'no') {
                $shouldFilterStatus = true;
            }
            if ($componentName == 'Interior (Validasi Banjir)' && $flooded == 'no') {
                $shouldFilterStatus = true;
            }
        }
        
        // Jika harus filter status dan ada status yang dipilih
        if ($shouldFilterStatus && !empty($selected)) {
            $selectedArray = [];
            if (strpos($selected, ',') !== false) {
                $selectedArray = array_map('trim', explode(',', $selected));
            } else {
                $selectedArray = [$selected];
            }
            
            // Cek apakah semua status yang dipilih adalah excluded statuses
            $allExcluded = true;
            foreach ($selectedArray as $status) {
                if (!in_array(strtolower($status), array_map('strtolower', $excludedStatuses))) {
                    $allExcluded = false;
                    break;
                }
            }
            
            // Jika semua statusnya excluded, skip point ini
            if ($allExcluded && !empty($selectedArray)) {
                continue;
            }
        }
        
        $settings = $point->settings ?? [];
        $statusArray = [];
        
        if (!empty($selected)) {
            if (strpos($selected, ',') !== false) {
                $statusArray = array_map('trim', explode(',', $selected));
            } else {
                $statusArray = [$selected];
            }
        }
        
        $showImages = false;
        
        if ($inputType === 'image' && $hasImage) {
            $showImages = true;
        } elseif ($inputType === 'imageTOradio' && $hasImage) {
            $showImages = true;
        } elseif ($inputType === 'radio') {
            foreach ($statusArray as $status) {
                $selectedOption = collect($settings['radios'] ?? [])->firstWhere('value', $status);
                $showImageUpload = $selectedOption['settings']['show_image_upload'] ?? false;
                if ($showImageUpload && $hasImage) {
                    $showImages = true;
                    break;
                }
            }
        }
        
        if ($showImages) {
            $allPointsWithImages[] = [
                'point' => $point,
                'result' => $result,
                'hasImage' => $hasImage,
                'inputType' => $inputType,
                'selected' => $selected,
                'settings' => $settings,
                'statusArray' => $statusArray,
                'componentName' => $componentName
            ];
        }
    }
@endphp

{{-- Container untuk kedua menu --}}
<div style="margin-bottom: 30px;">
    {{-- Menu Estimasi Perbaikan --}}
    @if(isset($repairEstimations) && $repairEstimations->count() > 0)
        <div class="section-box" style="border: 1px solid #28a745; border-radius: 10px 10px 0 0; margin-bottom: 20px; overflow: hidden;">
            <div style="font-size: 16px; font-weight: bold; background: #28a745; padding: 8px; border-bottom: 1px solid #218838; color: white; position: relative;">
                Estimasi Perbaikan
            </div>
            
            <div style="padding: 10px;">
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <tbody>
                        @foreach($repairEstimations as $index => $estimation)
                            <tr>
                                <td style="padding: 8px 10px; font-weight: bold; width: 30px; font-size: 12px; vertical-align: top; color: #666;">
                                    {{ $index + 1 }}.
                                </td>
                                <td style="padding: 8px 10px; font-weight: bold; width: 400px; font-size: 13px; vertical-align: top;">
                                    {{ $estimation->part_name }}
                                    @if($estimation->repair_description)
                                        <div style="font-size: 11px; color: #666; margin-bottom: 8px; line-height: 1.4;">
                                            {{ $estimation->repair_description }}
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 8px 10px; font-size: 13px; vertical-align: top;"> 
                                    <div style="font-size: 12px;">
                                        <span style="font-weight: bold; color: #555;">Urgency:</span>
                                        <span style="color: {{ $estimation->urgency === 'segera' ? '#dc3545' : '#ff6f00' }}; font-weight: bold;">
                                            {{ $estimation->urgency === 'segera' ? 'Segera' : 'Jangka Panjang' }}
                                        </span>
                                        <span style="color: #999; margin-left: 5px; font-size: 11px;">
                                            ({{ $estimation->status === 'perlu' ? 'perlu' : ($estimation->status === 'disarankan' ? 'disarankan' : 'opsional') }})
                                        </span>
                                    </div>
                                </td>
                                <td style="padding: 8px 10px; font-weight: bold; font-size: 13px; text-align: right; vertical-align: top;">
                                    Rp {{ number_format($estimation->estimated_cost, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                        
                        <tr>
                            <td colspan="3" style="padding: 15px 10px 5px 10px; text-align: right; font-weight: bold; font-size: 14px; border-top: 1px solid #eee;">
                                Total Estimasi:
                            </td>
                            <td style="padding: 15px 10px 5px 10px; text-align: right; font-weight: bold; font-size: 14px; color: #28a745; border-top: 1px solid #eee;">
                                Rp {{ number_format($totalRepairCost ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                {{-- Catatan untuk Customer --}}
                {{-- <div style="margin-top: 15px; padding: 12px; background-color: #28a745; border: 1px solid #28a745; border-radius: 5px; font-size: 12px; color: #070707;"> --}}
                    <div style="display: flex; align-items: flex-start; font-size: 12px;">
                        <div style="margin-right: 10px; font-size: 12px;">📝</div>
                        <div>
                            <strong style="display: block; margin-bottom: 5px;">Catatan Penting:</strong>
                            <ul style="margin: 0; padding-left: 15px;">
                                <li>Estimasi harga di atas adalah <strong>perhitungan kasar</strong> dan dapat berubah</li>
                                <li>Harga sparepart dan jasa perbaikan dapat berbeda-beda tergantung:
                                    <ul style="margin-top: 3px; padding-left: 15px;">
                                        <li>Wilayah/lokasi bengkel</li>
                                        <li>Brand dan kualitas sparepart yang digunakan</li>
                                        <li>Tingkat kesulitan perbaikan</li>
                                        <li>Kebijakan masing-masing bengkel</li>
                                    </ul>
                                </li>
                                <li>Disarankan untuk melakukan <strong>survey harga</strong> ke beberapa bengkel terlebih dahulu</li>
                                <li>Untuk perbaikan yang <span style="color: #dc3545; font-weight: bold;">urgent/segera</span>, segera konsultasikan dengan mekanik profesional</li>
                            </ul>
                        </div>
                    </div>
                {{-- </div> --}}
            </div>
        </div>
    @endif
</div>

<div style="page-break-before: always;"></div>

{{-- Peringatan Penting Sebelum Pembayaran --}}
<div style="margin: 25px 0; padding: 15px; background: linear-gradient(135deg, #fff8e1, #ffecb3); border: 2px solid #ffb300; border-radius: 10px; box-shadow: 0 3px 10px rgba(255, 179, 0, 0.1);">
    <div style="display: flex; align-items: center; margin-bottom: 12px;">
        <div style="margin-right: 12px; font-size: 20px; color: #ff6f00;">⚠️</div>
        <div>
            <h3 style="margin: 0; font-size: 13px; color: #ff6f00; text-transform: uppercase; letter-spacing: 0.5px;">
                HAL PENTING SEBELUM PEMBAYARAN
            </h3>
        </div>
    </div>
    
    <div style="padding-left: 32px;">
        <ul style="margin: 0; padding: 0; list-style-type: none;">
            <li style="margin-bottom: 10px; padding-left: 0; position: relative;">
                <div style="display: flex; align-items: flex-start;">
                    <div style="margin-right: 8px; color: #d84315; font-weight: bold;">•</div>
                    <div style="flex: 1;">
                        <span style="font-weight: bold; color: #333; font-size: 12px;">
                            Untuk menghindari kemungkinan yang terjadi di luar inspeksi
                        </span>
                        <span style="font-size: 11.5px; color: #555; line-height: 1.5; display: block; margin-top: 3px;">
                            (misalnya bunyi tidak wajar, fungsi elektrikal, lampu indikator, dan fitur penunjang ADAS/Cruise Control/dll.), 
                            <span style="color: #d32f2f; font-weight: bold; text-decoration: underline;">DISARANKAN untuk melakukan Test Drive Terlebih Dahulu</span> 
                            sebelum pembayaran/transaksi.
                        </span>
                    </div>
                </div>
            </li>
            
            <li style="padding-left: 0; position: relative;">
                <div style="display: flex; align-items: flex-start;">
                    <div style="margin-right: 8px; color: #d84315; font-weight: bold;">•</div>
                    <div style="flex: 1;">
                        <span style="font-weight: bold; color: #333; font-size: 12px;">
                            Pastikan knalpot Tidak Berasap
                        </span>
                        <span style="font-size: 11.5px; color: #555; line-height: 1.5; display: block; margin-top: 3px;">
                            (khusus untuk mobil bensin).
                        </span>
                    </div>
                </div>
            </li>
           <li style="padding-left: 0; position: relative;">
    <div style="display: flex; align-items: flex-start;">
        <div style="margin-right: 8px; color: #d84315; font-weight: bold;">•</div>
        <div style="flex: 1;">
            <span style="font-weight: bold; color: #333; font-size: 12px;">
                Prosedur Keamanan Pembayaran
            </span>
            <span style="font-size: 11.5px; color: #555; line-height: 1.5; display: block; margin-top: 3px;">
                <span style="color: #d32f2f; font-weight: bold;">⚠️ PERINGATAN ANTI PENIPUAN:</span>
                <br>• Transfer hanya saat penerima uang ADA di lokasi
                <br>• Konfirmasi langsung dengan pemilik resmi unit
                <br>• Jangan serahkan dana kepada pihak tidak dikenal
                <br>• Pastikan transaksi melalui jalur resmi dan tercatat
            </span>
        </div>
    </div>
</li>
        </ul>
        
        {{-- Info Tambahan --}}
        <div style="margin-top: 12px; padding: 8px 12px; background-color: rgba(255, 193, 7, 0.1); border-radius: 6px; border-left: 3px solid #ff9800;">
            <p style="margin: 0; font-size: 11px; color: #5d4037; font-style: italic; line-height: 1.4;">
                <strong>💡 Tips:</strong> Lakukan test drive minimal 15-20 menit dengan berbagai kondisi jalan (lurus, belokan, tanjakan) untuk memastikan performa kendaraan.
            </p>
        </div>
    </div>
</div>

<div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #f0f0f0;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 15px;">
                <div style="background: linear-gradient(90deg, #f8f9fa, #ffffff); padding: 15px; border-radius: 8px; border-left: 4px solid #0d98d8;">
                    <div style="display: flex; align-items: center; margin-bottom: 8px;">
                        <div style="margin-right: 10px; font-size: 16px; color: #0d98d8;">📄</div>
                        <div>
                            <strong style="font-size: 13px; color: #333;">Laporan Otomatis</strong>
                        </div>
                    </div>
                    <p style="margin: 0; font-size: 12px; color: #666; line-height: 1.5;">
                        Laporan ini dibuat secara otomatis berdasarkan data inspeksi yang tercatat. Setiap perubahan akan dicatat dalam sistem.
                    </p>
                </div>
            </td>
            
            <td style="width: 50%; vertical-align: top; padding-left: 15px;">
                <div style="background: linear-gradient(90deg, #f8f9fa, #ffffff); padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;">
                    <div style="display: flex; align-items: center; margin-bottom: 8px;">
                        <div style="margin-right: 10px; font-size: 16px; color: #28a745;">🤝</div>
                        <div>
                            <strong style="font-size: 13px; color: #333;">Terima Kasih</strong>
                        </div>
                    </div>
                    <p style="margin: 0; font-size: 12px; color: #666; line-height: 1.5;">
                        Terima kasih telah mempercayakan inspeksi kendaraan kepada kami. Untuk pertanyaan lebih lanjut, hubungi tim support kami.
                    </p>
                </div>
            </td>
        </tr>
    </table>
    
    {{-- Signature/Footer Section --}}
    <div style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #ddd; text-align: center;">
        <p style="margin: 0; font-size: 10px; color: #999;">
            <strong>Dokumen ini sah dan dapat digunakan sebagai referensi inspeksi kendaraan</strong><br>
            Generated on: {{ date('d F Y, H:i') }} • cekMobil_online
        </p>
    </div>
</div>
</body>
</html>