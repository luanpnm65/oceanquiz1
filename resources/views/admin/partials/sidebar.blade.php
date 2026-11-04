@php
    $adminNav = [
        ['route' => 'admin.index', 'patterns' => ['admin.index', 'admin.question-sets.edit'], 'icon' => '🗂️', 'label' => 'Bộ câu hỏi', 'hint' => 'Duyệt & xuất bản'],
        ['route' => 'admin.wikipedia.index', 'patterns' => ['admin.wikipedia.*'], 'icon' => '🔎', 'label' => 'Tìm Wikipedia', 'hint' => 'Lấy bài nguồn'],
        ['route' => 'admin.articles.index', 'patterns' => ['admin.articles.*'], 'icon' => '📚', 'label' => 'Bài đã lưu', 'hint' => 'Thư viện kiến thức'],
        ['route' => 'admin.ai.index', 'patterns' => ['admin.ai.*'], 'icon' => '⚡', 'label' => 'Gemini & nhật ký', 'hint' => 'Sinh câu hỏi nháp'],
        ['route' => 'admin.question-sets.create', 'patterns' => ['admin.question-sets.create'], 'icon' => '➕', 'label' => 'Tạo bộ thủ công', 'hint' => 'Không qua AI'],
    ];
@endphp

<aside class="admin-sidebar">
    <div class="admin-sidebar-head">
        <span class="admin-sidebar-kicker">AbyssalMind</span>
        <strong class="admin-sidebar-title">Trung Tâm AI</strong>
        <p class="admin-sidebar-sub">Biên soạn · Kiểm duyệt · Xuất bản</p>
    </div>

    <nav class="admin-nav" aria-label="Điều hướng quản trị">
        @foreach ($adminNav as $item)
            @php($isActive = request()->routeIs($item['patterns']))
            <a href="{{ route($item['route']) }}"
               class="admin-nav-link {{ $isActive ? 'active' : '' }}"
               @if ($isActive) aria-current="page" @endif>
                <span class="admin-nav-icon" aria-hidden="true">{{ $item['icon'] }}</span>
                <span class="admin-nav-text">
                    <strong>{{ $item['label'] }}</strong>
                    <small>{{ $item['hint'] }}</small>
                </span>
            </a>
        @endforeach
    </nav>

    <div class="admin-sidebar-foot">
        <div class="admin-user">
            <span class="admin-avatar" aria-hidden="true">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <div style="min-width: 0;">
                <div class="admin-user-name">{{ auth()->user()->name }}</div>
                <div class="admin-user-role">{{ auth()->user()->email }}</div>
            </div>
        </div>
        <a class="admin-back-link" href="{{ route('welcome') }}">← Về trang chủ</a>
    </div>
</aside>
