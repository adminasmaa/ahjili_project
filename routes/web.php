<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\HelpController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ReportMessageController;
use App\Http\Controllers\Admin\UserMangementController;
use App\Http\Controllers\Admin\RoleController;

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

Auth::routes();
// //Language Translation

Route::get('/', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login');
//Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('logout');

Route::group(['prefix'=>'admin','as'=>'admin.','middleware' => 'auth'], function () {
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    //Users route
    Route::get('/userProfile/{id}', [UsersController::class, 'getUserProfile'])->name('userProfile');
    Route::get('/ChangePassword/{id}', [UsersController::class, 'ChangePassword'])->name('ChangePassword');
    Route::post('/updatePassword', [UsersController::class, 'updatePassword'])->name('updatePassword');
    Route::post('/store', [UsersController::class, 'store'])->name('users.store');

    //Posts route
    Route::resource('posts', PostController::class);
//    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');
    Route::get('/post-status/{active}', [PostController::class, 'status'])->name('posts.status');

    //report abuse
    Route::get('/report-abuse-posts', [PostController::class, 'abusePostsIndex'])->name('report-abuse-posts.index');
    Route::delete('/report-abuse-posts/{id}', [PostController::class, 'abusePostsDestory'])->name('report-abuse-posts.destory');
    //end
    //  All comments and likes from dashboard
    Route::get('/comments', [AdminController::class, 'getComments'])->name('comments.index');
    Route::get('/likes', [AdminController::class, 'getLikes'])->name('likes.index');


    Route::resource('users', UsersController::class);
    Route::resource('helps', HelpController::class);
    Route::resource('notifications', NotificationController::class);
    Route::resource('usermangements', UserMangementController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('report-messages', ReportMessageController::class);

});
