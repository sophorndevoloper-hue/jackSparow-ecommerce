<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'make_id' => ['nullable', 'exists:makes,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'mpn' => ['nullable', 'string', 'max:100'],
            'upc_ean' => ['nullable', 'string', 'max:50'],
            'hs_code' => ['nullable', 'string', 'max:50'],
            'unspsc_code' => ['nullable', 'string', 'max:50'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'map_price' => ['nullable', 'numeric', 'min:0'],
            'min_margin_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'moq' => ['nullable', 'integer', 'min:1'],
            'case_pack_multiple' => ['nullable', 'integer', 'min:1'],
            'max_order_quantity' => ['nullable', 'integer', 'min:1'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'requires_serial_tracking' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'warranty_period' => ['nullable', 'string', 'max:100'],
            'manufacturer_warranty_months' => ['nullable', 'integer', 'min:0'],
            'distributor_warranty_months' => ['nullable', 'integer', 'min:0'],
            'form_factor' => ['nullable', 'string', 'max:50'],
            'socket' => ['nullable', 'string', 'max:50'],
            'chipset' => ['nullable', 'string', 'max:50'],
            'tdp_watts' => ['nullable', 'integer', 'min:0'],
            'power_requirement_watts' => ['nullable', 'integer', 'min:0'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'length_cm' => ['nullable', 'numeric', 'min:0'],
            'width_cm' => ['nullable', 'numeric', 'min:0'],
            'height_cm' => ['nullable', 'numeric', 'min:0'],
            'is_hazmat' => ['nullable', 'boolean'],
            'spec_keys' => ['nullable', 'array'],
            'spec_keys.*' => ['nullable', 'string', 'max:100'],
            'spec_values' => ['nullable', 'array'],
            'spec_values.*' => ['nullable', 'string', 'max:255'],
            'tier_min_qty' => ['nullable', 'array'],
            'tier_min_qty.*' => ['nullable', 'integer', 'min:1'],
            'tier_max_qty' => ['nullable', 'array'],
            'tier_max_qty.*' => ['nullable', 'integer', 'min:1'],
            'tier_price' => ['nullable', 'array'],
            'tier_price.*' => ['nullable', 'numeric', 'min:0'],
            'tier_customer_group_id' => ['nullable', 'array'],
            'tier_customer_group_id.*' => ['nullable', 'exists:customer_groups,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ];
    }
}
