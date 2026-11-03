@extends('layouts.app')

@section('title', 'Bảng xếp hạng | OceanQuiz')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/learner.css') }}">
@endpush

@section('content')
<div class="container learner-page leaderboard-page">
    <div class="page-head">
        <div class="page-head__text">
            <h1 class="page-title">Bảng xếp hạng</h1>
            <p class="page-subtitle">Xếp theo tổng điểm cao nhất, sau đó đến số bài đã hoàn thành.</p>
        </div>
        <a class="btn btn--primary btn--lg" href="{{ route('quiz.play') }}">Làm bài ngay</a>
    </div>

    @if ($leaders->isEmpty())
        <div class="empty-state">
            <p class="empty-state__title">Bảng xếp hạng đang trống</p>
            <p>Chưa có người chơi nào hoàn thành lượt làm bài. Hãy là người đầu tiên xuất hiện tại đây.</p>
            <a class="btn btn--primary btn--sm" href="{{ route('quiz.play') }}">Làm bài ngay</a>
        </div>
    @else
        <div class="table-wrap">
            <table class="table lb-table">
                <thead>
                    <tr>
                        <th scope="col">Hạng</th>
                        <th scope="col">Tên người dùng</th>
                        <th scope="col">Số bài đã hoàn thành</th>
                        <th scope="col" class="num">Tổng điểm</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leaders as $leader)
                        @php($isSelf = auth()->id() === $leader->id)
                        <tr class="{{ $isSelf ? 'is-self' : '' }}">
                            <td class="lb-rank">{{ $loop->iteration }}</td>
                            <td>
                                <span class="lb-name">
                                    {{ $leader->name }}
                                    @if ($isSelf)
                                        <span class="badge badge--teal">Bạn</span>
                                    @endif
                                </span>
                            </td>
                            <td>{{ $leader->quizzes_completed }} lượt</td>
                            <td class="num">{{ number_format($leader->total_score, 0, ',', '.') }} điểm</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
