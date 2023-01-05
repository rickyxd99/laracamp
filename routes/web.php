<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\User\DashboardController as UserDashboard;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\CheckoutController as AdminCheckout;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');


Route::middleware('auth')->group(function() {
    Route::get('checkout/success', [CheckoutController::class, 'success'])->name('checkout.success')->middleware('ensureUserRole:user');
    Route::get('checkout/{camp:slug}', [CheckoutController::class, 'create'])->name('checkout.create')->middleware('ensureUserRole:user');
    Route::post('checkout/{camp}', [CheckoutController::class, 'store'])->name('checkout.store')->middleware('ensureUserRole:user');
    Route::get('dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
    Route::get('dashboard/checkout/invoice/{checkout}',[CheckoutController::class, 'invoice'])->name('user.checkout.invoice');
});


//socialite routes
Route::get('sign-in-google', [UserController::class, 'google'])->name('user.login.google');
Route::get('auth/google/callback', [UserController::class, 'handleProviderCallback'])->name('user.google.callback');
        
 // user dashboard
 Route::prefix('user/dashboard')->namespace('User')->name('user.')->middleware('ensureUserRole:user')->group(function(){
    Route::get('/', [UserDashboard::class, 'index'])->name('dashboard'); // called by route('user.dashboard')
});
// admin dashboard
Route::prefix('admin/dashboard')->namespace('Admin')->name('admin.')->middleware('ensureUserRole:admin')->group(function(){
    Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard'); // called by route('admin.dashboard')
    Route::post('checkout/{checkout}',[AdminCheckout::class, 'update'])->name('checkout.update'); // called by route('admin.checkout.update')
});



 
require __DIR__.'/auth.php';



// Route::get('checkout/{camp:slug}', function(){
//     return view ('checkout.create');
// })->name('checkout.create');


// Route::get('/dashboard', function () {
//     return view('user.dashboard');
// })->middleware(['auth'])->name('dashboard');

//checkout route
// Route::get('checkout/{camp:slug}', [CheckoutController::class, 'create'])->name('checkout.create');

// Route::get('checkout)', function () {
//     return view('checkout');
// })->name('checkout');

// Route::get('success-checkout', function () {
//     return view('success_checkout');
// })->name('success-checkout');

// Route::get('dashboard', function () {
//     return view('user.dashboard');
// })->name('dashboard');