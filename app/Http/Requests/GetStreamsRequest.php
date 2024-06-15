<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetStreamsRequest extends FormRequest
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
        return [];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->request->all()) {
                $validator->errors()->add('parameters', 'No se permiten parámetros en esta solicitud.');
            }
        });
    }
}