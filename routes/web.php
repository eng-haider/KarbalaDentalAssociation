<?php

use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Section pages
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/board', [PageController::class, 'board'])->name('board');
Route::get('/regulations', [PageController::class, 'regulations'])->name('regulations');
Route::get('/discounts', [PageController::class, 'discounts'])->name('discounts');
Route::get('/apply', [PageController::class, 'apply'])->name('apply');
Route::get('/search', [PageController::class, 'transactionSearch'])->name('transaction-search');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/complaint', [PageController::class, 'complaint'])->name('complaint');

// News
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');

// Courses
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

// Marketplace (buy & sell)
Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::post('/marketplace', [MarketplaceController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('marketplace.store');

// Job opportunities
Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');

// Events
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// Public form submissions
Route::post('/complaints', [ComplaintController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('complaints.store');

Route::post('/events/{event}/register', [EventRegistrationController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('events.register');

// Transaction search API
Route::get('/api/transactions/search', [TransactionController::class, 'search'])
    ->name('transactions.search');
