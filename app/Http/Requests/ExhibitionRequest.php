<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'condition_id' => ['required', 'integer', 'exists:conditions,id'],
            'price' => ['required', 'integer', 'min:0'],
            'brand_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => '商品名を入力してください',
            'description.required' => '商品の説明を入力してください',
            'description.max' => '商品の説明は255文字以内で入力してください',
            'image.required' => '商品画像を選択してください',
            'image.image' => '商品画像は画像ファイルを選択してください',
            'image.mimes' => '商品画像はJPEGまたはPNG形式で選択してください',
            'category_ids.required' => '商品のカテゴリーを選択してください',
            'category_ids.min' => '商品のカテゴリーを選択してください',
            'category_ids.*.exists' => '選択したカテゴリーが不正です',
            'condition_id.required' => '商品の状態を選択してください',
            'condition_id.exists' => '選択した商品の状態が不正です',
            'price.required' => '販売価格を入力してください',
            'price.integer' => '販売価格は数値で入力してください',
            'price.min' => '販売価格は0円以上で入力してください',
            'brand_name.max' => 'ブランド名は255文字以内で入力してください',
        ];
    }
}
