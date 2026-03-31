<?php

namespace App\Services;

use App\Models\Product;

class ProductSearchService
{
    public function search(string $question): array
    {
        $keyword = trim($question);

        $products = Product::with(['category', 'typeproduct', 'attributes'])
            ->where(function ($q) use ($keyword) {
                $q->where('pro_name', 'like', '%' . $keyword . '%')
                  ->orWhere('pro_slug', 'like', '%' . $keyword . '%');

                try {
                    $q->orWhere('pro_description', 'like', '%' . $keyword . '%');
                } catch (\Throwable $e) {
                }

                try {
                    $q->orWhere('pro_content', 'like', '%' . $keyword . '%');
                } catch (\Throwable $e) {
                }
            })
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $result = [];

        foreach ($products as $product) {
            $result[] = [
                'id' => $product->id,
                'name' => $product->pro_name ?? null,
                'slug' => $product->pro_slug ?? null,
                'price' => $product->pro_price ?? null,
                'sale' => $product->pro_sale ?? null,
                'number' => $product->pro_number ?? null,
                'country' => method_exists($product, 'getCountry') ? $product->getCountry() : null,
                'category' => optional($product->category)->c_name,
                'type_product' => optional($product->typeproduct)->tp_name,
                'attributes' => $product->attributes->pluck('at_name')->filter()->values()->toArray(),
            ];
        }

        return $result;
    }
}