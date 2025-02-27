<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
        return [
            'category' => 'required|exists:categories,id',
            'brand' => 'required|exists:brands,id',
            'name' => ['required', 'max:255', 'min:3', function ($attribute, $value, $fail) {
                $slug = Str::slug($value);
                $check = Product::whereSlug($slug)->where('id', '!=', $this->id)->first();
                if ($check) {
                    $fail('Product exists');
                }
            }],
            'code' => 'nullable|unique:products,product_code',
            'description' => 'required',
            'price' => 'required|numeric',
            'discountType' => ['nullable', 'in:inr,%'],
            'discountAmt' => ['nullable', 'numeric', function ($attribute, $value, $fail) {
                if ($this->discountType && $value === null) {
                    $fail('Discount amount is required');
                }
                if ($this->discountType === 'inr' && $value >= $this->price) {
                    $fail('Discount amount must be less than price');
                }
                if ($this->discountType === '%' && $value >= 100) {
                    $fail('Discount amount must be less than 100%');
                }
            }],
            'stock' => 'required|numeric',
            'images' => ['required', 'array'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:200'],
        ];
    }

    public function attributes()
    {
        return [
            'category' => 'category',
            'brand' => 'brand',
            'name' => 'name',
            'code' => 'product code',
            'description' => 'description',
            'price' => 'price',
            'discountType' => 'discount type',
            'discountAmt' => 'discount amount',
            'stock' => 'stock',
            'images' => 'images',
        ];
    }

    public function messages()
    {
        return [
            'images.required' => 'At least one image is required',
            '*.required' => ':Attribute is required',
            '*.max' => ':Attribute must be less than :max characters',
            '*.min' => ':Attribute must be at least :min characters',
            '*.exists' => ':Attribute not found',
            '*.unique' => ':Attribute already exists',
            '*.numeric' => ':Attribute must be a number',
            '*.in' => ':Attribute must be one of :values',
            '*.image' => ':Attribute must be an image',
            '*.mimes' => ':Attribute must be a file of type: :values',
            '*.max' => ':Attribute must be less than :max KB',
        ];
    }
}
