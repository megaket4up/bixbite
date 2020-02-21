<?php

namespace BBCMS\Http\Requests\Api\V1\Auth;

use BBCMS\Http\Requests\BaseFormRequest;

use Illuminate\Validation\Rule;

class AuthRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
                'between:6,25',
            ]
        ];
    }
}
