<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\MedicineBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryLog;

class InventoryController extends Controller
{
    /**
     * Display a listing of the inventory items with related details.
     */
    public function index(Request $request)
    {
        $branchId = $request->query('branch_id');
        $search = $request->query('search');
        $status = $request->query('status');

        $query = Inventory::with([
            'medicine.units',
            'medicine.batches.prices.unit'
        ])
        ->when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
        ->when($search, function ($q) use ($search) {
            $q->whereHas('medicine', function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhereHas('batches', fn($b) => $b->where('batch_number', 'like', "%{$search}%"));
            });
        });

        // Optional status filtering (low, out, etc.)
        if ($status === 'low') {
            $query->whereColumn('quantity', '<=', 'minimum_quantity')->where('quantity', '>', 0);
        } elseif ($status === 'out') {
            $query->where('quantity', '<=', 0);
        }

        $inventories = $query->paginate(20);

        // Map inventory items to include formatted converted quantities based on medicine_units factor
        $inventories->getCollection()->transform(function ($inventory) {
            $medicineId = $inventory->medicine_id;
            
            // Get the first active batch for this inventory item to see what unit it was purchased/stored with
            $batch = $inventory->relationLoaded('medicine') && $inventory->medicine 
                ? $inventory->medicine->batches->where('branch_id', $inventory->branch_id)->first() 
                : null;

            $unitRecord = null;
            if ($batch && $batch->purchase_unit_id) {
                // Look up the exact medicine_units pivot row using the batch's purchase_unit_id
                $unitRecord = DB::table('medicine_units')
                    ->where('id', $batch->purchase_unit_id)
                    ->first();
            }

            // Fallback to the first unit configuration if no batch unit is found
            if (!$unitRecord) {
                $unitRecord = DB::table('medicine_units')
                    ->where('medicine_id', $medicineId)
                    ->orderBy('is_base', 'desc')
                    ->first();
            }

            $globalUnit = $unitRecord ? DB::table('units')->where('id', $unitRecord->unit_id)->first() : null;
            $unitName = $globalUnit->name ?? 'قطعة';
            $factor = $unitRecord && $unitRecord->factor > 0 ? (float) $unitRecord->factor : 1;
            
            $baseQty = (float) $inventory->quantity;
            $convertedQty = $factor > 0 ? $baseQty / $factor : $baseQty;

            $inventory->formatted_stock = number_format($convertedQty, 0) . ' ' . $unitName;
            $inventory->unit_name = $unitName;

            return $inventory;
        });

        // Optional Dashboard metrics matching your frontend expectations
        $dashboard = [
            'total_items' => Inventory::when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))->count(),
            'low_stock' => Inventory::when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))->whereColumn('quantity', '<=', 'minimum_quantity')->where('quantity', '>', 0)->count(),
            'out_stock' => Inventory::when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))->where('quantity', '<=', 0)->count(),
            'last_inventory' => now()->format('Y-m-d')
        ];

        return response()->json([
            'data' => $inventories,
            'dashboard' => $dashboard
        ]);
    }

    /**
     * Adjust inventory stock quantities manually.
     */
    public function adjust(Request $request)
    {
        $request->validate([
            'medicine_id'        => 'required|exists:medicines,id',
            'branch_id'          => 'required|exists:branches,id',
            'purchased_quantity' => 'required|numeric|min:0', // Total calculated base quantity from frontend
            'factor'             => 'required|numeric|min:0.01', 
            'notes'              => 'nullable|string',
            'batch_id'           => 'nullable|exists:medicine_batches,id' // Optional target batch ID
        ]);

        DB::beginTransaction();

        try {
            $totalCalculatedQuantity = $request->purchased_quantity;

            $inventory = Inventory::where('branch_id', $request->branch_id)
                                ->where('medicine_id', $request->medicine_id)
                                ->first();

            if (!$inventory) {
                $inventory = Inventory::create([
                    'branch_id'          => $request->branch_id,
                    'medicine_id'        => $request->medicine_id,
                    'purchased_quantity' => $request->purchased_quantity,
                    'factor'             => $request->factor,
                    'quantity'           => $totalCalculatedQuantity,
                ]);
            } else {
                $inventory->purchased_quantity = $request->purchased_quantity;
                $inventory->factor = $request->factor;
                $inventory->quantity = $totalCalculatedQuantity;
                $inventory->save();
            }

            // Update specific batch if provided, otherwise update the latest batch for this medicine/branch
            if ($request->filled('batch_id')) {
                $batch = MedicineBatch::where('id', $request->batch_id)
                                      ->where('branch_id', $request->branch_id)
                                      ->where('medicine_id', $request->medicine_id)
                                      ->first();
            } else {
                $batch = MedicineBatch::where('branch_id', $request->branch_id)
                                      ->where('medicine_id', $request->medicine_id)
                                      ->latest('id')
                                      ->first();
            }

            if ($batch) {
                $batch->quantity = $totalCalculatedQuantity;
                $batch->remaining_quantity = $totalCalculatedQuantity;
                $batch->save();
            }
            
            DB::commit();

                return response()->json([
                    'message' => 'تم تحديث المخزون والحسابات بنجاح',
                    'inventory' => $inventory,
                    'inventories' => $this->getInventoryList($request),
                    'dashboard' => $this->getDashboardMetrics($request)
                ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'فشل في التعديل', 'error' => $e->getMessage()], 500);
        }
    }

    private function getInventoryList(Request $request)
    {
        // نفس منطق `index` ولكن بدون pagination أو مع pagination
        // يمكنك إعادة استخدام الكود من `index` مع تعديل بسيط
    }

    private function getDashboardMetrics(Request $request)
    {
        // حساب metrics
    }
}
