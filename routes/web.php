<?php

use App\Http\Controllers\{
    BookController,
    CategoryController,
    FileController,
    HomeController,
    ManufacturerController,
    TypeController
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
    $route = Auth::user()
        ? 'home'
        : 'login';    

    return redirect()->route($route);
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/types', [TypeController::class, 'index'])->name('types.index');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/manufacturers', [ManufacturerController::class, 'index'])->name('manufacturers.index');
Route::delete('/files/remove-uploaded-photos-without-directory', [FileController::class, 'removeUploadedFilesWithoutDirectory'])->name('files.remove-uploaded-files-without-directory');
Route::resource('files', FileController::class)->only(['store', 'destroy']);
Route::resource('books', BookController::class)->except(['create', 'edit']);