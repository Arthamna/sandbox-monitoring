<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitCtfMatchRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            // 'user_id' => ['required', 'uuid', 'exists:users,id'],
            'user_id' => ['required', 'uuid'],
            'score_delta' => ['required', 'integer'],
            // 'score_delta' => ['required', 'integer', 'min:1'],
        ];
    }
}
