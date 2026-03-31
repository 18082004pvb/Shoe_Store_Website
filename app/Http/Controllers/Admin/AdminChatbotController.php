<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use Illuminate\Http\Request;

class AdminChatbotController extends Controller
{
    public function index(Request $request)
    {
        $conversations = ChatConversation::with(['messages'])
            ->when($request->q, function ($query) use ($request) {
                $keyword = trim($request->q);

                $query->where('title', 'like', '%' . $keyword . '%')
                      ->orWhere('session_id', 'like', '%' . $keyword . '%');
            })
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.chatbot.index', compact('conversations'));
    }

    public function view($id)
    {
        $conversation = ChatConversation::with(['messages'])
            ->findOrFail($id);

        return view('admin.chatbot.view', compact('conversation'));
    }

    public function delete($id)
    {
        $conversation = ChatConversation::findOrFail($id);
        $conversation->delete();

        return redirect()
            ->route('admin.chatbot.index')
            ->with('success', 'Đã xóa hội thoại.');
    }
}