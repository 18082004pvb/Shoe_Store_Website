@extends('layout.master_admin')

@section('content')
<div class="container-fluid">
    <h1 style="margin-bottom:20px;">Quản lý tài liệu chatbot</h1>

    <div style="margin-bottom:20px; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <form method="GET" action="{{ route('admin.chatbot_document.index') }}" style="display:flex; gap:10px; max-width:500px; width:100%;">
            <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Tìm tiêu đề, slug, source type...">
            <button class="btn btn-primary">Tìm kiếm</button>
        </form>

        <a href="{{ route('admin.chatbot_document.create') }}" class="btn btn-success">Thêm tài liệu</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tiêu đề</th>
                        <th>Slug</th>
                        <th>Loại nguồn</th>
                        <th>Active</th>
                        <th>Cập nhật</th>
                        <th width="180">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->title }}</td>
                            <td>{{ $item->slug }}</td>
                            <td>{{ $item->source_type }}</td>
                            <td>{{ $item->is_active ? 'Có' : 'Không' }}</td>
                            <td>{{ $item->updated_at }}</td>
                            <td>
                                <a href="{{ route('admin.chatbot_document.edit', $item->id) }}" class="btn btn-sm btn-info">Sửa</a>

                                <form action="{{ route('admin.chatbot_document.delete', $item->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Bạn có chắc muốn xóa tài liệu này?')">
                                    @csrf
                                    <button class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Chưa có tài liệu nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $documents->appends(request()->all())->links() }}
        </div>
    </div>
</div>
@endsection