<?php
namespace App\Services\Pricing;
use App\Models\PriceEngineRule;

class PriceEngine
{
    public function recalculate(

        float $buyPrice

    )
    {
        $price=$buyPrice;

        $rules=

        PriceEngineRule::

        where('enabled',true)

        ->orderBy('priority')

        ->get();

        foreach($rules as $rule){

            switch($rule->type){

                case 'markup':

                    $price+=

                    $price*

                    (

                        $rule->value/100

                    );

                break;

                case 'fixed':

                    $price=

                    $rule->value;

                break;

                case 'rounding':

                    $price=

                    ceil(

                        $price/

                        $rule->value

                    )

                    *

                    $rule->value;

                break;

            }

        }

        return round($price,2);

    }

}