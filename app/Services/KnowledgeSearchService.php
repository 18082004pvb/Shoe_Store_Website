<?php

namespace App\Services;

use App\Models\ChatbotDocument;

class KnowledgeSearchService
{
    public function search(string $question): array
    {
        $keyword = trim($question);

        $documents = ChatbotDocument::where('is_active', true)
            ->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                  ->orWhere('slug', 'like', '%' . $keyword . '%')
                  ->orWhere('short_content', 'like', '%' . $keyword . '%')
                  ->orWhere('content', 'like', '%' . $keyword . '%');
            })
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $result = [];

        foreach ($documents as $doc) {
            $result[] = [
                'id' => $doc->id,
                'title' => $doc->title,
                'slug' => $doc->slug,
                'url' => $doc->url,
                'source_type' => $doc->source_type,
                'source_id' => $doc->source_id,
                'short_content' => $doc->short_content,
                'content' => $doc->content,
            ];
        }

        return $result;
    }
}