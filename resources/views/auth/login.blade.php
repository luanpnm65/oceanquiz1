@extends('layouts.app')

@section('title', 'Đăng nhập | OceanQuiz')
@section('body_class', 'auth-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/learner.css') }}">
@endpush

@section('content')
<div class="auth-shell">
    <div class="auth-card">
        <h1 class="auth-card__title">Đăng nhập</h1>
        <p class="auth-card__subtitle">Đăng nhập để làm bài và lưu điểm của bạn.</p>

        @if ($errors->any())
            <div class="alert alert--error" role="alert">
                Email hoặc mật khẩu không đúng.
            </div>
        @endif

        <form class="auth-form" action="{{ route('login.submit') }}" method="POST">
            @csrf

            <div class="field">
                <label class="field__label" for="login-email">Email</label>
                <input
                    class="form-control"
                    type="email"
                    id="login-email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="ten@email.com"
                    autocomplete="email"
                    required
                    autofocus
                >
            </div>

            <div class="field">
                <label class="field__label" for="login-password">Mật khẩu</label>
                <input
                    class="form-control"
                    type="password"
                    id="login-password"
                    name="password"
                    placeholder="Nhập mật khẩu"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button class="btn btn--primary" type="submit">Đăng nhập</button>
        </form>
    </div>

    <p class="auth-switch">
        Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a>
    </p>
</div>
@endsection
