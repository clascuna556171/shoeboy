<?php

namespace App\Http\Requests;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only available pairs may be edited; block before validation so the
        // user gets the friendly "already reserved/sold" notice.
        if ($this->isMethod('put')) {
            $item = $this->route('item');

            return $item ? $item->isEditable() : true;
        }

        return true;
    }

    protected function failedAuthorization(): void
    {
        $item = $this->route('item');

        throw new HttpResponseException(
            back()->with('error', "Item {$item->sku} has already been {$item->status} and can no longer be edited.")
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:150'],
            'listed_price' => ['required', 'numeric', 'min:0'],
            'condition' => ['required', 'string'],
            'size' => ['required', 'string'],
            'repair_cost' => ['nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:50'],
            'triage_status' => ['nullable', Rule::in(Item::TRIAGE_STAGES)],
        ];

        if ($this->isMethod('post')) {
            $rules['batch_id'] = ['required', 'exists:batches,id'];
            $rules['sku'] = ['nullable', 'string', 'max:50', 'unique:items,sku'];
        }

        return $rules;
    }
}
