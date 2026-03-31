<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Product;
use App\Services\AiChatService;
use App\Services\KnowledgeSearchService;
use App\Services\OrderSupportService;
use App\Services\ProductSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    protected $aiChatService;
    protected $productSearchService;
    protected $orderSupportService;
    protected $knowledgeSearchService;

    public function __construct(
        AiChatService $aiChatService,
        ProductSearchService $productSearchService,
        OrderSupportService $orderSupportService,
        KnowledgeSearchService $knowledgeSearchService
    ) {
        $this->aiChatService = $aiChatService;
        $this->productSearchService = $productSearchService;
        $this->orderSupportService = $orderSupportService;
        $this->knowledgeSearchService = $knowledgeSearchService;
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'conversation_id' => 'nullable|integer',
            'product_id' => 'nullable|integer',
            'page_type' => 'nullable|string|max:50',
            'page_slug' => 'nullable|string|max:255',
        ]);

        $conversation = $this->findOrCreateConversation($request);

        if (!$this->canAccessConversation($conversation)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập hội thoại này.',
            ], 403);
        }

        ChatMessage::create([
            'chat_conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $request->message,
        ]);

        $productCurrentPageContext = $this->buildProductContext($request->product_id);
        $productSearchContext = $this->productSearchService->search($request->message);
        $orderContext = $this->orderSupportService->getCurrentUserOrders();
        $knowledgeContext = $this->knowledgeSearchService->search($request->message);
        $history = $this->buildHistoryMessages($conversation);

        $messages = array_merge(
            [
                [
                    'role' => 'system',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => $this->buildSystemPrompt(
                                $productCurrentPageContext,
                                $productSearchContext,
                                $orderContext,
                                $knowledgeContext,
                                $request
                            ),
                        ],
                    ],
                ],
            ],
            $history,
            [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => $request->message,
                        ],
                    ],
                ],
            ]
        );

        try {
            $result = $this->aiChatService->ask($messages);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chatbot đang tạm bận. Chi tiết lỗi: ' . $e->getMessage(),
            ], 500);
        }

        ChatMessage::create([
            'chat_conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $result['text'],
            'openai_response_id' => $result['response_id'],
        ]);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'answer' => $result['text'],
        ]);
    }

    public function getMessages($conversationId)
    {
        $conversation = ChatConversation::with('messages')->findOrFail($conversationId);

        if (!$this->canAccessConversation($conversation)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập hội thoại này.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'messages' => $conversation->messages()->orderBy('id', 'asc')->get(),
        ]);
    }

    protected function findOrCreateConversation(Request $request)
    {
        if ($request->filled('conversation_id')) {
            $conversation = ChatConversation::find($request->conversation_id);

            if ($conversation && $this->canAccessConversation($conversation)) {
                if (Auth::check()) {
                    if ((int) $conversation->user_id === (int) Auth::id()) {
                        return $conversation;
                    }
                } else {
                    if (!$conversation->user_id && $conversation->session_id === session()->getId()) {
                        return $conversation;
                    }
                }
            }
        }

        return ChatConversation::create([
            'user_id' => Auth::check() ? Auth::id() : null,
            'session_id' => session()->getId(),
            'product_id' => $request->product_id,
            'page_type' => $request->page_type,
            'page_slug' => $request->page_slug,
            'title' => 'Chat toàn site',
        ]);
    }

    protected function canAccessConversation($conversation): bool
    {
        if (Auth::check()) {
            if ($conversation->user_id && (int) $conversation->user_id === (int) Auth::id()) {
                return true;
            }

            if (!$conversation->user_id && $conversation->session_id === session()->getId()) {
                return true;
            }

            return false;
        }

        return $conversation->session_id === session()->getId();
    }

    protected function buildHistoryMessages($conversation): array
    {
        $messages = $conversation->messages()
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->reverse();

        $result = [];

        foreach ($messages as $message) {
            if (!in_array($message->role, ['user', 'assistant'])) {
                continue;
            }

            $result[] = [
                'role' => $message->role,
                'content' => [
                    [
                        'type' => 'input_text',
                        'text' => $message->content,
                    ],
                ],
            ];
        }

        return $result;
    }

    protected function buildProductContext($productId): array
    {
        if (!$productId) {
            return [];
        }

        $product = Product::with(['category', 'typeproduct', 'attributes', 'images'])->find($productId);

        if (!$product) {
            return [];
        }

        return [
            'id' => $product->id,
            'name' => $product->pro_name ?? null,
            'slug' => $product->pro_slug ?? null,
            'price' => $product->pro_price ?? null,
            'sale' => $product->pro_sale ?? null,
            'avatar' => $product->pro_avatar ?? null,
            'description' => $product->pro_description ?? '',
            'content' => $product->pro_content ?? '',
            'number' => $product->pro_number ?? null,
            'country' => method_exists($product, 'getCountry') ? $product->getCountry() : null,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->c_name ?? '',
                'slug' => $product->category->c_slug ?? '',
            ] : null,
            'type_product' => $product->typeproduct ? [
                'id' => $product->typeproduct->id,
                'name' => $product->typeproduct->tp_name ?? '',
            ] : null,
            'attributes' => $product->attributes
                ? $product->attributes->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->at_name ?? ($item->name ?? ''),
                    ];
                })->values()->toArray()
                : [],
        ];
    }

    protected function buildSystemPrompt(
        array $productCurrentPageContext,
        array $productSearchContext,
        array $orderContext,
        array $knowledgeContext,
        Request $request
    ): string {
        $prompt = "Bạn là chatbot AI hỗ trợ khách hàng cho website thương mại điện tử.\n";
        $prompt .= "Hãy trả lời bằng tiếng Việt.\n";
        $prompt .= "Ưu tiên câu trả lời ngắn gọn, rõ ràng, hữu ích.\n";
        $prompt .= "Không được tự bịa thông tin ngoài dữ liệu được cung cấp.\n";
        $prompt .= "Nếu không đủ dữ liệu thì phải nói rõ là chưa đủ dữ liệu xác nhận.\n";
        $prompt .= "Nếu người dùng hỏi về đơn hàng mà chưa đăng nhập thì yêu cầu họ đăng nhập.\n";
        $prompt .= "Nếu có nhiều nguồn dữ liệu, ưu tiên dữ liệu cụ thể và gần với câu hỏi nhất.\n";
        $prompt .= "Nếu đang ở trang sản phẩm thì ưu tiên CURRENT_PRODUCT_CONTEXT.\n";
        $prompt .= "Nếu câu hỏi là tìm sản phẩm toàn website thì ưu tiên PRODUCT_SEARCH_CONTEXT.\n";
        $prompt .= "Nếu câu hỏi là chính sách, bài viết, danh mục hoặc nội dung website thì ưu tiên KNOWLEDGE_CONTEXT.\n";
        $prompt .= "Nếu câu hỏi là đơn hàng thì chỉ dùng ORDER_CONTEXT.\n\n";

        $prompt .= "PAGE_CONTEXT:\n";
        $prompt .= json_encode([
            'page_type' => $request->page_type,
            'page_slug' => $request->page_slug,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $prompt .= "\n\nCURRENT_PRODUCT_CONTEXT:\n";
        $prompt .= json_encode($productCurrentPageContext, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $prompt .= "\n\nPRODUCT_SEARCH_CONTEXT:\n";
        $prompt .= json_encode($productSearchContext, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $prompt .= "\n\nORDER_CONTEXT:\n";
        $prompt .= json_encode($orderContext, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $prompt .= "\n\nKNOWLEDGE_CONTEXT:\n";
        $prompt .= json_encode($knowledgeContext, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return $prompt;
    }
}