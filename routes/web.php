<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return \Inertia\Inertia::render('Welcome');
})->name('welcome');


Route::middleware('auth')->group(function () {
    Route::get('/business/create', [BusinessController::class, 'create'])->name('business.create');
    Route::post('/business', [BusinessController::class, 'store'])->name('business.store');
    Route::post('/business/switch', [BusinessController::class, 'switch'])->name('business.switch');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'business'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/business/settings', [BusinessController::class, 'edit'])->name('business.edit');
    Route::patch('/business/settings', [BusinessController::class, 'update'])->name('business.update');

    Route::get('/reports', function () {
        return \Inertia\Inertia::render('Reports');
    })->name('reports');

    Route::resource('customers', CustomerController::class)->except(['show', 'create']);

    Route::resource('invoices', InvoiceController::class)->except('show');
    Route::post('/invoices/bulk-delete', [InvoiceController::class, 'bulkDestroy'])->name('invoices.bulk-delete');
    Route::get('/invoices/export-transactions', [InvoiceController::class, 'exportTransactions'])->name('invoices.export-transactions');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/preview', [InvoiceController::class, 'previewPdf'])->name('invoices.preview');
});
Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
})->name('google.redirect');

Route::get('/auth/google/callback', function () {
    $googleUser = Socialite::driver('google')->user();

    $user = User::updateOrCreate(
        ['email' => $googleUser->email],
        [
            'name' => $googleUser->name,
            'google_id' => $googleUser->id,
            'avatar' => $googleUser->avatar,
            'password' => bcrypt(uniqid()), // random password for non-password auth
        ]
    );

    Auth::login($user);

    return redirect('/dashboard');
})->name('google.callback');

require __DIR__ . '/auth.php';
