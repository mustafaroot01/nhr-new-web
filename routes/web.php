<?php

use App\Http\Controllers\Site\CategoryController;
use App\Http\Controllers\Site\ComplaintController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\FaqController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\MediaController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\PostController;
use App\Http\Controllers\Site\ReportController;
use App\Http\Controllers\Site\SearchController;
use Illuminate\Support\Facades\Route;

// Redirect root to the default locale.
Route::get('/', fn () => redirect('/ar'));

Route::prefix('{locale}')
    ->where(['locale' => 'ar|en'])
    ->middleware('setlocale')
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');

        Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
        Route::get('/posts/{slug}', [PostController::class, 'show'])->name('posts.show');

        Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');

        Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
        Route::post('/contact', [ContactController::class, 'store'])->name('contact.store')
            ->middleware('throttle:5,10'); // 5 submissions per 10 minutes per IP

        Route::get('/complaint', [ComplaintController::class, 'show'])->name('complaint.show');
        Route::post('/complaint', [ComplaintController::class, 'store'])->name('complaint.store')
            ->middleware('throttle:3,30'); // 3 complaints per 30 minutes per IP

        Route::get('/search', [SearchController::class, 'index'])->name('search.index')
            ->middleware('throttle:30,1'); // 30 searches per minute per IP

        // Generic page by slug — keep last so it doesn't shadow the named routes above.
        Route::get('/{slug}', [PageController::class, 'show'])->name('page.show');
    });
