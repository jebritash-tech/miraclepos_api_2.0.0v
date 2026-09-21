<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicinePrice;
use App\Models\PriceEngineRule;
use App\Services\AuditLogger;
use App\Services\PriceEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkPricingController extends Controller
{
    /* ============================================================
       POST /api/pricing/bulk-recalculate/preview
       ============================================================ */
    public function preview(Request $request)
    {
        $filters = $request->validate([
            'only_imported'   => 'boolean',
            'only_with_stock' => 'boolean',
            'skip_locked'     => 'boolean',
            'branch_id'       => 'nullable|exists:branches,id',
        ]);

        $query = $this->buildQuery($filters);
        $batchIds = (clone $query)->pluck('id');

        $totalPrices  = MedicinePrice::whereIn('batch_id', $batchIds)->count();
        $lockedPrices = MedicinePrice::whereIn('batch_id', $batchIds)
            ->where('price_mode', 'manual')
            ->count();

        // ✅ دفعات كل أسعارها مقفلة → ستُتجاوز
        $skippedBatches = MedicineBatch::whereIn('id', $batchIds)
            ->whereDoesntHave('prices', fn($q) => $q->where('price_mode', 'auto'))
            ->whereHas('prices')
            ->count();

        $willProcess = max(0, $query->count() - $skippedBatches);

        return response()->json([
            'preview' => [
                'batches'         => $query->count(),
                'batches_skipped' => $skippedBatches,
                'batches_will_process' => $willProcess,
                'prices'          => max(0, $totalPrices - $lockedPrices),
                'locked'          => $lockedPrices,
                'total'           => $totalPrices,
            ],
        ]);
    }

    /* ============================================================
       POST /api/pricing/bulk-recalculate/apply
       ============================================================ */
    public function apply(Request $request)
    {
        $data = $request->validate([
            'only_imported'   => 'boolean',
            'only_with_stock' => 'boolean',
            'skip_locked'     => 'boolean',
            'branch_id'       => 'nullable|exists:branches,id',
            'reason'          => 'required|string|min:3|max:255',
        ]);

        $stats = [
            'batches_processed' => 0,
            'batches_skipped'   => 0,
            'prices_updated'    => 0,
            'prices_skipped'    => 0,
            'failed'            => 0,
        ];

        $query = $this->buildQuery($data);
        $engine = app(PriceEngineService::class);

        $query->chunkById(100, function ($batches) use (&$stats, $engine, $data) {
            foreach ($batches as $batch) {
                try {
                    $lockedCount = MedicinePrice::where('batch_id', $batch->id)
                        ->where('price_mode', 'manual')
                        ->count();

                    $autoCount = MedicinePrice::where('batch_id', $batch->id)
                        ->where('price_mode', 'auto')
                        ->count();

                    // ✅ كل الأسعار مقفلة → تجاوز الدفعة بالكامل
                    if ($autoCount === 0 && $lockedCount > 0 && ($data['skip_locked'] ?? false)) {
                        $stats['batches_skipped']++;
                        $stats['prices_skipped'] += $lockedCount;
                        continue;
                    }

                    // generate() لا يلمس الأسعار المقفلة (محصّن مسبقاً)
                    $updated = $engine->generate($batch);

                    $stats['batches_processed']++;
                    $stats['prices_updated']  += $updated;
                    $stats['prices_skipped']  += $lockedCount;

                } catch (\Throwable $e) {
                    $stats['failed']++;
                    report($e);
                }
            }
        });

        AuditLogger::critical(
            'bulk_pricing_recalculate',
            null,
            "إعادة حساب شاملة: {$stats['batches_processed']} دفعة معالَجة، {$stats['batches_skipped']} متجاوزة، {$stats['prices_updated']} سعر محدّث — السبب: {$data['reason']}",
            [],
            [],
            [
                'filters' => array_diff_key($data, ['reason' => '']),
                'stats'   => $stats,
            ]
        );

        return response()->json([
            'message' => 'تمت إعادة الحساب بنجاح',
            'stats'   => $stats,
        ]);
    }

    /* ============================================================
       Helper — بناء الاستعلام مع كل الفلاتر
       ============================================================ */
    private function buildQuery(array $filters)
    {
        $query = MedicineBatch::query()
            ->with(['medicine.pricingRule', 'purchaseUnit'])
            ->whereHas('medicine');

        // فلتر: المستورد فقط
        if ($filters['only_imported'] ?? false) {
            $query->whereHas('medicine', function ($q) {
                $q->where('pricing_method', 'imported');
            });
        }

        // فلتر: بها مخزون فقط
        if ($filters['only_with_stock'] ?? false) {
            $query->where('remaining_quantity', '>', 0);
        }

        // فلتر: فرع محدد
        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        // ✅ استثنِ الدفعات التي كل أسعارها مقفلة
        if ($filters['skip_locked'] ?? false) {
            $query->whereHas('prices', function ($q) {
                $q->where('price_mode', 'auto');
            });
        }

        return $query;
    }
}