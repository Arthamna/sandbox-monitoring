<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RebalanceNodesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dry_run' => ['required', 'boolean'],
        ];
    }
}