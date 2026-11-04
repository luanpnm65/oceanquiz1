@extends('admin.layout')

@section('title', 'Tìm bài Wikipedia | AbyssalMind')

@section('admin-content')
    <header class="admin-page-head">
        <div>
            <span class="admin-eyebrow">Bước 1 · Nguồn kiến thức</span>
            <h1 class="admin-page-title">Tìm kiến thức từ Wikipedia</h1>
            <p class="admin-page-desc">
                Nhập từ khóa tiếng Việt để xem tối đa 10 bài liên quan, xem trước nội dung rồi lưu vào thư viện bài đã lưu.
            </p>
        </div>
        <div class="admin-head-actions">
            <a class="btn btn-outline" href="{{ route('admin.articles.index') }}">📚 Bài đã lưu</a>
            <a class="btn btn-outline" href="{{ route('admin.articles.create') }}">✍️ Nhập bài thủ công</a>
        </div>
    </header>

    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2 class="admin-card-title">Từ khóa tìm kiếm</h2>
                <p class="admin-card-sub">Ví dụ: Cá voi xanh, Rãnh Mariana, San hô, Bạch tuộc Dumbo…</p>
            </div>
        </div>

        <form class="admin-search-form" method="get" role="search">
            <div class="form-group">
                <label class="form-label" for="search">Từ khóa tiếng Việt <span class="req" aria-hidden="true">*</span></label>
                <input class="form-control" id="search" name="search" value="{{ request('search') }}" required maxlength="100" placeholder="Nhập chủ đề đại dương bạn muốn tìm">
                <p class="form-hint">Kết quả lấy trực tiếp từ Wikipedia tiếng Việt, có ghi nguồn và giấy phép khi lưu.</p>
            </div>
            <button class="btn btn-primary" type="submit">Tìm bài liên quan</button>
        </form>
    </section>

    @if ($apiError)
        <div class="admin-alert admin-alert-error" role="alert">
            <span class="admin-alert-icon" aria-hidden="true">⚠️</span>
            <div>
                <strong>Không lấy được dữ liệu Wikipedia</strong>
                {{ $apiError }}
                <a href="{{ route('admin.articles.create') }}">Nhập bài thủ công</a>
            </div>
        </div>
    @endif

    @if ($preview)
        <section class="admin-card">
            <div class="admin-card-head">
                <div>
                    <span class="admin-eyebrow">Xem trước</span>
                    <h2 class="admin-card-title">{{ $preview['title'] }}</h2>
                </div>
                <span class="admin-badge is-info">Wikipedia tiếng Việt</span>
            </div>

            <div class="admin-passage">{{ $preview['content'] }}</div>

            <div class="admin-meta-row">
                <span>Nguồn:
                    <a class="admin-source-link" href="{{ $preview['source_url'] }}" target="_blank" rel="noopener noreferrer">{{ $preview['source_name'] }}</a>
                </span>
                <span class="admin-chip">{{ $preview['license'] }}</span>
            </div>

            <form method="post" action="{{ route('admin.wikipedia.store') }}" class="admin-actions-row">
                @csrf
                <input type="hidden" name="page_id" value="{{ $preview['wiki_page_id'] }}">
                <button class="btn btn-primary">Lưu bài vào thư viện</button>
                <span class="form-hint">Bài đã lưu trước đó sẽ được giữ nguyên, không ghi đè nội dung đã biên tập.</span>
            </form>
        </section>
    @endif

    @if ($results)
        <section class="admin-section">
            <h2 class="admin-section-title">Kết quả tìm kiếm <span class="admin-count">{{ count($results) }}</span></h2>

            <div class="admin-result-grid">
                @foreach ($results as $result)
                    <article class="admin-card admin-result-card">
                        <h3>{{ $result['title'] }}</h3>
                        <p>{{ $result['snippet'] ?: 'Bài này chưa có đoạn mô tả ngắn.' }}</p>
                        <a class="btn btn-outline btn-sm" href="{{ route('admin.wikipedia.index', ['search' => request('search'), 'page_id' => $result['id']]) }}">Xem trước bài này</a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if (request('search') && ! $results && ! $apiError)
        <div class="admin-card">
            <div class="admin-empty">
                <span class="admin-empty-icon" aria-hidden="true">🔎</span>
                <div>Không tìm thấy bài phù hợp với “{{ request('search') }}”.</div>
                <p class="form-hint">Hãy thử từ khóa khác, hoặc nhập bài thủ công nếu bạn đã có nội dung.</p>
                <a class="btn btn-outline btn-sm" href="{{ route('admin.articles.create') }}">Nhập bài thủ công</a>
            </div>
        </div>
    @endif
@endsection
