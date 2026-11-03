<?php

// [Thành viên 1 - Owner] routes/web.php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\QuizController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

// --- KHU VỰC CỦA THÀNH VIÊN 1 (OWNER): LANDING PAGE, LEADERBOARD & AUTH ---
Route::get('/', [HomeController::class, 'welcome'])->name('welcome');
Route::get('/leaderboard', [HomeController::class, 'leaderboard'])->name('leaderboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// --- KHU VỰC CỦA THÀNH VIÊN 1 (OWNER): USER BENTO DASHBOARD ---
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// --- KHU VỰC CỦA THÀNH VIÊN 2 (CONTRIBUTOR A): QUIZ WORKFLOW THUẦN PHP (KHÔNG JS) ---
Route::middleware('auth')->prefix('quiz')->name('quiz.')->group(function () {
    // Bước 0: bốc ngẫu nhiên một bộ đề đã xuất bản và mở trang đọc tư liệu.
    Route::get('/play', [QuizController::class, 'play'])->name('play');

    // Bước 1: đọc tư liệu. Nút "Bắt đầu làm bài" và thẻ meta refresh gọi route start.
    Route::get('/{quizSet}/start', [QuizController::class, 'start'])->name('start');

    // Bước 2: từng câu hỏi, mỗi câu một trang do server tính giờ.
    Route::get('/attempt/{attempt}', [QuizController::class, 'question'])->name('question');
    Route::post('/attempt/{attempt}/answer', [QuizController::class, 'answer'])->name('answer');

    // Kết quả từng câu (đúng/sai/giải thích) và kết quả cả bài.
    Route::get('/attempt/{attempt}/answer/{answer}', [QuizController::class, 'answerResult'])->name('answer-result');
    Route::get('/attempt/{attempt}/result', [QuizController::class, 'result'])->name('result');

    // Luồng JSON cũ giữ nguyên để không phá phần backend đã nộp trước đó.
    Route::post('/submit', [QuizController::class, 'submit'])->name('submit');
    Route::get('/mistakes', [QuizController::class, 'mistakes'])->name('mistakes');
});

// --- KHU VỰC CỦA THÀNH VIÊN 3 (CONTRIBUTOR B): ADMIN DASHBOARD, WIKIPEDIA API & GEMINI AI ---
Route::middleware(['auth', AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
});

require __DIR__.'/admin-articles.php';
require __DIR__.'/admin-question-sets.php';
