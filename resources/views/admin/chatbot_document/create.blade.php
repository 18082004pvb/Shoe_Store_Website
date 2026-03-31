@extends('layout.master_admin')

@section('content')
<div class="container-fluid">
    <h1 style="margin-bottom:20px;">Thêm tài liệu chatbot</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.chatbot_document.store') }}" method="POST">
                @csrf
                @include('admin.chatbot_document.form')
                <button class="btn btn-primary">Lưu dữ liệu</button>
                <a href="{{ route('admin.chatbot_document.index') }}" class="btn btn-secondary">Quay lại</a>
            </form>
        </div>
    </div>
</div>
@endsection