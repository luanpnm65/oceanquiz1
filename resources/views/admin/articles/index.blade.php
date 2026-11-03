@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@section('title', 'Bài đã lưu | OceanQuiz')

@section('content')
<div class="container admin-page">
    <div class="page-head">
        <div class="page-head__text">
            <p class="eyebrow">Quản trị · Thư viện</p>
            <h1 class="page-title">Bài đã lưu</h1>
            <p class="page-subtitle">Mỗi bộ đề gắn với một bài đọc. Bài đã xuất bản hoặc đã có lượt làm sẽ bị khóa để giữ lịch sử.</p>
        </div>
        <div class="admin-head-actions">
            <a class="btn btn--primary" href="{{ route('admin.articles.create') }}">Thêm bài thủ công</a>
            <a class="btn btn--ghost" href="{{ route('admin.wikipedia.index') }}">Tìm Wikipedia</a>
        </div>
    </div>

    @include('admin.partials.nav')

    <section class="admin-card admin-table-card" aria-labelledby="articles-title">
        <div class="admin-table-card__head">
            <div>
                <h2 class="card__title" id="articles-title">Thư viện bài kiến thức</h2>
                <p class="card__subtitle">Bài lưu từ Wikipedia hoặc nhập thủ công, mới nhất ở trên.</p>
            </div>
            <p class="text-sm text-faint">{{ $articles->total() }} bài</p>
        </div>

        @if ($articles->isNotEmpty())
            <div class="table-wrap admin-table-wrap">
                <table class="table">
                    <caption class="sr-only">Danh sách bài kiến thức đã lưu</caption>
                    <thead>
                        <tr>
                            <th scope="col">Tiêu đề và nguồn</th>
                            <th scope="col" class="num">Số bộ đề</th>
                            <th scope="col" class="num">Ngày lưu</th>
                            <th scope="col" class="admin-th-actions">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($articles as $article)
                            @php
                                $sourceDisplay = $article->source_url ? urldecode(preg_replace('#^https?://#i', '', $article->source_url)) : null;
                            @endphp
                            <tr>
                                <td>
                                    <span class="admin-cell-title">{{ $article->title }}</span>
                                    @if ($sourceDisplay)
                                        <a class="admin-source" href="{{ $article->source_url }}" target="_blank"
                                           rel="noopener noreferrer" title="{{ $article->source_url }}">{{ mb_strimwidth($sourceDisplay, 0, 46, '…', 'UTF-8') }}</a>
                                    @elseif ($article->source_name)
                                        <span class="admin-source">{{ $article->source_name }}</span>
                                    @else
                                        <span class="admin-source admin-source--empty">Nhập thủ công, không có nguồn</span>
                                    @endif
                                </td>
                                <td class="num">{{ $article->quiz_sets_count }}</td>
                                <td class="num">{{ $article->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y') }}</td>
                                <td>
                                    <div class="admin-cell-actions">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.articles.edit', $article) }}">Xem / sửa</a>
                                        @if ($article->quiz_sets_count === 0)
                                            <a class="btn btn--ghost btn--sm" href="{{ route('admin.ai.index', ['article_id' => $article->id]) }}">Sinh câu hỏi</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($articles->hasPages())
                <div class="admin-pagination">
                    {{ $articles->links('pagination::simple-default') }}
                </div>
            @endif
        @else
            <div class="admin-empty-wrapper">
                <div class="empty-state">
                    <p>Chưa có bài nào trong thư viện.</p>
                    <p class="mt-2">Hãy <a href="{{ route('admin.wikipedia.index') }}">tìm bài Wikipedia</a> hoặc
                        <a href="{{ route('admin.articles.create') }}">nhập bài thủ công</a>.</p>
                </div>
            </div>
        @endif
    </section>
</div>
@endsection
