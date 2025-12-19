<?php

namespace App\Services;

use App\Models\DataInspection\Inspection;
use App\Models\DataInspection\InspectionImage;
use App\Models\DataInspection\MenuPoint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Throwable;

class InspectionmPdfGenerator
{
    /**
     * Generate protected PDF untuk inspection dan simpan ke storage (folder report2)
     *
     * @param Inspection $inspection
     * @return string|null Path file PDF yang disimpan (relative to storage/app/public) or null on failure
     */
    public function generate(Inspection $inspection)
    {
        try {
            // Ambil data menu points & images
            $menu_points = MenuPoint::with([
                'app_menu',
                'inspection_point',
                'inspection_point.component',
                'inspection_point.results' => function ($q) use ($inspection) {
                    $q->where('inspection_id', $inspection->id);
                },
                'inspection_point.images' => function ($q) use ($inspection) {
                    $q->where('inspection_id', $inspection->id)
                      ->orderBy('created_at', 'asc');
                }
            ])
            ->whereHas('app_menu', function ($query) use ($inspection) {
                $query->where('category_id', $inspection->category_id);
            })
            ->get();

                    //Filter untuk ambil data lain-lain
            $carOtherNames = [
                'Pajak Tahunan',
                'Pajak 5 Tahunan',
                'PKB',
                'Kepemilikan',
                'BS/BM',
            ];

            $dataCarOther = $menu_points
                ->filter(fn ($item) =>
                    $item->inspection_point &&
                    in_array($item->inspection_point->name, $carOtherNames)
                )
                ->mapWithKeys(function ($item) {
                    $result = $item->inspection_point->results->first();

                    return [
                        Str::camel(str_replace(['/', ' '], '_', $item->inspection_point->name)) => [
                            'status' => $result->status ?? null,
                            'note'   => $result->note ?? null,
                        ]
                    ];
                });

            // Ambil cover image (Depan Kanan), jika tidak ada pakai image pertama
            $coverImage = InspectionImage::where('inspection_id', $inspection->id)
                ->whereHas('point', function ($q) {
                    $q->where('name', 'Depan Kanan');
                })
                ->first();

            if (!$coverImage) {
                $coverImage = InspectionImage::where('inspection_id', $inspection->id)->first();
            }

                    // Hitung total estimasi perbaikan
            $totalRepairCost = $inspection->repairEstimations->sum('estimated_cost');

            // Jika inspection->code kosong maka generate random 6-digit code dan simpan
            if (empty($inspection->code)) {
                $randomCode = mt_rand(100000, 999999); // 6 digit
                $inspection->update(['code' => (string) $randomCode]);
                // reload variable
                $inspection->refresh();
            }

               // Init mPDF
            $mpdf = new Mpdf([
                'format' => 'A4',
                'margin_left'   => 10,
                'margin_right'  => 10,
                'margin_top'    => 15,
                'margin_bottom' => 15,
                'default_font' => 'dejavusans',
                'default_font_size' => 15
            ]);

            // Proteksi PDF pakai kode inspection
            $mpdf->SetProtection([], (string) $inspection->code, null);

              // Render view blade
            $html = view('inspection.report.mPDF2', [
                'inspection' => $inspection,
                'menu_points' => $menu_points,
                'coverImage' => $coverImage,

                'repairEstimations' => $inspection->repairEstimations,
                'totalRepairCost' => $totalRepairCost,
                'dataCarOther' => $dataCarOther,
                // Kirim informasi role user
                'user_roles' => auth()->user()->roles->pluck('name')->toArray()

            ])->render();

            $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
            
            $filename = 'inspection_report_' . $inspection->plate_number . '_' . time() . '.pdf';
            $directory = 'inspection-reports/' . date('Y/m');

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory, 0755, true);
            }

            $filePath = $directory . '/' . $filename;

            // Simpan file PDF ke storage
            Storage::disk('public')->put($filePath, $mpdf->Output('', 'S')); // 'S' = string

            // Update inspection file path & approved_at jika belum ada
            $inspection->update([
                'file' => $filePath,
                'approved_at' => $inspection->approved_at ?? now(),
            ]);

            // Tambahkan log (asumsi ada method addLog di model)
            if (method_exists($inspection, 'addLog')) {
                $inspection->addLog('pdf_generated', 'PDF inspection berhasil di-generate dan diproteksi ');
            }

            return $filePath;
        } catch (Throwable $e) {
            // log error (boleh ganti ke logger kamu)
            Log::error('InspectionPdfGenerator generate error: ' . $e->getMessage(), [
                'inspection_id' => $inspection->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}
