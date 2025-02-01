<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserRegisterRequest extends FormRequest
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
        // dd('325432');
        return [
            'first_name' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Zء-ي ]+$/u',
            'last_name' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Zء-ي ]+$/u',
            'password' =>  ['required', 'unique:users'],
            'mobile_number' => ['required','string','unique:users'],
            'uuid' => 'required|string'

        ];
    }
}
