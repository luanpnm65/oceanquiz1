@extends('layouts.app')

@section('title', 'OceanQuiz — Học về đại dương qua câu hỏi trắc nghiệm')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush

@section('content')
<div class="landing">

    {{-- Thước độ sâu cố định bên phải; chấm trắng chạy theo cuộn bằng CSS scroll-timeline --}}
    <aside class="depth-ruler" aria-hidden="true">
        <div class="depth-ruler__track">
            <span class="depth-ruler__line"></span>
            <span class="depth-ruler__dot"></span>
            <ul class="depth-ruler__marks">
                <li class="depth-ruler__mark"><span class="depth-ruler__tick"></span><span class="depth-ruler__label">0 m</span></li>
                <li class="depth-ruler__mark"><span class="depth-ruler__tick"></span><span class="depth-ruler__label">200 m</span></li>
                <li class="depth-ruler__mark"><span class="depth-ruler__tick"></span><span class="depth-ruler__label">1.000 m</span></li>
                <li class="depth-ruler__mark"><span class="depth-ruler__tick"></span><span class="depth-ruler__label">4.000 m</span></li>
                <li class="depth-ruler__mark"><span class="depth-ruler__tick"></span><span class="depth-ruler__label">6.000 m</span></li>
            </ul>
        </div>
    </aside>

    {{-- Khung 01: hero mặt nước --}}
    <section class="hero" aria-labelledby="hero-title">
        <div class="hero__media">
            <img src="{{ asset('images/hero-whale.jpg') }}"
                 alt="Cá nhà táng mẹ và con bơi gần mặt nước đại dương"
                 width="1920" height="1080" fetchpriority="high" decoding="async">
        </div>
        <div class="hero__shade" aria-hidden="true"></div>
        <div class="hero__bubbles" aria-hidden="true">
            <span class="bubble bubble--1"></span>
            <span class="bubble bubble--2"></span>
            <span class="bubble bubble--3"></span>
            <span class="bubble bubble--4"></span>
            <span class="bubble bubble--5"></span>
        </div>

        <div class="hero__inner container">
            <p class="hero__pill">
                <span class="hero__pill-dot" aria-hidden="true"></span>Bạn đang ở mặt nước · 0 m
            </p>
            <h1 class="hero__title" id="hero-title">Học về đại dương qua câu hỏi trắc nghiệm</h1>
            <p class="hero__subtitle">Đọc tư liệu ngắn, làm bài có tính giờ, rồi xem điểm của bạn trên bảng xếp hạng.</p>

            <div class="hero__actions btn-row">
                @auth
                    <a class="btn btn--primary btn--lg" href="{{ route('dashboard') }}">Vào bảng điều khiển</a>
                    <a class="btn btn--ghost btn--lg" href="{{ route('quiz.play') }}">Làm bài ngay</a>
                @else
                    <a class="btn btn--primary btn--lg" href="{{ route('register') }}">Đăng ký miễn phí</a>
                    <a class="btn btn--ghost btn--lg" href="{{ route('login') }}">Đăng nhập</a>
                @endauth
            </div>
        </div>

        <p class="hero__scroll">Cuộn xuống để xem 5 tầng đại dương<span class="hero__chevron" aria-hidden="true"></span></p>
    </section>

    {{-- Khung 02: cách hoạt động --}}
    <section class="how" aria-labelledby="how-title">
        <div class="container">
            <h2 class="how__title" id="how-title">Cách hoạt động</h2>
            <ol class="how__steps">
                <li class="how__step">
                    <span class="step-number" aria-hidden="true">1</span>
                    <h3 class="how__step-title">Đọc tư liệu</h3>
                    <p class="how__step-text">Mỗi bộ đề có một bài đọc ngắn về một chủ đề đại dương. Thời gian đọc tối đa 3 phút.</p>
                </li>
                <li class="how__step">
                    <span class="step-number" aria-hidden="true">2</span>
                    <h3 class="how__step-title">Làm bài trắc nghiệm</h3>
                    <p class="how__step-text">Mỗi câu hỏi có giới hạn thời gian. Trả lời đúng và nhanh được tối đa 1.000 điểm.</p>
                </li>
                <li class="how__step">
                    <span class="step-number" aria-hidden="true">3</span>
                    <h3 class="how__step-title">Xem kết quả</h3>
                    <p class="how__step-text">Mỗi đáp án có giải thích. Các câu sai được gom lại để bạn ôn tập.</p>
                </li>
            </ol>
        </div>
    </section>

    {{-- Khung 03: tầng 1 --}}
    <section class="tier" aria-labelledby="tang-1">
        <div class="container tier__grid">
            <div class="tier__text">
                <div class="tier__sticky">
                    <p class="tier__depth tier__depth--1">0 – 200 <span class="tier__unit">m</span></p>
                    <h2 class="tier__name" id="tang-1">Tầng ánh sáng</h2>
                    <p class="tier__desc">Ánh sáng mặt trời đủ cho quang hợp. Đây là nơi có phần lớn sinh vật biển, như san hô, rùa biển và cá heo.</p>
                    <p class="tier__note"><strong>Rùa xanh.</strong> Định hướng qua hàng nghìn hải lý nhờ cảm nhận từ trường Trái Đất.</p>
                </div>
            </div>
            <figure class="tier__media">
                <div class="tier__frame">
                    <img src="{{ asset('images/turtle.jpg') }}"
                         alt="Rùa xanh bơi trên rạn san hô ở tầng ánh sáng"
                         width="1400" height="933" loading="lazy" decoding="async">
                </div>
                <figcaption class="tier__caption">
                    <em>Chelonia mydas</em> — rùa xanh.
                    <span class="tier__credit">Ảnh: Diego Delso, CC BY-SA 4.0.</span>
                </figcaption>
            </figure>
        </div>
    </section>

    {{-- Khung 04: tầng 2 (ảnh bên trái) --}}
    <section class="tier tier--reverse" aria-labelledby="tang-2">
        <div class="container tier__grid">
            <div class="tier__text">
                <div class="tier__sticky">
                    <p class="tier__depth tier__depth--2">200 – 1.000 <span class="tier__unit">m</span></p>
                    <h2 class="tier__name" id="tang-2">Tầng chạng vạng</h2>
                    <p class="tier__desc">Ánh sáng yếu dần và không đủ cho quang hợp. Mỗi đêm, nhiều sinh vật bơi lên gần mặt nước để kiếm ăn.</p>
                    <p class="tier__note"><strong>Cá đèn lồng.</strong> Phát sáng ở bụng để xóa bóng của chính mình trước kẻ săn mồi.</p>
                </div>
            </div>
            <figure class="tier__media">
                <div class="tier__frame">
                    <img src="{{ asset('images/lanternfish.jpg') }}"
                         alt="Cận cảnh phần đầu cá đèn lồng Benthosema glaciale"
                         width="1460" height="772" loading="lazy" decoding="async">
                </div>
                <figcaption class="tier__caption">
                    <em>Benthosema glaciale</em> — cá đèn lồng (ảnh cận cảnh phần đầu).
                    <span class="tier__credit">Ảnh: HulloThere, CC BY 4.0.</span>
                </figcaption>
            </figure>
        </div>
    </section>

    {{-- Khung 05: tầng 3 --}}
    <section class="tier" aria-labelledby="tang-3">
        <div class="container tier__grid">
            <div class="tier__text">
                <div class="tier__sticky">
                    <p class="tier__depth tier__depth--3">1.000 – 4.000 <span class="tier__unit">m</span></p>
                    <h2 class="tier__name" id="tang-3">Tầng nửa đêm</h2>
                    <p class="tier__desc">Không còn ánh sáng mặt trời. Nhiều loài tự phát sáng, nhiệt độ nước chỉ khoảng 4 °C và áp suất rất lớn.</p>
                    <p class="tier__note"><strong>Bạch tuộc Dumbo.</strong> Bơi bằng hai chiếc vây hình tai ở hai bên đỉnh thân.</p>
                </div>
            </div>
            <figure class="tier__media">
                <div class="tier__frame">
                    <img src="{{ asset('images/dumbo.jpg') }}"
                         alt="Bạch tuộc Dumbo bơi bằng hai chiếc vây hình tai"
                         width="1600" height="900" loading="lazy" decoding="async">
                </div>
                <figcaption class="tier__caption">
                    <em>Grimpoteuthis</em> sp. — bạch tuộc Dumbo.
                    <span class="tier__credit">Ảnh: NOAA Okeanos Explorer, phạm vi công cộng.</span>
                </figcaption>
            </figure>
        </div>
    </section>

    {{-- Khung 06: tầng 4 (ảnh bên trái, cắt logo NOAA ở góc ảnh) --}}
    <section class="tier tier--reverse" aria-labelledby="tang-4">
        <div class="container tier__grid">
            <div class="tier__text">
                <div class="tier__sticky">
                    <p class="tier__depth tier__depth--4">4.000 – 6.000 <span class="tier__unit">m</span></p>
                    <h2 class="tier__name" id="tang-4">Tầng đáy thẳm</h2>
                    <p class="tier__desc">Đồng bằng bùn lạnh phủ phần lớn đáy đại dương. Sự sống thưa thớt, tập trung quanh các miệng phun thủy nhiệt.</p>
                    <p class="tier__note"><strong>Cá ba chân.</strong> Đứng yên trên đáy bằng ba tia vây dài, chờ con mồi trôi tới.</p>
                </div>
            </div>
            <figure class="tier__media">
                <div class="tier__frame tier__frame--crop">
                    <img src="{{ asset('images/bathypterois.jpg') }}"
                         alt="Cá ba chân đứng trên đáy bùn ở tầng đáy thẳm"
                         width="960" height="540" loading="lazy" decoding="async">
                </div>
                <figcaption class="tier__caption">
                    <em>Bathypterois</em> sp. — cá ba chân trên đáy biển.
                    <span class="tier__credit">Ảnh: NOAA, North Atlantic Stepping Stones, phạm vi công cộng.</span>
                </figcaption>
            </figure>
        </div>
    </section>

    {{-- Tầng 5: hố thẳm, không dùng ảnh --}}
    <section class="tier tier--hadal" aria-labelledby="tang-5">
        <div class="container">
            <div class="tier__hadal">
                <p class="tier__depth tier__depth--5">6.000 – 10.935 <span class="tier__unit">m</span></p>
                <h2 class="tier__name" id="tang-5">Tầng hố thẳm</h2>
                <p class="tier__desc">Chỉ một số ít sinh vật sống được ở độ sâu này. Áp suất tại đây gấp hơn 1.000 lần mặt biển và nước gần như lạnh giá quanh năm.</p>
                <p class="tier__note"><strong>Cá sên Mariana.</strong> Loài cá sống sâu nhất từng được ghi nhận, ở độ sâu khoảng 8.000 m.</p>
            </div>
        </div>
    </section>

    {{-- Khung 07: kết trang --}}
    <section class="finale" aria-labelledby="finale-title">
        <div class="container finale__inner">
            <p class="finale__depth">10.935<span class="finale__unit">m</span></p>
            <p class="finale__caption">Tầng hố thẳm · từ 6.000 m đến đáy Challenger Deep, Rãnh Mariana</p>

            <h2 class="finale__title" id="finale-title">Sẵn sàng làm bài chưa?</h2>
            <p class="finale__text">
                @auth
                    Bạn đã có tài khoản. Vào bảng điều khiển để chọn bộ đề theo từng tầng và xem điểm của mình trên bảng xếp hạng.
                @else
                    Đăng ký miễn phí để làm bộ đề theo từng tầng và xem điểm của bạn trên bảng xếp hạng.
                @endauth
            </p>

            <div class="finale__actions btn-row">
                @auth
                    <a class="btn btn--primary btn--lg" href="{{ route('dashboard') }}">Vào bảng điều khiển</a>
                    <a class="btn btn--ghost btn--lg" href="{{ route('quiz.play') }}">Làm bài ngay</a>
                @else
                    <a class="btn btn--primary btn--lg" href="{{ route('register') }}">Đăng ký miễn phí</a>
                    <a class="btn btn--ghost btn--lg" href="{{ route('login') }}">Đăng nhập</a>
                @endauth
            </div>

            <p class="finale__note">OceanQuiz · Học về đại dương qua câu hỏi trắc nghiệm</p>
        </div>
    </section>
</div>
@endsection
