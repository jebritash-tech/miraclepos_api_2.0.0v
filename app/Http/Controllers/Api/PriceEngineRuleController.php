<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PricingEngineRule;
class PriceEngineRuleController extends Controller
{
    public function index()
    {
        return \App\Models\PriceEngineRule::

        orderBy('priority')

        ->get();
    }

    public function update(Request $request,PriceEngineRule $rule)
    {
        $rule->update(

            $request->all()

        );

        return $rule;
    }
}