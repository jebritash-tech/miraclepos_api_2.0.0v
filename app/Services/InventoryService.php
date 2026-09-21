<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\MedicineBatch;

class InventoryService
{
    /**
     * زيادة المخزون عند الشراء
     */
    public function increase(MedicineBatch $batch): Inventory
    {
        $inventory = Inventory::firstOrCreate(
            [
                'medicine_id' => $batch->medicine_id,
                'branch_id'   => $batch->branch_id,
            ],
            [
                'quantity' => 0
            ]
        );

        $inventory->quantity += $batch->quantity;
        $inventory->save();

        

        return $inventory;
    }
    /**
     * خصم المخزون من دفعة محددة (يُستخدم في POS)
     *
     * @param MedicineBatch $batch      الدفعة
     * @param int           $quantityBase الكمية بالوحدة الأساسية
     * @return Inventory
     * @throws \RuntimeException
     */
    public function decreaseBatch(MedicineBatch $batch, float $quantityBase): Inventory
    {
        if ($quantityBase <= 0) {
            throw new \RuntimeException('الكمية يجب أن تكون أكبر من صفر');
        }

        // ✅ تحقق من كفاية المخزون
        if ($batch->remaining_quantity < $quantityBase) {
            throw new \RuntimeException(
                "المخزون غير كافٍ في الدفعة #{$batch->batch_number}: " .
                "المتاح {$batch->remaining_quantity}، المطلوب {$quantityBase}"
            );
        }

        // 1. خصم من الدفعة
        $batch->decrement('remaining_quantity', $quantityBase);

        // 2. خصم من المخزون الإجمالي
        $inventory = Inventory::where('medicine_id', $batch->medicine_id)
            ->where('branch_id', $batch->branch_id)
            ->first();

        if (!$inventory) {
            // إنشاء سجل inventory إن لم يكن موجوداً (سيناريو غير متوقع)
            $inventory = Inventory::create([
                'medicine_id' => $batch->medicine_id,
                'branch_id'   => $batch->branch_id,
                'quantity'    => 0,
            ]);
        }

        $inventory->quantity = max(0, $inventory->quantity - $quantityBase);
        $inventory->save();

        return $inventory;
    }
    /**
     * إنقاص المخزون عند البيع
     */
    public function decrease(
        MedicineBatch $batch,
        int $quantity
    ): Inventory {

        $inventory = Inventory::where(
            'medicine_id',
            $batch->medicine_id
        )
        ->where(
            'branch_id',
            $batch->branch_id
        )
        ->firstOrFail();

        $batch->remaining_quantity -= $quantity;
        $batch->save();

        $inventory->quantity -= $quantity;
        $inventory->save();

        

        return $inventory;
    }
}