<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocenteEstadoRequest extends FormRequest
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
        $rules = [
            'id_usuario' => ['required','exists:tbl_docentes,usu_id'],
            'fecha_inicial' => ['required','date'],
            'fecha_final' => ['nullable','date','after:fecha_inicial'],
            'id_tipo_docente_estado' => ['required','exists:tbl_tipo_docente_estado,tde_id'],
            'observaciones' => ['nullable','string','max:65535'],
        ];
        if ($this->getMethod() == 'POST') {
            $rules += ['archivo' => ['nullable','file']];
        }
        return $rules;
    }
}
