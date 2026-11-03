@extends('layouts.app')

@section('title', 'Bảng điều khiển | OceanQuiz')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/learner.css') }}">
@endpush

@section('content')
<div class="container learner-page">
    <h1 class="page-title dashboard-head">Bảng điều khiển</h1>

    <div class="dashboard-grid">
        {{-- Cột trái: hồ sơ, thống kê, lối tắt làm bài và cách tính điểm --}}
        <div class="dash-side">
            <section class="card profile-card" aria-labelledby="profile-name">
                <div class="profile-card__head">
                    <div class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
                    <div>
                        <p class="profile-card__label">Người dùng</p>
                        <h2 class="profile-card__name" id="profile-name">{{ $user->name }}</h2>
                        <p class="profile-card__email">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="profile-tiles">
                    <div class="tile">
                        <p class="tile__label">Tổng điểm</p>
                        <p class="tile__value">{{ number_format($user->total_score, 0, ',', '.') }}</p>
                    </div>
                    <div class="tile">
                        <p class="tile__label">Xếp hạng</p>
                        <p class="tile__value">#{{ $globalRank }}</p>
                    </div>
                </div>

                <div class="profile-actions">
                    <a class="btn btn--primary" href="{{ route('quiz.play') }}">Bắt đầu làm bài</a>
                    <a class="btn btn--ghost" href="{{ route('quiz.mistakes') }}">Ôn tập câu sai ({{ $mistakeCount }} câu)</a>
                </div>
            </section>

            <section class="card points-card" aria-labelledby="points-title">
                <h2 class="points-card__title" id="points-title">Cách tính điểm</h2>
                <p>Mỗi lượt gồm 2 bước: đọc tư liệu (tối đa 3 phút) rồi trả lời các câu hỏi trắc nghiệm.</p>
                <p>Trả lời đúng được từ 500 đến 1.000 điểm, càng nhanh càng nhiều. Trả lời sai hoặc hết giờ được 0 điểm.</p>
            </section>
        </div>

        {{-- Cột phải: lịch sử 5 lượt gần nhất --}}
        <section class="card history-card" aria-labelledby="history-title">
            <div class="history-head">
                <div>
                    <h2 class="history-title" id="history-title">Lịch sử 5 lần làm bài gần nhất</h2>
                    <p class="history-subtitle">Bấm vào từng lượt để xem chi tiết đúng/sai.</p>
                </div>
                <p class="history-total">Tổng số bài đã làm: {{ $totalAttempts }}</p>
            </div>

            @forelse ($recentAttempts as $attempt)
                @php
                    $quizSet = $attempt->quizSet;
                    $zoneRaw = (string) ($quizSet->ocean_zone ?? '');
                    $zoneShort = $zoneRaw !== ''
                        ? \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower(trim(\Illuminate\Support\Str::before($zoneRaw, ' ('))))
                        : 'Đại dương';
                    $zoneLower = \Illuminate\Support\Str::lower($zoneRaw);
                    $zoneLevel = \Illuminate\Support\Str::contains($zoneLower, 'hố thẳm')
                        ? 5
                        : (\Illuminate\Support\Str::contains($zoneLower, 'đáy')
                            ? 4
                            : (\Illuminate\Support\Str::contains($zoneLower, 'nửa đêm')
                                ? 3
                                : (\Illuminate\Support\Str::contains($zoneLower, 'chạng vạng') ? 2 : 1)));
                    $sortedAnswers = $attempt->answers
                        ->sortBy(fn ($answer) => optional($answer->question)->id ?? 0)
                        ->values();
                @endphp

                <article class="attempt">
                    <div class="attempt__head">
                        <div>
                            <h3 class="attempt__title">{{ $quizSet->title ?? 'Bộ đề không còn tồn tại' }}</h3>
                            <p class="attempt__meta">
                                <span class="zone">
                                    <span class="zone__dot zone__dot--{{ $zoneLevel }}" aria-hidden="true"></span>
                                    {{ $zoneShort }}
                                </span>
                                <span aria-hidden="true">·</span>
                                <span>{{ $attempt->created_at->diffForHumans() }}</span>
                            </p>
                        </div>
                        <div class="attempt__side">
                            <p class="attempt__score">+{{ number_format($attempt->score, 0, ',', '.') }} điểm</p>
                            <p class="attempt__correct">Đúng {{ $attempt->correct_count }}/{{ $attempt->total_questions }} câu</p>
                        </div>
                    </div>

                    <details class="attempt__details" {{ $loop->first ? 'open' : '' }}>
                        <summary>Xem chi tiết đúng/sai</summary>
                        <div class="attempt__answers">
                            @foreach ($sortedAnswers->take(4) as $answer)
                                @php
                                    $question = $answer->question;
                                    $options = $question->options ?? [];
                                    $selectedIndex = $answer->selected_option_index;
                                    $selectedText = $selectedIndex !== null && isset($options[$selectedIndex])
                                        ? $options[$selectedIndex]
                                        : 'Hết giờ, không chọn';
                                    $correctText = $question && isset($options[$question->correct_option_index])
                                        ? $options[$question->correct_option_index]
                                        : 'Không có đáp án';
                                @endphp

                                <div class="answer-line">
                                    <p class="answer-line__head">
                                        <span class="answer-line__question">Câu {{ $loop->iteration }}: {{ $question->question_text ?? 'Câu hỏi đã bị xóa' }}</span>
                                        <span class="answer-line__points {{ $answer->is_correct ? 'is-correct' : 'is-wrong' }}">
                                            @if ($answer->is_correct)
                                                +{{ number_format($answer->points_earned, 0, ',', '.') }} điểm
                                            @else
                                                0 điểm
                                            @endif
                                            ({{ number_format($answer->time_taken, 1, ',', '.') }} giây)
                                        </span>
                                    </p>
                                    <p class="answer-line__choice">
                                        Bạn chọn:
                                        <strong class="{{ $answer->is_correct ? 'is-correct' : 'is-wrong' }}">{{ $selectedText }}</strong>
                                        @unless ($answer->is_correct)
                                            <span class="choice-divider" aria-hidden="true">|</span>
                                            Đáp án đúng:
                                            <strong class="is-correct">{{ $correctText }}</strong>
                                        @endunless
                                    </p>
                                </div>
                            @endforeach

                            @if ($sortedAnswers->count() > 4)
                                <p class="answer-more">… và {{ $sortedAnswers->count() - 4 }} câu khác</p>
                            @endif
                        </div>
                    </details>
                </article>
            @empty
                <div class="empty-state empty-state--history">
                    <p class="empty-state__title">Chưa có lượt làm bài nào</p>
                    <p>Bắt đầu lượt làm bài đầu tiên để lưu điểm và theo dõi tiến bộ của bạn tại đây.</p>
                    <a class="btn btn--ghost btn--sm" href="{{ route('quiz.play') }}">Bắt đầu làm bài</a>
                </div>
            @endforelse
        </section>
    </div>
</div>
@endsection
