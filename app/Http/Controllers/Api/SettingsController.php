<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /* ============================================================
       GET /api/settings — كل الإعدادات مجمّعة
       ============================================================ */
    public function index()
    {
        return response()->json([
            'groups' => Setting::byGroup(),
        ]);
    }

    /* ============================================================
       PUT /api/settings — تحديث مجموعة/قيم
       ============================================================ */
    public function update(Request $request)
    {
        $request->validate([
            'settings'   => 'required|array',
            'settings.*' => 'nullable',
        ]);

        $updated = Setting::setMany($request->input('settings'));

        return response()->json([
            'message' => "تم تحديث {$updated} إعداد",
            'updated' => $updated,
            'groups'  => Setting::byGroup(),
        ]);
    }

    /* ============================================================
       GET /api/settings/public — للإعدادات التي يحتاجها POS
       (بدون مصادقة admin، مثل اسم الصيدلية وطباعة)
       ============================================================ */
    public function publicSettings()
    {
        $publicKeys = [
            'pharmacy.name', 'pharmacy.phone', 'pharmacy.address',
            'pharmacy.tax_number', 'pharmacy.currency', 'pharmacy.logo_url',
            'print.enabled', 'print.auto_after_sale', 'print.width', 'print.allow_reprint',
            'pin.enabled', 'pin.length',
            'pin.on_shift_open', 'pin.on_shift_close', 'pin.on_withdraw',
            'pin.on_void_sale', 'pin.on_price_change', 'pin.on_every_sale',
            'pin.on_expense',
            'pin.on_debt_payment',
        ];

        $result = [];
        foreach ($publicKeys as $key) {
            $result[$key] = Setting::get($key);
        }

        return response()->json($result);
    }
}