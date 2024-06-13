<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostStreamerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'userId' => 'required|string',
            'streamerId' => 'required|string',
        ];
    }


    public function messages(): array
    {
        return [
            'userId.required' => 'El ID de usuario es obligatorio',
            'userId.string' => 'El ID del usuario debe ser una cadena de caracteres.',
            'streamerId.required' => 'La ID del streamer es obligatoria.',
            'streamerId.string' => 'La ID del streamer debe ser una cadena de caracteres.',
        ];
    }

}