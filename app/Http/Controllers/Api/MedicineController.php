<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\PriceEngineRule;

class MedicineController extends Controller
{
   
    public function index()
    {
        $medicines = Medicine::with([
            'category',
            'pricingRule',
            'units.unit'
        ])->get();

        $medicines->each(function ($medicine) {

            $medicine->setRelation(

                'units.unit',

                $medicine->units ?? collect()

            );

        });

        return $medicines;
    }
    public function show(Medicine $medicine)
    {
    
        return $medicine->load([
    
            'category',
    
            'units.unit'
    
        ]);
    
    }
    // جلب دواء واحد بالباركود
    public function showByBarcode($barcode)
    {
        $medicine = Medicine::with('batches')
            ->where('barcode', $barcode)
            ->firstOrFail();

        return response()->json($medicine);
    }

    // إضافة دواء جديد
    public function store(Request $request)
    {

        $validated = $request->validate([

            'name'=>'required|string|max:255',

            'category_id'=>'nullable|exists:categories,id',

            'notes'=>'nullable|string',

            'units'=>'required|array|min:1',

            'units.*.unit_id'=>'required|exists:units,id',

            'units.*.factor'=>'required|integer|min:1',

            'units.*.barcode'=>'nullable|string|max:255',

            'units.*.allow_sale'=>'boolean',

            'units.*.is_base'=>'boolean',

            'units.*.sort_order'=>'nullable|integer',
            'pricing_rule_id' => [
                'nullable',
                'exists:price_engine_rules,id'
            ],
            'pricing_method' => 'required|in:local,imported',

        ]);

        $baseUnits = collect($validated['units'])->where('is_base',true);

        if($baseUnits->count()!=1){
            return response()->json(['message'=>'يجب اختيار وحدة أساسية واحدة.'],422);
        }

        $duplicates=collect($validated['units'])->pluck('unit_id');

        if($duplicates->count()!=$duplicates->unique()->count()){
            
            return response()->json(['message'=>'الوحدة مكررة.'],422);
            
        }

        $barcodes=collect($validated['units'])->pluck('barcode')->filter();

        if($barcodes->count()!=$barcodes->unique()->count()){
            
            return response()->json(['message'=>'يوجد باركود مكرر.'],422);
            
        }

        return DB::transaction(function () use ($validated) {
            /*
            |--------------------------------------------------------------------------
            | Validate Pricing Rule
            |--------------------------------------------------------------------------
            */

            if (
                !empty($validated['pricing_rule_id'])
            ) {

                $ruleExists =
                    \App\Models\PriceEngineRule::query()

                        ->where(
                            'id',
                            $validated['pricing_rule_id']
                        )

                        ->where(
                            'is_active',
                            true
                        )

                        ->exists();

                if (!$ruleExists) {

                    return response()->json([

                        'message' =>
                            'قاعدة التسعير المحددة غير مفعلة.'

                    ], 422);
                }
            }
            $medicine = Medicine::create([

                'name'        => $validated['name'],
            
                'category_id' => $validated['category_id'],
            
                'notes'       => $validated['notes'] ?? null,
                'pricing_method' => $validated['pricing_method'],
                'pricing_rule_id' => $validated['pricing_rule_id'] ?? null,
            
            ]);
            foreach (

                $validated['units']
            
                as
            
                $index => $unit
            
            ) {
            
                MedicineUnit::create([
            
                    'medicine_id' => $medicine->id,
            
                    'unit_id' => $unit['unit_id'],
            
                    'factor' => $unit['factor'],
            
                    'barcode' => $unit['barcode'] ?? null,
            
                    'allow_sale' => $unit['allow_sale'] ?? true,
            
                    'is_base' => $unit['is_base'] ?? false,
            
                    'sort_order' => $unit['sort_order'] ?? ($index + 1),
            
                ]);
            
            }
             return response()->json([
                'message' => 'تم إضافة الدواء بنجاح',
                'medicine' => $medicine->load(['category', 'units.unit', 'pricingRule']),
                'medicines' => Medicine::with(['category', 'units.unit', 'pricingRule'])->get()
            ], 201);

        });
    }

    public function update(Request $request, Medicine $medicine)
    {
       $validated = $request->validate([
            'name'=>'required|string|max:255',
            'category_id'=>'nullable|exists:categories,id',
            'notes'=>'nullable|string',
            'units'=>'required|array|min:1',
            'units.*.unit_id'=>'required|exists:units,id',
            'units.*.factor'=>'required|integer|min:1',
            'units.*.barcode'=>'nullable|string|max:255',
            'units.*.allow_sale'=>'boolean',
            'units.*.is_base'=>'boolean',
            'units.*.sort_order'=>'nullable|integer',
            'pricing_rule_id' => [
                'nullable',
                'exists:price_engine_rules,id'
            ],
            'pricing_method' => 'required|in:local,imported',
        ]);

        $baseUnits = collect($validated['units'])->where('is_base',true);

        if($baseUnits->count()!=1){
            return response()->json(['message'=>'يجب اختيار وحدة أساسية واحدة.'],422);
        }

        $duplicates=collect($validated['units'])->pluck('unit_id');

        if($duplicates->count()!=$duplicates->unique()->count()){
            return response()->json(['message'=>'الوحدة مكررة.'],422);
        }

        $barcodes=collect($validated['units'])->pluck('barcode')->filter();

        if($barcodes->count()!=$barcodes->unique()->count()){
            return response()->json(['message'=>'يوجد باركود مكرر.'],422);
        }

        return DB::transaction(function () use ($validated, $medicine) {
            
            if (
                array_key_exists('pricing_rule_id', $validated)
                && !empty($validated['pricing_rule_id'])
            ) {
                $ruleExists = \App\Models\PriceEngineRule::query()
                    ->where('id', $validated['pricing_rule_id'])
                    ->where('is_active', true)
                    ->exists();

                if (!$ruleExists) {
                    return response()->json([
                        'message' => 'قاعدة التسعير المحددة غير مفعلة.'
                    ], 422);
                }
            }

            $medicine->update([
                'name' => $validated['name'],
                'category_id' => $validated['category_id'],
                'notes' => $validated['notes'] ?? null,
                'pricing_method' => $validated['pricing_method'],
                'pricing_rule_id' => $validated['pricing_rule_id'] ?? null,
            ]);

            // Track incoming unit IDs to update/create instead of deleting everything blindly
            $incomingUnitIds = [];

            foreach ($validated['units'] as $index => $unitData) {
                // Find if this unit relation already exists for this medicine
                $medicineUnit = MedicineUnit::where('medicine_id', $medicine->id)
                    ->where('unit_id', $unitData['unit_id'])
                    ->first();

                if ($medicineUnit) {
                    // Update existing unit entry
                    $medicineUnit->update([
                        'factor' => $unitData['factor'],
                        'barcode' => $unitData['barcode'] ?? null,
                        'allow_sale' => $unitData['allow_sale'] ?? true,
                        'is_base' => $unitData['is_base'] ?? false,
                        'sort_order' => $unitData['sort_order'] ?? ($index + 1),
                    ]);
                    $incomingUnitIds[] = $medicineUnit->id;
                } else {
                    // Create new unit entry
                    $newUnit = MedicineUnit::create([
                        'medicine_id' => $medicine->id,
                        'unit_id' => $unitData['unit_id'],
                        'factor' => $unitData['factor'],
                        'barcode' => $unitData['barcode'] ?? null,
                        'allow_sale' => $unitData['allow_sale'] ?? true,
                        'is_base' => $unitData['is_base'] ?? false,
                        'sort_order' => $unitData['sort_order'] ?? ($index + 1),
                    ]);
                    $incomingUnitIds[] = $newUnit->id;
                }
            }

            // Safely delete only units that were removed from the request AND are NOT tied to active batches
            MedicineUnit::where('medicine_id', $medicine->id)
                ->whereNotIn('id', $incomingUnitIds)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('medicine_batches')
                          ->whereColumn('medicine_batches.purchase_unit_id', 'medicine_units.id');
                })
                ->delete();

                return response()->json([
                    'message' => 'تم تحديث الدواء بنجاح',
                    'medicine' => $medicine->fresh()->load(['category', 'units.unit', 'pricingRule']),
                    'medicines' => Medicine::with(['category', 'units.unit', 'pricingRule'])->get()
                ]);
        });
    }

    public function destroy(Medicine $medicine)
    {
    
        if (
    
            $medicine->batches()->exists()
    
        ) {
    
            return response()->json([
    
                'message' => 'لا يمكن حذف دواء يحتوي على مخزون.'
    
            ],422);
    
        }
    
        //$medicine->units()->delete();
        $medicine->delete();
        return response()->json([
            'message' => 'تم حذف الدواء بنجاح',
            'medicines' => Medicine::with(['category', 'units.unit', 'pricingRule'])->get()
        ]);
    
    }

    public function updatePricingRule(
            Request $request,
            Medicine $medicine
        ) {
            $validated = $request->validate([

                'pricing_rule_id' => [
                    'nullable',
                    'exists:price_engine_rules,id'
                ],

            ]);

            if (
                !empty($validated['pricing_rule_id'])
            ) {

                $active = PriceEngineRule::query()

                    ->where(
                        'id',
                        $validated['pricing_rule_id']
                    )

                    ->where(
                        'is_active',
                        true
                    )

                    ->exists();

                if (!$active) {

                    return response()->json([

                        'message' =>
                            'قاعدة التسعير المحددة غير مفعلة.'

                    ], 422);
                }
            }

            $medicine->update([

                'pricing_rule_id' =>
                    $validated['pricing_rule_id'] ?? null

            ]);

            return response()->json([

                'success' => true,

                'message' =>
                    'تم تحديث قاعدة تسعير الدواء.',

                'medicine' =>
                    $medicine->fresh()->load(
                        'pricingRule'
                    )

            ]);
}
}