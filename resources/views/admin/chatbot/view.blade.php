@extends('layout.master_admin')

@section('content')
<div class="container-fluid">
    <h1 style="margin-bottom: 20px;">Chi tiết hội thoại #{{ $conversation->id }}</h1>

    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body">
            <p><strong>User:</strong> {{ $conversation->user->name ?? 'Khách vãng lai' }}</p>
            <p><strong>Email:</strong> {{ $conversation->user->email ?? 'N/A' }}</p>
            <p><strong>Sản phẩm:</strong> {{ $conversation->product->pro_name ?? 'Không gắn sản phẩm' }}</p>
            <p><strong>Session ID:</strong> {{ $conversation->session_id }}</p>
            <p><strong>Page type:</strong> {{ $conversation->page_type }}</p>
            <p><strong>Page slug:</strong> {{ $conversation->page_slug }}</p>
            <p><strong>Ngày tạo:</strong> {{ $conversation->created_at }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="background: #f8fafc;">
            @forelse($conversation->messages as $message)
                <div style="margin-bottom: 15px; text-align: {{ $message->role == 'user' ? 'right' : 'left' }};">
                    <div style="
                        display: inline-block;
                        max-width: 75%;
                        padding: 12px;
                        border-radius: 12px;
                        background: {{ $message->role == 'user' ? '#2563eb' : '#ffffff' }};
                        color: {{ $message->role == 'user' ? '#ffffff' : '#111827' }};
                        border: 1px solid #ddd;
                    ">
                        <div style="font-weight: bold; margin-bottom: 6px;">
                            {{ $message->role == 'user' ? 'Khách' : 'Bot' }}
                        </div>
                        <div>{!! nl2br(e($message->content)) !!}</div>
                        <div style="font-size: 11px; margin-top: 8px; opacity: .8;">
                            {{ $message->created_at }}
                        </div>
                    </div>
                </div>
            @empty
                <p>Chưa có tin nhắn.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection