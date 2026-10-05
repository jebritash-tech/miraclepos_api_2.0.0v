<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicineBatch;
use App\Models\PriceEngineRule;
use App\Services\PriceEngineService;

use Illuminate\Http\Request;
use App\Models\Medicine;
class PriceEngineController extends Controller
{
    protected PriceEngineService $engine;

    public function __construct(PriceEngineService $engine)
    {
        $this->engine = $engine;
    }

    /*
    |--------------------------------------------------------------------------
    | Rules
    |--------------------------------------------------------------------------
    */
    public function rules()
    {
        $rules = PriceEngineRule::query()

            ->orderBy('sort_order')

            ->orderBy('id')

            ->get();

        return response()->json([

            'success' => true,

            'data' => $rules

        ]);
    }
    public function index()
    {
        return PriceEngineRule::orderBy('sort_order')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([

            'name' => [
                'required',
                'string',
                'max:100'
            ],

            'type' => [
                'required',
                'in:percentage,fixed,multiply'
            ],

            'value' => [
                'required',
                'numeric'
            ],

            'apply_on' => [
                'required',
                'in:buy_price,sell_price,profit'
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:1'
            ],

            'is_active' => [
                'sometimes',
                'boolean'
            ],

            'is_default' => [
                'sometimes',
                'boolean'
            ],

            'settings' => [
                'nullable',
                'array'
            ],

            'settings.rounding' => [
                'nullable',
                'array'
            ],

            'settings.rounding.mode' => [
                'nullable',
                'in:none,nearest,up,down'
            ],

            'settings.rounding.unit' => [
                'nullable',
                'numeric',
                'gt:0'
            ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Default Rule
        |--------------------------------------------------------------------------
        */

        if (
            ($data['is_default'] ?? false) === true
        ) {

            PriceEngineRule::query()
                ->where('is_default', true)
                ->update([
                    'is_default' => false
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Create
        |--------------------------------------------------------------------------
        */
        // ✅ إذا لم يحدد المستخدم tiered، اجعلها true (متدرج)
        $data['settings'] = $data['settings'] ?? [];
        $data['settings']['rounding'] = $data['settings']['rounding'] ?? ['mode' => 'up'];
        
        if (!array_key_exists('tiered', $data['settings']['rounding'])) {
            $data['settings']['rounding']['tiered'] = true;
        }
        
        // ✅ تأكد من mode
        if (empty($data['settings']['rounding']['mode'])) {
            $data['settings']['rounding']['mode'] = 'up';
        }
        $rule = PriceEngineRule::create($data);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء قاعدة التسعير.',
                'data' => $rule,
                'rules' => PriceEngineRule::orderBy('sort_order')->get()
            ], 201);
    }

    public function update(
        Request $request,
        PriceEngineRule $rule
    ) {
        $data = $request->validate([

            'name' => [
                'required',
                'string',
                'max:100'
            ],

            'type' => [
                'required',
                'in:percentage,fixed,multiply'
            ],

            'value' => [
                'required',
                'numeric'
            ],

            'apply_on' => [
                'required',
                'in:buy_price,sell_price,profit'
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:1'
            ],

            'is_active' => [
                'sometimes',
                'boolean'
            ],

            'is_default' => [
                'sometimes',
                'boolean'
            ],

            'settings' => [
                'nullable',
                'array'
            ],

            'settings.rounding' => [
                'nullable',
                'array'
            ],

            'settings.rounding.mode' => [
                'nullable',
                'in:none,nearest,up,down'
            ],

            'settings.rounding.unit' => [
                'nullable',
                'numeric',
                'gt:0'
            ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Default Rule
        |--------------------------------------------------------------------------
        */

        if (
            ($data['is_default'] ?? false) === true
        ) {

            PriceEngineRule::query()
                ->where('id', '!=', $rule->id)
                ->where('is_default', true)
                ->update([
                    'is_default' => false
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */
        // ✅ نفس المنطق في التحديث
        $data['settings'] = $data['settings'] ?? [];
        $data['settings']['rounding'] = $data['settings']['rounding'] ?? ['mode' => 'up'];
        
        if (!array_key_exists('tiered', $data['settings']['rounding'])) {
            $data['settings']['rounding']['tiered'] = true;
        }
        $rule->update($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث قاعدة التسعير.',
            'data' => $rule->fresh(),
            'rules' => PriceEngineRule::orderBy('sort_order')->get()
        ]);
    }

    public function destroy(PriceEngineRule $rule)
    {
        $rule->delete();

            return response()->json([
                'message' => 'تم حذف القاعدة',
                'rules' => PriceEngineRule::orderBy('sort_order')->get()
            ]);
    }

    public function toggle(PriceEngineRule $rule)
    {
        $rule->update([
            'is_active' => !$rule->is_active
        ]);

        return response()->json([
            'message' => 'تم تغيير حالة القاعدة',
            'rule' => $rule,
            'rules' => PriceEngineRule::orderBy('sort_order')->get()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Prices
    |--------------------------------------------------------------------------
    */

    public function regenerate(MedicineBatch $batch)
    {
        $this->engine->generate($batch);

        return response()->json([
            'message' => 'Prices regenerated successfully.'
        ]);
    }

    public function regenerateAll()
    {
        app(

            PriceEngineService::class

        )->regenerateAll();

        return response()->json([

            'message'=>'All prices regenerated.'

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Simulator
    |--------------------------------------------------------------------------
    */
    public function simulate(Request $request)
    {
        $result = $this->engine->simulate($request);

        return response()->json([

            'success' => true,

            'result' => $result

        ]);
    }

    public function regenerateCurrent()
    {
        $result =
            $this->engine
                ->regenerateCurrentPrices();

        return response()->json([

            'success' => true,

            'message' =>
                'تم تحديث أسعار المخزون الحالي.',

            'data' => $result,

        ]);
    }
}