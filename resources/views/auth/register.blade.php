@extends('layouts.app')

@section('title', 'Đăng ký | OceanQuiz')
@section('body_class', 'auth-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/learner.css') }}">
@endpush

@section('content')
<div class="auth-shell">
    <div class="auth-card">
        <h1 class="auth-card__title">Đăng ký</h1>
        <p class="auth-card__subtitle">Tạo tài khoản để lưu điểm và xuất hiện trên bảng xếp hạng.</p>

        @if ($errors->any())
            <div class="alert alert--error" role="alert">
                <ul>
                    @if ($errors->has('name'))
                        <li>Họ và tên không được để trống.</li>
                    @endif
                    @if ($errors->has('email'))
                        <li>Email không hợp lệ hoặc đã được sử dụng.</li>
                    @endif
                    @if ($errors->has('password'))
                        <li>Mật khẩu phải có ít nhất 6 ký tự và khớp với phần nhập lại.</li>
                    @endif
                </ul>
            </div>
        @endif

        <form class="auth-form" action="{{ route('register.submit') }}" method="POST">
            @csrf

            <div class="field">
                <label class="field__label" for="register-name">Họ và tên</label>
                <input
                    class="form-control"
                    type="text"
                    id="register-name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Nguyễn Hải Đăng"
                    autocomplete="name"
                    required
                >
            </div>

            <div class="field">
                <label class="field__label" for="register-email">Email</label>
                <input
                    class="form-control"
                    type="email"
                    id="register-email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="ten@email.com"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="field">
                <label class="field__label" for="register-password">
                    Mật khẩu <span class="field__note">tối thiểu 6 ký tự</span>
                </label>
                <input
                    class="form-control"
                    type="password"
                    id="register-password"
                    name="password"
                    placeholder="Nhập mật khẩu"
                    autocomplete="new-password"
                    minlength="6"
                    required
                >
            </div>

            <div class="field">
                <label class="field__label" for="register-password-confirmation">Nhập lại mật khẩu</label>
                <input
                    class="form-control"
                    type="password"
                    id="register-password-confirmation"
                    name="password_confirmation"
                    placeholder="Nhập lại mật khẩu"
                    autocomplete="new-password"
                    minlength="6"
                    required
                >
            </div>

            <button class="btn btn--primary" type="submit">Tạo tài khoản</button>
        </form>
    </div>

    <p class="auth-switch">
        Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a>
    </p>
</div>
@endsection
