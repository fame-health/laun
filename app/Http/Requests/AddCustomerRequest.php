<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name'    => 'required|max:25',
            'email'   => 'nullable|email|unique:users,email',
            'alamat'  => 'required',
            'no_telp' => 'required|unique:users,no_telp',
        ];
    }

    /**
     * Custom error messages
     */
    public function messages()
    {
        return [
            'name.required'      => 'Nama tidak boleh kosong.',
            'name.max'           => 'Nama tidak boleh lebih dari 25 karakter.',

            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah digunakan.',

            'alamat.required'    => 'Alamat tidak boleh kosong.',

            'no_telp.required'   => 'Nomor Telepon tidak boleh kosong.',
            'no_telp.unique'     => 'Nomor Telepon sudah digunakan.',
        ];
    }
}
