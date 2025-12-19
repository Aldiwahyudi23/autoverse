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
                <h1 style="font-size: 24px; margin: 0; line-height: 1.2;">
                    <span style="color: #70d874;">Auto</span><span style="color: #000000;">Verse</span>
                </h1>
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
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Tanggal Inspeksi</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ \Carbon\Carbon::parse($inspection->inspection_date)->format('d-m-Y H:i:s') }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Merek</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $inspection->car->brand->name }}</td>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Odo Meter</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $dataCarOther['jarakTempuh(KM)']['note'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Model</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $inspection->car->model->name }}</td>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Pajak 1 Tahunan</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $dataCarOther['pajakTahunan']['note'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Tipe</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $inspection->car->type->name }}</td>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Pajak 5 Tahunan</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $dataCarOther['pajak5Tahunan']['note'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">CC</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $inspection->car->cc }}</td>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Pajak Tahunan</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $dataCarOther['pKB']['note'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Bahan Bakar</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $inspection->car->fuel_type }}</td>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Kepemilikan</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $dataCarOther['kepemilikan']['status'] ?? '' }} {{ $dataCarOther['kepemilikan']['note'] ?? '' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Transmisi</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $inspection->car->transmission }}</td>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Buku Manual</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $dataCarOther['BS/BM']['status'] ?? '' }} {{ $dataCarOther['BS/BM']['note'] ?? '' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;">Tahun Pembuatan</td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;">: {{ $inspection->car->year }}</td>
                <td style="padding: 6px 8px; font-weight: bold; width: 140px; font-size: 12px; vertical-align: top;"></td>
                <td style="padding: 6px 8px; font-size: 12px; vertical-align: top;"></td>
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
                        $collisionColor = '#ffc107';
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
        <div style="margin: 15px 0; padding: 12px; background: #f9f9f9; border-left: 4px solid #0d98d8;">
            <h3 style="margin: 0 0 6px; font-size: 15px;">Kesimpulan Inspeksi:</h3>
            <div style="margin: 0; font-size: 12px; line-height: 1.8;">{!! $inspection->notes !!}</div>
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

    @foreach($groupedMenuPoints as $componentId => $points)
        @php
            $componentName = optional($points->first()->inspection_point->component)->name ?? 'Tanpa Komponen';
            $points = $points->sortBy(function ($point) {
                return $point->order ?? $point->created_at;
            });
            
            $hasData = false;
            if ($componentName == 'Interior (Validasi Banjir)' && $flooded == 'yes') {
                $hasData = true;
            } elseif ($componentName == 'Rangka (Validasi Tabrak)' && $collision == 'yes') {
                $hasData = true;
            } elseif ($componentName == 'Foto Kendaraan') {
                $hasData = $points->flatMap(fn($p) => $p->inspection_point->images)->isNotEmpty();
            } else {
                foreach ($points as $point) {
                    $result = $point->inspection_point->results->first();
                    $hasResult = $result && (!empty($result->status) || !empty($result->note));
                    $hasImage = $point->inspection_point->images && $point->inspection_point->images->count() > 0;
                    if ($hasResult || $hasImage) {
                        $hasData = true;
                        break;
                    }
                }
            }
        @endphp

        @if($hasData)
            @if(in_array($componentName, ['Dokumen', 'Foto Kendaraan', 'Rangka (Validasi Tabrak)', 'Interior (Validasi Banjir)']))
                <div style="page-break-before: always;"></div>
            @endif
            
            <div class="section-box" style="border: 1px solid #0d98d8; border-radius: 10px 10px 0 0; margin-bottom: 20px; overflow: hidden;">
                <div style="font-size: 16px; font-weight: bold; background: #0d98d8; padding: 8px; border-bottom: 1px solid #07a9e9; color: white; position: relative;">
                    {{ $componentName ?? 'Tanpa Komponen' }}
                </div>

                <div style="padding: 10px;">
                    @if ($componentName == 'Interior (Validasi Banjir)')
                        <div style="text-align: center; margin-bottom: 15px;">
                            @if ($flooded === 'yes')
                                <img src="{{ public_path('images/icons/banjir.png') }}" alt="Banjir" style="width: 80px; height: 80px;">
                                <p style="font-weight: bold; font-size: 15px; color: #dc3545;">Bekas Banjir</p>
                            @else
                                <img src="{{ public_path('images/icons/aman.png') }}" alt="Aman" style="width: 80px; height: 80px;">
                                <p style="font-weight: bold; font-size: 15px; color: #28a745;">Bebas Banjir</p>
                            @endif
                        </div>
                    @endif
                    @if ($componentName == 'Rangka (Validasi Tabrak)')
                        <div style="text-align: center; margin-bottom: 15px;">
                            @if ($collision == 'yes')
                                @php
                                    $collisionImage = public_path('images/icons/ringan.png');
                                    $collisionText = 'Tabrak Ringan';
                                    $collisionColor = '#ffc107';
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
                        </div>
                    @endif
@if($componentName == 'Foto Kendaraan' )
    @php
        $allCarImages = [];
        
        // 1. Ambil foto asli dari komponen Foto Kendaraan itu sendiri
        foreach($points as $point) {
            if($point->inspection_point->images) {
                $allCarImages = array_merge($allCarImages, $point->inspection_point->images->all());
            }
        }

        // 2. Ambil foto tambahan dari Exterior dan Interior dengan syarat status tertentu
        $targetExtra = ['Rangka (Validasi Tabrak)', 'Interior (Validasi Banjir)'];
        $validStatuses = ['ok', 'normal', 'good', 'baik'];

        foreach($groupedMenuPoints as $gPoints) {
            $gName = optional($gPoints->first()->inspection_point->component)->name;
            
            if(in_array($gName, $targetExtra)) {
                foreach($gPoints as $p) {
                    $result = $p->inspection_point->results->first();
                    $status = strtolower($result->status ?? '');
                    
                    // Cek jika punya gambar DAN statusnya masuk kriteria valid
                    if($p->inspection_point->images && $p->inspection_point->images->isNotEmpty() && in_array($status, $validStatuses)) {
                        $allCarImages = array_merge($allCarImages, $p->inspection_point->images->all());
                    }
                }
            }
        }

        // 3. Setup Grid (Tetap 4 kolom sesuai struktur Anda)
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

                    {{-- Kolom Kosong untuk menjaga alignment --}}
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
@else
    {{-- Bagian logika else untuk komponen selain Foto Kendaraan tetap sama --}}
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
                                                        $statusColor = '#ffc107';
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
                                                        $statusColor = '#ffc107';
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

                        @foreach($points as $point)
                            @php
                                $result = $point->inspection_point->results->first();
                                $hasResult = $result && (!empty($result->status) || !empty($result->note));
                                $hasImage = $point->inspection_point->images && $point->inspection_point->images->count() > 0;

                                $inputType = $point->input_type ?? '';
                                $selected = $result->status ?? null;

                                if (!$hasImage) {
                                    continue;
                                }

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
                            @endphp
                            
                            @if($showImages)
                                <div style="margin-bottom: 15px; border-bottom: 1px dashed #ccc; padding-bottom: 10px;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr>
                                            <td style="width: 35%; padding: 4px; vertical-align: top; font-weight: bold; font-size: 12px;">{{ $point->inspection_point->name ?? '-' }}</td>
                                        </tr>
                                    </table>
                                    
                                    <div style="margin-top: 10px;">
                                        <table class="image-table">
                                            @php
                                                $images = $point->inspection_point->images;
                                                $columns = 5;
                                                $imageChunks = $images->count() > 0 ? array_chunk($images->all(), $columns) : [[]];
                                            @endphp
                                            @foreach($imageChunks as $chunk)
                                                <tr>
                                                    @foreach($chunk as $img)
                                                        <td style="width: 20%;">
                                                            @if($img->image_path && file_exists(public_path($img->image_path)))
                                                                <div class="image-container">
                                                                    <img src="{{ public_path($img->image_path) }}" alt="image">
                                                                </div>
                                                            @else
                                                                <div class="image-placeholder">
                                                                    <span>Gambar tidak ditemukan</span>
                                                                </div>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                    @for ($i = count($chunk); $i < $columns; $i++)
                                                        <td style="width: 20%;">
                                                            <div class="image-placeholder">
                                                                <span></span>
                                                            </div>
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
                </div>
            </div>
        @endif
    @endforeach

    @if(isset($repairEstimations) && $repairEstimations->count() > 0)
        <div style="margin: 15px 0; padding: 12px; border-left: 4px solid #0d98d8;">
            <h3 style="margin: 0 0 6px; font-size: 15px;">Estimasi Perbaikan:</h3>

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
                                    <span style="color: {{ $estimation->urgency === 'segera' ? '#dc3545' : '#ffc107' }}; font-weight: bold;">
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
        </div>
    @endif

    {{-- @if($inspection->notes)
        <div style="margin: 15px 0; padding: 12px; background: #f9f9f9; border-left: 4px solid #0d98d8;">
            <h3 style="margin: 0 0 6px; font-size: 15px;">Kesimpulan Inspeksi:</h3>
            <div style="margin: 0; font-size: 12px; line-height: 1.8;">{!! $inspection->notes !!}</div>
        </div>
    @endif --}}

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <tr>
            <td style="width: 50%;">
                <p style="margin: 0; font-size: 12px;">Laporan ini dibuat secara otomatis.</p>
            </td>
            <td style="width: 50%; text-align: right;">
                <p style="margin: 0; font-size: 12px;">Terima kasih telah menggunakan layanan kami.</p>
            </td>
        </tr>
    </table>
</body>
</html>