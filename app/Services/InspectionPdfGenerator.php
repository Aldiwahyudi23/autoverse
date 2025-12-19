<?php

namespace App\Services;

use App\Models\DataInspection\Inspection;
use App\Models\DataInspection\InspectionImage;
use App\Models\DataInspection\MenuPoint;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Throwable;

class InspectionPdfGenerator
{
    /**
     * Generate PDF untuk inspection dan simpan ke storage
     *
     * @param Inspection $inspection
     * @return string|null Path file PDF yang disimpan
     */
    public function generate(Inspection $inspection)
    {
        try {
            // Ambil data menu points & images - struktur sama dengan mPDF
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

            // Filter untuk ambil data lain-lain - sama dengan mPDF
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

            // Ambil cover image (Depan Kanan) - sama dengan mPDF
            $coverImage = InspectionImage::where('inspection_id', $inspection->id)
                ->whereHas('point', function ($q) {
                    $q->where('name', 'Depan Kanan');
                })
                ->first();

            if (!$coverImage) {
                $coverImage = InspectionImage::where('inspection_id', $inspection->id)->first();
            }

            // Hitung total estimasi perbaikan - sama dengan mPDF
            $totalRepairCost = $inspection->repairEstimations->sum('estimated_cost');

            // Jika inspection->code kosong maka generate random 6-digit code dan simpan - sama dengan mPDF
            if (empty($inspection->code)) {
                $randomCode = mt_rand(100000, 999999); // 6 digit
                $inspection->update(['code' => (string) $randomCode]);
                // reload variable
                $inspection->refresh();
            }

            // Generate PDF - TANPA PROTEKSI (berbeda dengan mPDF)
            $pdf = Pdf::loadView('inspection.report.domPDF1', [
                'inspection' => $inspection,
                'menu_points' => $menu_points,
                'coverImage' => $coverImage,
                'repairEstimations' => $inspection->repairEstimations,
                'totalRepairCost' => $totalRepairCost,
                'dataCarOther' => $dataCarOther,
                // Kirim informasi role user - sama dengan mPDF
                'user_roles' => auth()->user()->roles->pluck('name')->toArray()
            ])->setPaper('a4', 'portrait');

            // Tentukan nama & folder file
            $filename = 'inspection_report_' . $inspection->plate_number . '_' . time() . '.pdf';
            $directory = 'inspection-reports/' . date('Y/m');

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory, 0755, true);
            }

            $filePath = $directory . '/' . $filename;

            // Simpan ke storage
            Storage::disk('public')->put($filePath, $pdf->output());

            // Update inspection file path & approved_at jika belum ada
            $inspection->update([
                'file' => $filePath,
                'approved_at' => $inspection->approved_at ?? now(),
            ]);

            // Tambahkan log
            $inspection->addLog('pdf_generated', 'PDF inspection berhasil di-generate otomatis');

            return $filePath;

        } catch (Throwable $e) {
            // log error
            Log::error('InspectionPdfGenerator generate error: ' . $e->getMessage(), [
                'inspection_id' => $inspection->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}