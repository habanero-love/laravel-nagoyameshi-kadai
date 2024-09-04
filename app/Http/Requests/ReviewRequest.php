<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
         // 基本のルールセット
         $rules = [
            'score' => 'required|integer|between:1,5',
            'content' => 'required',
        ];

        // リクエストがPATCHまたはPUTの場合は、'required'の前に'sometimes'を追加
        if ($this->isMethod('patch') || $this->isMethod('put')) {
            foreach ($rules as $key => $rule) {
                $rules[$key] = preg_replace('/^required/', 'sometimes|required', $rule);
            }
        }

        return $rules;
    }
}
