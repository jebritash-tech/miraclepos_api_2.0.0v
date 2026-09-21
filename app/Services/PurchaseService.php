<?php

namespace App\Services;
use App\Services\InventoryService;
use App\Services\PriceEngineService;
use App\Models\Purchase;
use App\Models\MedicineBatch;
use App\Models\InventoryMovement;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function store(array $data)
    {
        $branchId = $data['purchase']['branch_id'];

        // ✅ احسب subtotal أولاً
        $subtotal = collect($data['items'])->sum(function ($item) {
            return $item['buy_price'] * $item['quantity'];
        });

        $discount = (float) ($data['purchase']['discount'] ?? 0);
        $totalAmount = $subtotal - $discount;

        $purchase = Purchase::create([
            'supplier_id'    => $data['purchase']['supplier_id'],
            'invoice_number' => $data['purchase']['invoice_number'] ?? null,
            'purchase_date'  => $data['purchase']['purchase_date'],
            'exchange_rate'  => $data['purchase']['exchange_rate'] ?? 1,
            'discount'       => $discount,
            'notes'          => $data['purchase']['notes'] ?? null,
            'subtotal'       => $subtotal,        // ✅ جديد
            'total_amount'   => $totalAmount,     // ✅ بعد الخصم
            'user_id'        => auth()->id(),
            'branch_id'      => $branchId,
        ]);

        
        // Items in bascket
        foreach($data['items'] as $row)
        {
            $item = PurchaseItem::create([

                'purchase_id'

                    =>

                    $purchase->id,

                'medicine_id'

                    =>

                    $row['medicine_id'],

                'unit_id'

                    =>

                    $row['unit_id'],

                'quantity'

                    =>

                    $row['quantity'],

                'factor'

                    =>

                    $row['factor'],

                'base_quantity'

                    =>

                    $row['base_quantity'],

                'buy_price'

                    =>

                    $row['buy_price'],

                'subtotal'

                    =>

                    $row['quantity']

                    *

                    $row['buy_price']

            ]);
            // Find the pivot record id from medicine_units table
           // Find the pivot record id from medicine_units table
            $medicineUnit = DB::table('medicine_units')
                ->where('medicine_id', $row['medicine_id'])
                ->where('unit_id', $row['unit_id'])
                ->first();

            $batch = MedicineBatch::create([
                'medicine_id'        => $row['medicine_id'],
                'purchase_item_id'   => $item->id,
                'branch_id'          => $branchId,
                'batch_number'       => $row['batch_number'],
                'expiry_date'        => $row['expiry_date'],
                'buy_price'          => $row['buy_price'],
                'quantity'           => $row['base_quantity'],
                'remaining_quantity' => $row['base_quantity'],
                // Use the pivot record's primary key correctly here:
                'purchase_unit_id'   => $medicineUnit ? $medicineUnit->id : $row['unit_id'],
            ]);
            
            $inventory = app(
                InventoryService::class
            )->increase($batch);

            app(
                PriceEngineService::class
            )->generate($batch);

            InventoryMovement::create([

                'medicine_id'   => $batch->medicine_id,

                'batch_id'      => $batch->id,

                'branch_id'     => $batch->branch_id,

                'type'          => 'purchase',

                'quantity'      => $batch->quantity,

                'balance_after' => $inventory->quantity,

                'reference_type'=> Purchase::class,

                'reference_id'  => $purchase->id,

            ]);

            
        }

        return $purchase->load([
            'supplier',
            'items',
            'items.batch',
            'items.batch.prices',
            'branch',
        ]);
    }
}