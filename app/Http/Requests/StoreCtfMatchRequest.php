<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCtfMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'challenge_key' => ['required', 'string', 'max:100'],
            'mode' => ['sometimes', 'in:vs,multiplayer'],
        ];
    }
}