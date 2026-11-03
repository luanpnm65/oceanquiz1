@php
    $adminTabs = [
        [
            'label' => 'Bộ đề',
            'href' => route('admin.index'),
            'active' => request()->routeIs('admin.index', 'admin.question-sets.edit'),
        ],
        [
            'label' => 'Tìm Wikipedia',
            'href' => route('admin.wikipedia.index'),
            'active' => request()->routeIs('admin.wikipedia.*'),
        ],
        [
            'label' => 'Bài đã lưu',
            'href' => route('admin.articles.index'),
            'active' => request()->routeIs('admin.articles.*'),
        ],
        [
            'label' => 'Gemini & nhật ký',
            'href' => route('admin.ai.index'),
            'active' => request()->routeIs('admin.ai.*'),
        ],
        [
            'label' => 'Tạo bộ thủ công',
            'href' => route('admin.question-sets.create'),
            'active' => request()->routeIs('admin.question-sets.create'),
        ],
    ];
@endphp

<nav class="admin-tabs" aria-label="Điều hướng khu quản trị">
    @foreach ($adminTabs as $tab)
        <a class="admin-tabs__link {{ $tab['active'] ? 'is-active' : '' }}"
           href="{{ $tab['href'] }}"
           @if ($tab['active']) aria-current="page" @endif>{{ $tab['label'] }}</a>
    @endforeach
</nav>
