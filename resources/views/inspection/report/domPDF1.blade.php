<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hasil Inspeksi - AutoVerse</title>
    <style>
        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background-color: #fff;
        }
        
        /* Container */
        .container {
            width: 100%;
            max-width: 210mm; /* A4 width */
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eaeaea;
        }
        
        .brand {
            flex: 1;
        }
        
        .brand h1 {
            font-size: 32px;
            font-weight: 800;
        }
        
        .brand-auto {
            color: #70d874;
        }
        
        .brand-verse {
            color: #000000;
        }
        
        .report-title {
            flex: 2;
            text-align: center;
        }
        
        .report-title h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .date-info {
            flex: 1;
            text-align: right;
            color: #7f8c8d;
            font-size: 13px;
        }
        
        /* Vehicle Info Section */
        .vehicle-info-section {
            margin-bottom: 30px;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .vehicle-info-section h2 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0d98d8;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-table tr {
            border-bottom: 1px solid #eee;
        }
        
        .info-table tr:last-child {
            border-bottom: none;
        }
        
        .info-table td {
            padding: 10px 15px;
            vertical-align: top;
        }
        
        .info-label {
            font-weight: 600;
            color: #34495e;
            width: 140px;
        }
        
        .info-value {
            color: #2c3e50;
        }
        
        /* Status Indicators */
        .status-indicators {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin: 30px 0;
            padding: 25px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .status-box {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .status-box.flooded {
            border-top: 4px solid #dc3545;
        }
        
        .status-box.flood-free {
            border-top: 4px solid #28a745;
        }
        
        .status-box.collision {
            border-top: 4px solid #ffc107;
        }
        
        .status-box.collision-free {
            border-top: 4px solid #28a745;
        }
        
        .status-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .status-icon img {
            max-width: 100%;
            max-height: 100%;
        }
        
        .status-text {
            font-size: 18px;
            font-weight: 700;
            margin-top: 10px;
        }
        
        .flooded-text {
            color: #dc3545;
        }
        
        .flood-free-text {
            color: #28a745;
        }
        
        .collision-text {
            color: #ffc107;
        }
        
        .collision-free-text {
            color: #28a745;
        }
        
        /* Inspection Conclusion */
        .inspection-conclusion {
            background: linear-gradient(to right, #f8f9fa, #e3f2fd);
            border-left: 5px solid #0d98d8;
            padding: 20px;
            margin: 25px 0;
            border-radius: 5px;
        }
        
        .inspection-conclusion h3 {
            color: #2c3e50;
            margin-bottom: 12px;
            font-size: 17px;
        }
        
        .conclusion-content {
            color: #34495e;
            line-height: 1.7;
        }
        
        /* Components Section */
        .components-section {
            margin-top: 40px;
        }
        
        .component-card {
            margin-bottom: 30px;
            border: 2px solid #0d98d8;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(13, 152, 216, 0.15);
        }
        
        .component-header {
            background: linear-gradient(135deg, #0d98d8 0%, #0b8bc9 100%);
            color: white;
            padding: 15px 20px;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .component-header::before {
            content: "✓";
            margin-right: 10px;
            font-size: 20px;
        }
        
        .component-content {
            padding: 25px;
            background: white;
        }
        
        /* Data Table */
        .data-table-two-columns {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .data-table-two-columns tr {
            border-bottom: 1px solid #eee;
        }
        
        .data-table-two-columns tr:last-child {
            border-bottom: none;
        }
        
        .data-table-two-columns td {
            padding: 12px 15px;
            vertical-align: top;
            width: 50%;
        }
        
        .point-name {
            font-weight: 600;
            color: #2c3e50;
            width: 140px;
        }
        
        .point-value {
            color: #34495e;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 8px;
            margin-bottom: 5px;
        }
        
        .status-good {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-bad {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        /* Image Gallery */
        .image-gallery {
            margin-top: 25px;
        }
        
        .gallery-title {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #eaeaea;
        }
        
        .image-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        
        .image-item {
            text-align: center;
            background: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .image-label {
            padding: 10px;
            font-size: 12px;
            font-weight: 600;
            color: #2c3e50;
            background: white;
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .image-container {
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            padding: 10px;
        }
        
        .image-container img {
            max-width: 100%;
            max-height: 100%;
            border-radius: 4px;
            object-fit: contain;
        }
        
        .image-placeholder {
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            color: #95a5a6;
            font-style: italic;
        }
        
        /* Damage Photos Section */
        .damage-section {
            margin-top: 40px;
            border: 2px solid #dc3545;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.15);
        }
        
        .damage-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 15px 20px;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
        }
        
        .damage-header::before {
            content: "⚠";
            margin-right: 10px;
            font-size: 20px;
        }
        
        /* Repair Estimates */
        .repair-estimates {
            margin-top: 40px;
            padding: 25px;
            background: linear-gradient(to right, #f8f9fa, #e3f2fd);
            border-left: 5px solid #0d98d8;
            border-radius: 8px;
        }
        
        .repair-estimates h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .estimates-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .estimates-table th {
            background: #2c3e50;
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .estimates-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .estimates-table tr:last-child td {
            border-bottom: none;
        }
        
        .urgency-soon {
            color: #dc3545;
            font-weight: 600;
        }
        
        .urgency-long {
            color: #ffc107;
            font-weight: 600;
        }
        
        .status-badge-sm {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 12px;
            background: #e9ecef;
            color: #6c757d;
        }
        
        .total-row {
            background: #f8f9fa;
            font-weight: 700;
        }
        
        .total-amount {
            color: #28a745;
            font-size: 16px;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #eaeaea;
            display: flex;
            justify-content: space-between;
            color: #7f8c8d;
            font-size: 13px;
        }
        
        /* Page Break */
        .page-break {
            page-break-before: always;
            margin-top: 40px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .status-indicators {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .image-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .data-table-two-columns {
                display: block;
            }
            
            .data-table-two-columns tr {
                display: block;
                margin-bottom: 10px;
            }
            
            .data-table-two-columns td {
                display: block;
                width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="brand">
                <h1><span class="brand-auto">Auto</span><span class="brand-verse">Verse</span></h1>
            </div>
            <div class="report-title">
                <h1>Laporan Hasil Inspeksi</h1>
                <p>Laporan resmi hasil inspeksi kendaraan</p>
            </div>
            <div class="date-info">
                <p>Tanggal: {{ \Carbon\Carbon::parse($inspection->inspection_date)->format('d F Y') }}</p>
                <p>Waktu: {{ \Carbon\Carbon::parse($inspection->inspection_date)->format('H:i') }}</p>
            </div>
        </div>

        @php
            $conclusionSettings = $inspection->settings['conclusion'] ?? [];
            $flooded = $conclusionSettings['flooded'] ?? 'no';
            $collision = $conclusionSettings['collision'] ?? 'no';
            $collisionSeverity = $conclusionSettings['collision_severity'] ?? '';
        @endphp

        <!-- Vehicle Information -->
        <div class="vehicle-info-section">
            <h2>Informasi Kendaraan</h2>
            <table class="info-table">
                @if ($inspection->car_id)
                <tr>
                    <td class="info-label">Nomor Polisi</td>
                    <td class="info-value">: {{ $inspection->plate_number }}</td>
                    <td class="info-label">Tanggal Inspeksi</td>
                    <td class="info-value">: {{ \Carbon\Carbon::parse($inspection->inspection_date)->format('d-m-Y H:i:s') }}</td>
                </tr>
                <tr>
                    <td class="info-label">Merek</td>
                    <td class="info-value">: {{ $inspection->car->brand->name }}</td>
                    <td class="info-label">Odo Meter</td>
                    <td class="info-value">: {{ $dataCarOther['jarakTempuh(KM)']['note'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Model</td>
                    <td class="info-value">: {{ $inspection->car->model->name }}</td>
                    <td class="info-label">Pajak 1 Tahunan</td>
                    <td class="info-value">: {{ $dataCarOther['pajakTahunan']['note'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Tipe</td>
                    <td class="info-value">: {{ $inspection->car->type->name }}</td>
                    <td class="info-label">Pajak 5 Tahunan</td>
                    <td class="info-value">: {{ $dataCarOther['pajak5Tahunan']['note'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="info-label">CC</td>
                    <td class="info-value">: {{ $inspection->car->cc }}</td>
                    <td class="info-label">Pajak Tahunan (PKB)</td>
                    <td class="info-value">: {{ $dataCarOther['pKB']['note'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Bahan Bakar</td>
                    <td class="info-value">: {{ $inspection->car->fuel_type }}</td>
                    <td class="info-label">Kepemilikan</td>
                    <td class="info-value">: {{ $dataCarOther['kepemilikan']['status'] ?? '' }} {{ $dataCarOther['kepemilikan']['note'] ?? '' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Transmisi</td>
                    <td class="info-value">: {{ $inspection->car->transmission }}</td>
                    <td class="info-label">Buku Manual</td>
                    <td class="info-value">: {{ $dataCarOther['BS/BM']['status'] ?? '' }} {{ $dataCarOther['BS/BM']['note'] ?? '' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Tahun Pembuatan</td>
                    <td class="info-value">: {{ $inspection->car->year }}</td>
                    <td class="info-label"></td>
                    <td class="info-value"></td>
                </tr>
                <tr>
                    <td class="info-label">Periode Model</td>
                    <td class="info-value">: {{ $inspection->car->production_period ?? '-' }}</td>
                    <td class="info-label"></td>
                    <td class="info-value"></td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Status Indicators -->
        <div class="status-indicators">
            <div class="status-box {{ $flooded == 'yes' ? 'flooded' : 'flood-free' }}">
                <div class="status-icon">
                    @if ($flooded == 'yes')
                        <img src="{{ public_path('images/icons/banjir.png') }}" alt="Banjir">
                    @else
                        <img src="{{ public_path('images/icons/aman.png') }}" alt="Aman">
                    @endif
                </div>
                @if ($flooded == 'yes')
                    <p class="status-text flooded-text">Bekas Banjir</p>
                @else
                    <p class="status-text flood-free-text">Bebas Banjir</p>
                @endif
            </div>
            
            <div class="status-box {{ $collision == 'yes' ? 'collision' : 'collision-free' }}">
                <div class="status-icon">
                    @if ($collision == 'yes')
                        @php
                            $collisionImage = public_path('images/icons/ringan.png');
                            if ($collisionSeverity === 'moderate') {
                                $collisionImage = public_path('images/icons/beruntun.png');
                            } elseif ($collisionSeverity === 'heavy') {
                                $collisionImage = public_path('images/icons/berat.png');
                            }
                        @endphp
                        <img src="{{ $collisionImage }}" alt="Tabrak">
                    @else
                        <img src="{{ public_path('images/icons/aman.png') }}" alt="Aman">
                    @endif
                </div>
                @if ($collision == 'yes')
                    @php
                        $collisionText = 'Tabrak Ringan';
                        $collisionColor = 'collision-text';
                        if ($collisionSeverity === 'moderate') {
                            $collisionText = 'Tabrak Beruntun';
                            $collisionColor = 'collision-text';
                        } elseif ($collisionSeverity === 'heavy') {
                            $collisionText = 'Tabrak Berat';
                            $collisionColor = 'collision-text';
                        }
                    @endphp
                    <p class="status-text {{ $collisionColor }}">{{ $collisionText }}</p>
                @else
                    <p class="status-text collision-free-text">Bebas Tabrak</p>
                @endif
            </div>
        </div>

        @if($inspection->notes)
        <!-- Inspection Conclusion -->
        <div class="inspection-conclusion">
            <h3>Kesimpulan Inspeksi</h3>
            <div class="conclusion-content">{!! $inspection->notes !!}</div>
        </div>
        @endif

        @php
            $groupedMenuPoints = $menu_points
                ->sortBy(function ($point) {
                    $component = $point->inspection_point->component;
                    return $component->order ?? $component->created_at;
                })
                ->groupBy(function ($point) {
                    return $point->inspection_point->component->id ?? 0;
                });
        @endphp

        <!-- Components Section -->
        <div class="components-section">
            @foreach($groupedMenuPoints as $componentId => $points)
                @php
                    $componentName = optional($points->first()->inspection_point->component)->name ?? 'Tanpa Komponen';
                    $points = $points->sortBy(function ($point) {
                        return $point->order ?? $point->created_at;
                    });
                    
                    // Filter untuk menentukan apakah komponen ini ditampilkan
                    $showComponent = false;
                    $isDocumentComponent = $componentName == 'Dokumen';
                    $isCarPhotoComponent = $componentName == 'Foto Kendaraan';
                    $isFrameComponent = $componentName == 'Rangka (Validasi Tabrak)';
                    $isInteriorComponent = $componentName == 'Interior (Validasi Banjir)';
                    
                    // Komponen Foto Kendaraan selalu ditampilkan
                    if ($isCarPhotoComponent) {
                        $showComponent = true;
                    }
                    // Komponen Rangka hanya ditampilkan jika ada tabrakan
                    elseif ($isFrameComponent && $collision == 'yes') {
                        $showComponent = true;
                    }
                    // Komponen Interior hanya ditampilkan jika ada banjir
                    elseif ($isInteriorComponent && $flooded == 'yes') {
                        $showComponent = true;
                    }
                    // Komponen lainnya ditampilkan jika ada data
                    else {
                        if ($isDocumentComponent) {
                            // Untuk Dokumen, cek apakah ada data selain type image
                            foreach ($points as $point) {
                                $inputType = $point->input_type ?? '';
                                if ($inputType !== 'image') {
                                    $result = $point->inspection_point->results->first();
                                    $hasResult = $result && (!empty($result->status) || !empty($result->note));
                                    if ($hasResult) {
                                        $showComponent = true;
                                        break;
                                    }
                                }
                            }
                        } else {
                            // Untuk komponen lain selain Dokumen, Foto Kendaraan, Rangka, dan Interior
                            if (!in_array($componentName, ['Rangka (Validasi Tabrak)', 'Interior (Validasi Banjir)'])) {
                                foreach ($points as $point) {
                                    $result = $point->inspection_point->results->first();
                                    $hasResult = $result && (!empty($result->status) || !empty($result->note));
                                    if ($hasResult) {
                                        $showComponent = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                @endphp

                @if($showComponent)
                    @if(in_array($componentName, ['Dokumen', 'Foto Kendaraan', 'Rangka (Validasi Tabrak)', 'Interior (Validasi Banjir)']))
                        <div class="page-break"></div>
                    @endif
                    
                    <div class="component-card">
                        <div class="component-header">
                            {{ $componentName ?? 'Tanpa Komponen' }}
                        </div>
                        
                        <div class="component-content">
                            @if($isCarPhotoComponent)
                                <!-- Foto Kendaraan -->
                                @php
                                    $allCarImages = [];
                                    
                                    // 1. Gambar dari komponen Foto Kendaraan sendiri
                                    foreach($points as $point) {
                                        if($point->inspection_point->images) {
                                            foreach($point->inspection_point->images as $img) {
                                                $allCarImages[] = [
                                                    'image' => $img,
                                                    'point_name' => $img->point->name ?? $point->inspection_point->name ?? 'Untitled'
                                                ];
                                            }
                                        }
                                    }
                                    
                                    // 2. Gambar dari Rangka (Validasi Tabrak) - hanya status baik
                                    if ($collision == 'yes') {
                                        $framePoints = $groupedMenuPoints->first(function ($points, $id) {
                                            $componentName = optional($points->first()->inspection_point->component)->name ?? '';
                                            return $componentName == 'Rangka (Validasi Tabrak)';
                                        });
                                        
                                        if ($framePoints) {
                                            foreach($framePoints as $point) {
                                                $result = $point->inspection_point->results->first();
                                                $status = strtolower($result->status ?? '');
                                                $goodStatuses = ['ada', 'ok', 'baik', 'normal', 'good'];
                                                
                                                $isGoodStatus = false;
                                                foreach($goodStatuses as $goodStatus) {
                                                    if (strpos($status, $goodStatus) !== false) {
                                                        $isGoodStatus = true;
                                                        break;
                                                    }
                                                }
                                                
                                                if ($isGoodStatus && $point->inspection_point->images) {
                                                    foreach($point->inspection_point->images as $img) {
                                                        $allCarImages[] = [
                                                            'image' => $img,
                                                            'point_name' => $img->point->name ?? $point->inspection_point->name ?? 'Untitled'
                                                        ];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    
                                    // 3. Gambar dari Interior (Validasi Banjir) - hanya status baik
                                    if ($flooded == 'yes') {
                                        $interiorPoints = $groupedMenuPoints->first(function ($points, $id) {
                                            $componentName = optional($points->first()->inspection_point->component)->name ?? '';
                                            return $componentName == 'Interior (Validasi Banjir)';
                                        });
                                        
                                        if ($interiorPoints) {
                                            foreach($interiorPoints as $point) {
                                                $result = $point->inspection_point->results->first();
                                                $status = strtolower($result->status ?? '');
                                                $goodStatuses = ['ada', 'ok', 'baik', 'normal', 'good'];
                                                
                                                $isGoodStatus = false;
                                                foreach($goodStatuses as $goodStatus) {
                                                    if (strpos($status, $goodStatus) !== false) {
                                                        $isGoodStatus = true;
                                                        break;
                                                    }
                                                }
                                                
                                                if ($isGoodStatus && $point->inspection_point->images) {
                                                    foreach($point->inspection_point->images as $img) {
                                                        $allCarImages[] = [
                                                            'image' => $img,
                                                            'point_name' => $img->point->name ?? $point->inspection_point->name ?? 'Untitled'
                                                        ];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                
                                @if(count($allCarImages) > 0)
                                    <div class="image-gallery">
                                        <h4 class="gallery-title">Foto Kendaraan</h4>
                                        <div class="image-grid">
                                            @foreach($allCarImages as $item)
                                                <div class="image-item">
                                                    <div class="image-label">{{ $item['point_name'] }}</div>
                                                    <div class="image-container">
                                                        @php
                                                            // Helper function untuk mendapatkan path gambar
                                                            function getImagePath($path) {
                                                                if (empty($path)) return null;
                                                                
                                                                // Coba berbagai lokasi
                                                                $locations = [
                                                                    base_path('public/' . $path),
                                                                    storage_path('app/public/' . $path),
                                                                    public_path($path)
                                                                ];
                                                                
                                                                foreach ($locations as $location) {
                                                                    if (file_exists($location)) {
                                                                        return $location;
                                                                    }
                                                                }
                                                                
                                                                return null;
                                                            }
                                                            
                                                            $imagePath = getImagePath($item['image']->image_path);
                                                        @endphp
                                                        
                                                        @if($imagePath)
                                                            <img src="{{ $imagePath }}" alt="{{ $item['point_name'] }}">
                                                        @else
                                                            <div class="image-placeholder">Gambar tidak ditemukan</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            
                            @elseif($isDocumentComponent)
                                <!-- Dokumen - Data dan Gambar -->
                                @php
                                    $displayPoints = [];
                                    $documentImages = [];
                                    foreach($points as $point) {
                                        $inputType = $point->input_type ?? '';
                                        
                                        if ($inputType === 'image') {
                                            // Kumpulkan gambar untuk dokumen
                                            if ($point->inspection_point->images) {
                                                foreach($point->inspection_point->images as $img) {
                                                    $documentImages[] = [
                                                        'image' => $img,
                                                        'point_name' => $img->point->name ?? $point->inspection_point->name ?? 'Untitled'
                                                    ];
                                                }
                                            }
                                            continue;
                                        }
                                        
                                        $result = $point->inspection_point->results->first();
                                        $hasResult = $result && (!empty($result->status) || !empty($result->note));
                                        
                                        if (!$hasResult) {
                                            continue;
                                        }
                                        
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
                                        $isRadioType = in_array($inputType, ['radio', 'imageTOradio']);
                                        
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
                                            'inputType' => $inputType,
                                            'statusArray' => $statusArray,
                                            'isRadioType' => $isRadioType,
                                            'displayNotes' => $displayNotes,
                                        ];
                                    }
                                @endphp
                                
                                @if(count($displayPoints) > 0)
                                    <table class="data-table-two-columns">
                                        @foreach($displayPoints as $display)
                                        <tr>
                                            <td style="width: 40%;">
                                                <strong>{{ $display['point']->inspection_point->name ?? '-' }}</strong>
                                            </td>
                                            <td style="width: 60%;">
                                                @if($display['isRadioType'] && !empty($display['statusArray']))
                                                    @foreach($display['statusArray'] as $status)
                                                        @php
                                                            $statusClass = 'status-badge ';
                                                            if (in_array(strtolower($status), ['normal', 'ada', 'baik', 'good', 'ok'])) {
                                                                $statusClass .= 'status-good';
                                                            } elseif (in_array(strtolower($status), ['tidak normal', 'tidak ada', 'rusak', 'bad', 'not ok'])) {
                                                                $statusClass .= 'status-bad';
                                                            } else {
                                                                $statusClass .= 'status-warning';
                                                            }
                                                        @endphp
                                                        <span class="{{ $statusClass }}">{{ $status }}</span>
                                                    @endforeach
                                                @endif
                                                
                                                @if (!empty($display['displayNotes']))
                                                    @foreach($display['displayNotes'] as $note)
                                                        {{ $note }}
                                                    @endforeach
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </table>
                                @endif
                                
                                <!-- Foto Dokumen -->
                                @if(count($documentImages) > 0)
                                    <div class="image-gallery">
                                        <h4 class="gallery-title">Foto Dokumen</h4>
                                        <div class="image-grid">
                                            @foreach($documentImages as $item)
                                                <div class="image-item">
                                                    <div class="image-label">{{ $item['point_name'] }}</div>
                                                    <div class="image-container">
                                                        @php
                                                            $imagePath = getImagePath($item['image']->image_path);
                                                        @endphp
                                                        
                                                        @if($imagePath)
                                                            <img src="{{ $imagePath }}" alt="{{ $item['point_name'] }}">
                                                        @else
                                                            <div class="image-placeholder">Gambar tidak ditemukan</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            
                            @else
                                <!-- Komponen Lainnya -->
                                @php
                                    $displayPoints = [];
                                    $damagePhotos = [];
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
                                        $isRadioType = in_array($inputType, ['radio', 'imageTOradio']);
                                        
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
                                            'inputType' => $inputType,
                                            'statusArray' => $statusArray,
                                            'isRadioType' => $isRadioType,
                                            'displayNotes' => $displayNotes,
                                        ];
                                        
                                        // Kumpulkan gambar untuk Kerusakan Lainnya
                                        // Hanya untuk status yang TIDAK baik
                                        $hasImage = $point->inspection_point->images && $point->inspection_point->images->count() > 0;
                                        if ($hasImage && $isRadioType && !empty($statusArray)) {
                                            $status = strtolower(implode(',', $statusArray));
                                            $goodStatuses = ['ada', 'ok', 'baik', 'normal', 'good'];
                                            $isGoodStatus = false;
                                            
                                            foreach($goodStatuses as $goodStatus) {
                                                if (strpos($status, $goodStatus) !== false) {
                                                    $isGoodStatus = true;
                                                    break;
                                                }
                                            }
                                            
                                            // Jika status TIDAK baik, tambahkan ke damagePhotos
                                            if (!$isGoodStatus) {
                                                foreach($point->inspection_point->images as $img) {
                                                    $damagePhotos[] = [
                                                        'image' => $img,
                                                        'point_name' => $img->point->name ?? $point->inspection_point->name ?? 'Untitled',
                                                        'component_name' => $componentName
                                                    ];
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                
                                @if(count($displayPoints) > 0)
                                    <table class="data-table-two-columns">
                                        @foreach($displayPoints as $display)
                                        <tr>
                                            <td style="width: 40%;">
                                                <strong>{{ $display['point']->inspection_point->name ?? '-' }}</strong>
                                            </td>
                                            <td style="width: 60%;">
                                                @if($display['isRadioType'] && !empty($display['statusArray']))
                                                    @foreach($display['statusArray'] as $status)
                                                        @php
                                                            $statusClass = 'status-badge ';
                                                            if (in_array(strtolower($status), ['normal', 'ada', 'baik', 'good', 'ok'])) {
                                                                $statusClass .= 'status-good';
                                                            } elseif (in_array(strtolower($status), ['tidak normal', 'tidak ada', 'rusak', 'bad', 'not ok'])) {
                                                                $statusClass .= 'status-bad';
                                                            } else {
                                                                $statusClass .= 'status-warning';
                                                            }
                                                        @endphp
                                                        <span class="{{ $statusClass }}">{{ $status }}</span>
                                                    @endforeach
                                                @endif
                                                
                                                @if (!empty($display['displayNotes']))
                                                    @foreach($display['displayNotes'] as $note)
                                                        {{ $note }}
                                                    @endforeach
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </table>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Kerusakan Lainnya -->
        @if(isset($damagePhotos) && count($damagePhotos) > 0)
            <div class="page-break"></div>
            <div class="damage-section">
                <div class="damage-header">
                    Kerusakan Lainnya
                </div>
                
                <div class="component-content">
                    <div class="image-gallery">
                        <h4 class="gallery-title">Foto Kerusakan</h4>
                        <div class="image-grid">
                            @foreach($damagePhotos as $item)
                                <div class="image-item">
                                    <div class="image-label">
                                        {{ $item['point_name'] }}<br>
                                        <small>({{ $item['component_name'] }})</small>
                                    </div>
                                    <div class="image-container">
                                        @php
                                            $imagePath = getImagePath($item['image']->image_path);
                                        @endphp
                                        
                                        @if($imagePath)
                                            <img src="{{ $imagePath }}" alt="{{ $item['point_name'] }}">
                                        @else
                                            <div class="image-placeholder">Gambar tidak ditemukan</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Repair Estimates -->
        @if(isset($repairEstimations) && $repairEstimations->count() > 0)
            <div class="page-break"></div>
            <div class="repair-estimates">
                <h3>Estimasi Perbaikan</h3>
                <table class="estimates-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No.</th>
                            <th>Nama Part & Deskripsi</th>
                            <th>Urgency & Status</th>
                            <th style="text-align: right;">Estimasi Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($repairEstimations as $index => $estimation)
                            <tr>
                                <td style="width: 40px;">{{ $index + 1 }}.</td>
                                <td>
                                    <strong>{{ $estimation->part_name }}</strong>
                                    @if($estimation->repair_description)
                                        <div style="margin-top: 5px; font-size: 12px; color: #666;">
                                            {{ $estimation->repair_description }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $estimation->urgency === 'segera' ? 'urgency-soon' : 'urgency-long' }}">
                                        {{ $estimation->urgency === 'segera' ? 'Segera' : 'Jangka Panjang' }}
                                    </span>
                                    <br>
                                    <span class="status-badge-sm">
                                        {{ $estimation->status === 'perlu' ? 'perlu' : ($estimation->status === 'disarankan' ? 'disarankan' : 'opsional') }}
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight: 600;">
                                    Rp {{ number_format($estimation->estimated_cost, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                        
                        <tr class="total-row">
                            <td colspan="3" style="text-align: right; font-weight: 700;">Total Estimasi:</td>
                            <td style="text-align: right;" class="total-amount">
                                Rp {{ number_format($totalRepairCost ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        @if($inspection->notes)
        <!-- Final Conclusion -->
        <div class="inspection-conclusion" style="margin-top: 30px;">
            <h3>Kesimpulan Akhir Inspeksi</h3>
            <div class="conclusion-content">{!! $inspection->notes !!}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="footer-left">
                <p>Laporan ini dibuat secara otomatis oleh sistem AutoVerse.</p>
            </div>
            <div class="footer-right">
                <p>Terima kasih telah menggunakan layanan kami.</p>
            </div>
        </div>
    </div>
</body>
</html>