<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plot_number' => 'required|string|max:50|unique:plots,plot_number,' . $this->plot?->id,
            'section'     => 'nullable|string|max:100',
            'lat'         => 'nullable|numeric|between:-90,90',
            'lng'         => 'nullable|numeric|between:-180,180',
            'shape'       => 'nullable|json',
            'lot_type'    => 'nullable|in:individual,family',
            'dimension'   => 'nullable|string|max:100',
            'capacity'    => 'nullable|integer|min:1|max:255',
            'price'       => 'nullable|numeric|min:0',
            'status'      => 'nullable|in:available,reserved,occupied,full',
            'notes'       => 'nullable|string|max:1000',
        ];
    }
}
