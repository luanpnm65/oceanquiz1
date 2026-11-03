@extends('layouts.app')
@section('title','Quản lý bộ câu hỏi')
@section('content')
<div class="container"><h1>Trung tâm biên soạn Wikipedia & Gemini</h1><p>Tìm bài → lưu bài → tạo 5 câu nháp → kiểm duyệt → xuất bản.</p>
@include('admin.partials.nav')
<div class="bento-card"><h2>Bộ câu hỏi</h2><div style="overflow-x:auto"><table class="data-table"><thead><tr><th>Bộ đề</th><th>Trạng thái</th><th>Số câu</th><th>Lượt làm</th><th>Thao tác</th></tr></thead><tbody>
@forelse($quizSets as $set)<tr><td>{{ $set->title }}</td><td>{{ $set->is_active ? 'Đã xuất bản' : 'Bản nháp' }}</td><td>{{ $set->questions_count }}</td><td>{{ $set->attempts_count }}</td><td><a class="btn btn-outline" href="{{ route('admin.question-sets.edit',$set) }}">Xem / biên tập</a></td></tr>@empty<tr><td colspan="5">Chưa có bộ câu hỏi.</td></tr>@endforelse
</tbody></table></div></div></div>@endsection