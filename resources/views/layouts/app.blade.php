<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="OceanQuiz — đọc tư liệu ngắn về đại dương, làm bài trắc nghiệm có tính giờ và theo dõi điểm trên bảng xếp hạng.">
    <title>@yield('title', 'OceanQuiz — Học về đại dương qua câu hỏi trắc nghiệm')</title>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/be-vietnam-pro-547cf24c.woff2') }}" crossorigin>
    <link rel="stylesheet" href="{{ asset('fonts/be-vietnam-pro.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    @stack('styles')
    @stack('head')
</head>
<body class="@yield('body_class')">
    <a class="skip-link" href="#main">Bỏ qua điều hướng</a>

    <header class="site-header">
        <nav class="nav container" aria-label="Điều hướng chính">
            <a class="brand" href="{{ route('welcome') }}">Ocean<span class="brand__quiz">Quiz</span></a>

            <div class="nav__links">
                <a class="nav__link {{ request()->routeIs('welcome') ? 'is-active' : '' }}" href="{{ route('welcome') }}">Trang chủ</a>
                <a class="nav__link {{ request()->routeIs('leaderboard') ? 'is-active' : '' }}" href="{{ route('leaderboard') }}">Bảng xếp hạng</a>

                @auth
                    <a class="nav__link {{ request()->routeIs('dashboard', 'quiz.*') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">Bảng điều khiển</a>
                    @if (auth()->user()->isAdmin())
                        <a class="nav__link {{ request()->routeIs('admin.*') ? 'is-active' : '' }}" href="{{ route('admin.index') }}">Quản trị</a>
                    @endif
                @endauth

                <span class="nav__divider" aria-hidden="true"></span>

                <div class="nav__user-actions">
                    @auth
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn--ghost btn--sm">Đăng xuất</button>
                        </form>
                    @else
                        <a class="nav__link {{ request()->routeIs('login') ? 'is-active' : '' }}" href="{{ route('login') }}">Đăng nhập</a>
                        <a class="btn btn--primary btn--sm" href="{{ route('register') }}">Đăng ký</a>
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <main id="main">
        @if (session('success') || session('error') || $errors->any())
            <div class="container page-flash" aria-live="polite">
                @if (session('success'))
                    <div class="alert alert--success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert--error">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert--error">
                        <strong>Vui lòng kiểm tra lại:</strong>
                        <ul class="mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    @hasSection('footer')
        <footer class="site-footer">
            <div class="container site-footer__inner">
                @yield('footer')
            </div>
        </footer>
    @endif
</body>
</html>
