<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificacionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nombre' => 'required | string | max:255',
            'descripcion' => 'nullable | string | max:255',
            'asunto' => 'nullable | string | max:255',
            'responder_email' => 'nullable | email | max:255',
            'responder_nombre' => 'nullable | string | max:255',
            'fecha' => 'nullable | date',
        ];
    }
}
