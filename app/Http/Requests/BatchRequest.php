<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $batch = $this->route('batch');

        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'batch_code' => ['required', 'string', 'max:30', $batch
                ? Rule::unique('batches', 'batch_code')->ignore($batch->id)
                : Rule::unique('batches', 'batch_code')],
            'date_acquired' => ['required', 'date'],
            'total_sacks' => ['required', 'integer', 'min:1'],
            'total_pairs' => ['required', 'integer', 'min:1'],
            'total_cost' => ['required', 'numeric', 'min:0'],
        ];
    }
}
