<?php

use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OsifCompanyController;
use App\Http\Controllers\OsifController;
use App\Http\Controllers\OsifInvitationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\StorageFileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Uploaded images, for when the host's public/storage link is missing or stale.
Route::get('/storage/{path}', StorageFileController::class)
    ->where('path', '.*')
    ->name('storage.public');

// Section pages
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/board', [PageController::class, 'board'])->name('board');
Route::get('/regulations', [PageController::class, 'regulations'])->name('regulations');
Route::get('/discounts', [PageController::class, 'discounts'])->name('discounts');
Route::get('/apply', [PageController::class, 'apply'])->name('apply');
Route::get('/app', [PageController::class, 'app'])->name('app');
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

// OSIF is intentionally a stand-alone event experience while sharing the
// association's registration and administration infrastructure.
Route::get('/osif', OsifController::class)->name('osif');
Route::get('/osif/register', [OsifController::class, 'registration'])->name('osif.register');

// The exhibiting companies: a page each with its products, offers and a WhatsApp cart.
Route::get('/osif/companies', [OsifCompanyController::class, 'index'])->name('osif.companies');
Route::get('/osif/companies/{company}', [OsifCompanyController::class, 'show'])->name('osif.companies.show');
Route::get('/osif/companies/{company}/qr.png', [OsifCompanyController::class, 'qr'])->name('osif.companies.qr');
Route::get('/osif/companies/{company}/poster', [OsifCompanyController::class, 'poster'])->name('osif.companies.poster');
Route::get('/osif/media/{path}', [OsifCompanyController::class, 'media'])
    ->where('path', 'companies/[A-Za-z0-9._/-]+')
    ->name('osif.media');

// The page an invitation's QR code opens, and the dashboard upload of its card files.
Route::get('/osif/invitation/{token}', [OsifInvitationController::class, 'show'])
    ->where('token', '[A-Za-z0-9]{20,64}')
    ->name('osif.invitation');
Route::get('/osif/invitation/{token}/card', [OsifInvitationController::class, 'card'])
    ->where('token', '[A-Za-z0-9]{20,64}')
    ->name('osif.invitation.card');
Route::get('/osif/invitation/{token}/card/image', [OsifInvitationController::class, 'cardImage'])
    ->where('token', '[A-Za-z0-9]{20,64}')
    ->name('osif.invitation.card.image');
Route::get('/osif/invitation/{token}/preview', [OsifInvitationController::class, 'preview'])
    ->where('token', '[A-Za-z0-9]{20,64}')
    ->name('osif.invitation.preview');
Route::get('/osif/invitation/{token}/pdf', [OsifInvitationController::class, 'pdf'])
    ->where('token', '[A-Za-z0-9]{20,64}')
    ->name('osif.invitation.pdf');
Route::get('/osif/invitation/{token}/card/download', [OsifInvitationController::class, 'downloadCard'])
    ->where('token', '[A-Za-z0-9]{20,64}')
    ->name('osif.invitation.card.download');
Route::get('/osif/invitation/{token}/pdf/download', [OsifInvitationController::class, 'downloadPdf'])
    ->where('token', '[A-Za-z0-9]{20,64}')
    ->name('osif.invitation.pdf.download');
Route::post('/osif/invitations/{registration}/files', [OsifInvitationController::class, 'storeFiles'])
    ->name('osif.invitation.files');

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
