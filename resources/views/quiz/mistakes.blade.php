<!-- [Thành viên 2 - Contributor A] resources/views/quiz/mistakes.blade.php -->
@extends('layouts.app')

@section('title', 'Targeted Mistake Review | AbyssalMind')

@section('content')
<div class="container" style="max-width: 960px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;">
        <div>
            <span class="zone-depth-pill">ADAPTIVE LEARNING DECK</span>
            <h1 style="font-size: 1.85rem; margin-top: 0.4rem;">🎯 Review Mistakes (Last 5 Attempts)</h1>
            <p style="color: var(--text-muted); font-size: 0.92rem;">
                Questions you missed in your last 5 expeditions are automatically aggregated here for targeted mastery.
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline">← Back to Bento Dashboard</a>
    </div>

    @forelse($wrongAnswers as $idx => $wrongItem)
        @php
            $q = $wrongItem->question;
            $options = $q ? $q->options : [];
        @endphp
        @if($q)
            <div class="bento-card" style="margin-bottom: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="font-size: 0.8rem; color: var(--cyan-glow); font-weight: 700;">
                        📚 Topic: {{ $q->quizSet->title ?? 'Ocean Set' }} ({{ $q->quizSet->ocean_zone ?? '' }})
                    </span>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">
                        Missed {{ $wrongItem->created_at->diffForHumans() }}
                    </span>
                </div>

                <h3 style="font-size: 1.2rem; margin-bottom: 1rem;">{{ $idx + 1 }}. {{ $q->question_text }}</h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    @foreach($options as $optIdx => $optText)
                        @php
                            $isCorrectOpt = ($optIdx === (int)$q->correct_option_index);
                            $wasSelectedWrong = ($wrongItem->selected_option_index !== null && $optIdx === (int)$wrongItem->selected_option_index);
                        @endphp
                        <div style="padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid {{ $isCorrectOpt ? '#22c55e' : ($wasSelectedWrong ? '#ef4444' : 'rgba(255,255,255,0.1)') }}; background: {{ $isCorrectOpt ? 'rgba(34, 197, 94, 0.14)' : ($wasSelectedWrong ? 'rgba(239, 68, 68, 0.14)' : 'rgba(255,255,255,0.03)') }};">
                            <strong>{{ chr(65 + $optIdx) }}.</strong> {{ $optText }}
                            @if($isCorrectOpt)
                                <span style="float: right; color: #4ade80; font-weight: 700;">✓ Correct</span>
                            @elseif($wasSelectedWrong)
                                <span style="float: right; color: #f87171; font-weight: 700;">✗ Your Choice</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($q->explanation)
                    <div style="background: rgba(0, 242, 254, 0.08); border-left: 3px solid var(--cyan-glow); padding: 0.85rem 1rem; border-radius: 8px; font-size: 0.9rem;">
                        <strong>💡 Scientific Explanation:</strong> {{ $q->explanation }}
                    </div>
                @endif
            </div>
        @endif
    @empty
        <div class="bento-card" style="text-align: center; padding: 3.5rem 1.5rem;">
            <div style="font-size: 3.5rem; margin-bottom: 0.75rem;">🌟</div>
            <h2>Flawless Sonar Record!</h2>
            <p style="color: var(--text-muted); margin: 0.5rem 0 1.5rem;">
                You have zero unanswered or incorrect questions across your last 5 quiz attempts.
            </p>
            <a href="{{ route('quiz.play') }}" class="btn btn-primary">🚀 Take Another Ocean Quiz</a>
        </div>
    @endforelse
</div>
@endsection