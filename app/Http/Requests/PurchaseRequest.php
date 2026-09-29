<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    
    public function rules()
    {
        return [

            'purchase.supplier_id'

            =>'required',

            'purchase.branch_id'   => 'required|exists:branches,id',

            'purchase.purchase_date'

            =>'required',

            'items'

            =>'required|array|min:1'

        ];
    }
}
