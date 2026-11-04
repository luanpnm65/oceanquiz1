<?php

namespace App\Http\Controllers;

use App\Models\QuizSet;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index', ['quizSets' => QuizSet::withCount('questions', 'attempts')->latest()->get()]);
    }
}