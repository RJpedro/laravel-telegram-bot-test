<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ajuste se quiser restrição de acesso
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'active' => 'sometimes|boolean',
            'plan_type' => 'required|string'
        ];
    }
}
