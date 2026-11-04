@extends('layouts.app')

@push('styles')
    {{-- Bản build Vite đã khôi phục trong public/build (manifest.json + assets). --}}
    @if (is_file(public_path('build/manifest.json')))
        @vite('resources/css/app.css')
    @endif

    @php($adminCssPath = public_path('css/admin-app.css'))
    <link rel="stylesheet" href="{{ asset('css/admin-app.css') }}{{ is_file($adminCssPath) ? '?v=' . filemtime($adminCssPath) : '' }}">
@endpush

@push('scripts')
    @if (is_file(public_path('build/manifest.json')))
        @vite('resources/js/app.js')
    @endif
@endpush

@section('content')
<div class="container admin-shell">
    @include('admin.partials.sidebar')

    <div class="admin-main">
        @if ($errors->any())
            <div class="admin-alert admin-alert-error" role="alert">
                <span class="admin-alert-icon" aria-hidden="true">⚠️</span>
                <div>
                    <strong>Vui lòng kiểm tra lại {{ $errors->count() }} mục bên dưới</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('admin-content')
    </div>
</div>
@endsection
