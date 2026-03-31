<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminChatbotDocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = ChatbotDocument::query()
            ->when($request->q, function ($query) use ($request) {
                $keyword = trim($request->q);

                $query->where(function ($sub) use ($keyword) {
                    $sub->where('title', 'like', '%' . $keyword . '%')
                        ->orWhere('slug', 'like', '%' . $keyword . '%')
                        ->orWhere('source_type', 'like', '%' . $keyword . '%')
                        ->orWhere('content', 'like', '%' . $keyword . '%');
                });
            })
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.chatbot_document.index', compact('documents'));
    }

    public function create()
    {
        $document = new ChatbotDocument();
        return view('admin.chatbot_document.create', compact('document'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        ChatbotDocument::create($this->prepareData($data));

        return redirect()
            ->route('admin.chatbot_document.index')
            ->with('success', 'Đã tạo tài liệu chatbot.');
    }

    public function edit($id)
    {
        $document = ChatbotDocument::findOrFail($id);
        return view('admin.chatbot_document.update', compact('document'));
    }

    public function update(Request $request, $id)
    {
        $document = ChatbotDocument::findOrFail($id);

        $data = $this->validateData($request);

        $document->update($this->prepareData($data));

        return redirect()
            ->route('admin.chatbot_document.index')
            ->with('success', 'Đã cập nhật tài liệu chatbot.');
    }

    public function delete($id)
    {
        $document = ChatbotDocument::findOrFail($id);
        $document->delete();

        return redirect()
            ->route('admin.chatbot_document.index')
            ->with('success', 'Đã xóa tài liệu chatbot.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'source_type' => 'nullable|string|max:100',
            'source_id' => 'nullable|integer',
            'content' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);
    }

    protected function prepareData(array $data): array
    {
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';

        $data['slug'] = !empty($data['slug']) ? $data['slug'] : Str::slug($title);
        $data['short_content'] = Str::limit(strip_tags($content), 500);
        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : false;
        $data['synced_at'] = now();

        return $data;
    }
}