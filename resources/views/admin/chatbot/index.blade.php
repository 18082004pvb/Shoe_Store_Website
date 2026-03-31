@extends('layout.master_admin')

@section('content')
<div class="container-fluid">
    <h1 style="margin-bottom: 20px;">Quản lý hội thoại chatbot</h1>

    <form method="GET" action="{{ route('admin.chatbot.index') }}" style="margin-bottom: 20px;">
        <div style="display: flex; gap: 10px; max-width: 500px;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo user, email, sản phẩm..."
                style="flex: 1; padding: 10px; border: 1px solid #ccc;">
            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        </div>
    </form>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Sản phẩm</th>
                        <th>Số tin nhắn</th>
                        <th>Session</th>
                        <th>Ngày tạo</th>
                        <th width="160">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversations as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>
                                @if($item->user)
                                    <strong>{{ $item->user->name ?? 'N/A' }}</strong><br>
                                    <small>{{ $item->user->email ?? '' }}</small>
                                @else
                                    <span>Khách vãng lai</span>
                                @endif
                            </td>
                            <td>{{ $item->product->pro_name ?? 'Không gắn sản phẩm' }}</td>
                            <td>{{ $item->messages_count }}</td>
                            <td>{{ $item->session_id }}</td>
                            <td>{{ $item->created_at }}</td>
                            <td>
                                <a href="{{ route('admin.chatbot.view', $item->id) }}" class="btn btn-sm btn-info">Xem</a>

                                <form action="{{ route('admin.chatbot.destroy', $item->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Bạn có chắc muốn xóa hội thoại này?')">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Chưa có hội thoại nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div>
                {{ $conversations->appends(request()->all())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection