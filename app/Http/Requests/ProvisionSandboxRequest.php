<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProvisionSandboxRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'owner_user_id' => ['required', 'uuid', 'exists:users,id'],
            'kind'          => ['required', 'in:training,competition'],
            'type'          => ['sometimes', 'in:qemu,lxc'],
            'config'        => ['sometimes', 'array'],
            'config.image'  => ['sometimes', 'string'],
            'config.ram'    => ['sometimes', 'integer'],
            'config.cpu'    => ['sometimes', 'integer'],
            'config.storage'=> ['sometimes', 'string'],
            'config.disk'   => ['sometimes', 'integer'],
            'config.features'=> ['sometimes', 'string'],
            'config.virtualization' => ['sometimes', 'boolean'],
        ];
    }
}