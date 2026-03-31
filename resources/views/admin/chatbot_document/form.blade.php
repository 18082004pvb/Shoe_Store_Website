<div class="form-group">
    <label>Tiêu đề</label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $document->title ?? '') }}">
    @error('title')
        <small style="color:red">{{ $message }}</small>
    @enderror
</div>

<div class="form-group">
    <label>Slug</label>
    <input type="text" name="slug" class="form-control" value="{{ old('slug', $document->slug ?? '') }}">
    @error('slug')
        <small style="color:red">{{ $message }}</small>
    @enderror
</div>

<div class="form-group">
    <label>URL</label>
    <input type="text" name="url" class="form-control" value="{{ old('url', $document->url ?? '') }}">
    @error('url')
        <small style="color:red">{{ $message }}</small>
    @enderror
</div>

<div class="form-group">
    <label>Loại nguồn</label>
    <input type="text" name="source_type" class="form-control" value="{{ old('source_type', $document->source_type ?? 'policy') }}">
    @error('source_type')
        <small style="color:red">{{ $message }}</small>
    @enderror
</div>

<div class="form-group">
    <label>Source ID</label>
    <input type="number" name="source_id" class="form-control" value="{{ old('source_id', $document->source_id ?? '') }}">
    @error('source_id')
        <small style="color:red">{{ $message }}</small>
    @enderror
</div>

<div class="form-group">
    <label>Nội dung</label>
    <textarea name="content" rows="14" class="form-control">{{ old('content', $document->content ?? '') }}</textarea>
    @error('content')
        <small style="color:red">{{ $message }}</small>
    @enderror
</div>

<div class="form-group">
    <label style="display:block;">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $document->is_active ?? true) ? 'checked' : '' }}>
        Kích hoạt
    </label>
</div>