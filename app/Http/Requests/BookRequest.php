<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
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
          "title" => "required|min:3",
          "author" => "required", 
          "isbn" => "nullable|min:13|max:13|regex:/\d+-\d+-\d+-[\dX]/i",
          "year" => "nullable|integer|min:1500",
        ];
  
    }
}
