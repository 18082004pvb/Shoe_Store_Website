<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use App\Models\ChatbotDocument;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncChatbotKnowledgeCommand extends Command
{
    protected $signature = 'chatbot:sync-knowledge';
    protected $description = 'Đồng bộ dữ liệu sản phẩm, danh mục, bài viết vào chatbot_documents';

    public function handle()
    {
        $this->syncProducts();
        $this->syncCategories();
        $this->syncArticles();

        $this->info('Đã đồng bộ dữ liệu tri thức cho chatbot.');
        return Command::SUCCESS;
    }

    protected function syncProducts()
    {
        $products = Product::with(['category', 'typeproduct', 'attributes'])->get();

        foreach ($products as $product) {
            $content = $this->buildProductContent($product);

            ChatbotDocument::updateOrCreate(
                [
                    'source_type' => 'product',
                    'source_id' => $product->id,
                ],
                [
                    'title' => $product->pro_name ?? null,
                    'slug' => $product->pro_slug ?? null,
                    'url' => $product->pro_slug ? url('san-pham/' . $product->pro_slug . '-' . $product->id) : null,
                    'content' => $content,
                    'short_content' => Str::limit(strip_tags($content), 500),
                    'is_active' => (int)($product->pro_active ?? 1) === 1,
                    'synced_at' => now(),
                ]
            );
        }

        $this->info('Đã sync products');
    }

    protected function syncCategories()
    {
        $categories = Category::with(['parent', 'children', 'attributes'])->get();

        foreach ($categories as $category) {
            $content = $this->buildCategoryContent($category);

            ChatbotDocument::updateOrCreate(
                [
                    'source_type' => 'category',
                    'source_id' => $category->id,
                ],
                [
                    'title' => $category->c_name ?? null,
                    'slug' => $category->c_slug ?? null,
                    'url' => $category->c_slug ? url('danh-muc/' . $category->c_slug) : null,
                    'content' => $content,
                    'short_content' => Str::limit(strip_tags($content), 500),
                    'is_active' => true,
                    'synced_at' => now(),
                ]
            );
        }

        $this->info('Đã sync categories');
    }

    protected function syncArticles()
    {
        $articles = Article::with(['menu', 'admin'])->get();

        foreach ($articles as $article) {
            $title = $article->a_name ?? $article->a_title ?? $article->title ?? 'Bài viết #' . $article->id;
            $slug = $article->a_slug ?? $article->slug ?? null;

            $content = $this->buildArticleContent($article);

            ChatbotDocument::updateOrCreate(
                [
                    'source_type' => 'article',
                    'source_id' => $article->id,
                ],
                [
                    'title' => $title,
                    'slug' => $slug,
                    'url' => $slug ? url('article/' . $slug) : null,
                    'content' => $content,
                    'short_content' => Str::limit(strip_tags($content), 500),
                    'is_active' => (int)($article->a_active ?? 1) === 1,
                    'synced_at' => now(),
                ]
            );
        }

        $this->info('Đã sync articles');
    }

    protected function buildProductContent($product): string
    {
        $attributeNames = $product->attributes
            ? $product->attributes->pluck('at_name')->filter()->implode(', ')
            : '';

        $parts = [
            'Tên sản phẩm: ' . ($product->pro_name ?? ''),
            'Slug: ' . ($product->pro_slug ?? ''),
            'Giá: ' . ($product->pro_price ?? ''),
            'Giảm giá: ' . ($product->pro_sale ?? ''),
            'Số lượng: ' . ($product->pro_number ?? ''),
            'Quốc gia: ' . (method_exists($product, 'getCountry') ? $product->getCountry() : ''),
            'Danh mục: ' . optional($product->category)->c_name,
            'Loại sản phẩm: ' . optional($product->typeproduct)->tp_name,
            'Thuộc tính: ' . $attributeNames,
            'Mô tả ngắn: ' . ($product->pro_description ?? ''),
            'Nội dung chi tiết: ' . ($product->pro_content ?? ''),
        ];

        return implode("\n", $parts);
    }

    protected function buildCategoryContent($category): string
    {
        $children = $category->children ? $category->children->pluck('c_name')->implode(', ') : '';
        $attributes = $category->attributes ? $category->attributes->pluck('at_name')->implode(', ') : '';

        $parts = [
            'Tên danh mục: ' . ($category->c_name ?? ''),
            'Slug: ' . ($category->c_slug ?? ''),
            'Danh mục cha: ' . optional($category->parent)->c_name,
            'Danh mục con: ' . $children,
            'Thuộc tính: ' . $attributes,
            'Số sản phẩm: ' . $category->products()->count(),
        ];

        return implode("\n", $parts);
    }

    protected function buildArticleContent($article): string
    {
        $parts = [
            'Tiêu đề: ' . ($article->a_name ?? $article->a_title ?? $article->title ?? ''),
            'Slug: ' . ($article->a_slug ?? $article->slug ?? ''),
            'Menu: ' . optional($article->menu)->mn_name,
            'Người tạo: ' . optional($article->admin)->name,
            'Mô tả ngắn: ' . ($article->a_description ?? $article->description ?? ''),
            'Nội dung: ' . strip_tags($article->a_content ?? $article->content ?? ''),
        ];

        return implode("\n", $parts);
    }
}