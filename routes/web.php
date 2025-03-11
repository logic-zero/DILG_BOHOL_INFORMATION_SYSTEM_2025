<?php

use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//Guest Routes
Route::get('/', function () {
    return Inertia::render('Guest/Home', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::inertia('/news', 'Guest/News')->name('news');
Route::inertia('/LGUs', 'Guest/LGUs')->name('LGUs');
Route::inertia('/provincialOfficials', 'Guest/ProvincialOfficials')->name('provincialOfficials');
Route::inertia('/knowledgeMaterials', 'Guest/KnowledgeMaterials')->name('KnowledgeMaterials');
Route::inertia('/latestIssuances', 'Guest/LatestIssuances')->name('LatestIssuances');
Route::inertia('/legalOpinions', 'Guest/LegalOpinions')->name('LegalOpinions');
Route::inertia('/aboutUs', 'Guest/AboutUs')->name('AboutUs');
Route::inertia('/organizationalStructure', 'Guest/OrganizationalStructure')->name('OrganizationalStructure');
Route::inertia('/fieldOfficers', 'Guest/FieldOfficers')->name('FieldOfficers');
Route::inertia('/citizensCharter', 'Guest/CitizensCharter')->name('CitizensCharter');
Route::inertia('/DILGFAMILY', 'Guest/DILGFAMILY')->name('DILGFAMILY');
Route::inertia('/contactInformation', 'Guest/ContactInformation')->name('ContactInformation');
Route::inertia('/downloadables', 'Guest/Downloadables')->name('Downloadables');
Route::inertia('/FAQs', 'Guest/FAQs')->name('FAQs');
Route::inertia('/jobVacancies', 'Guest/JobVacancies')->name('JobVacancies');

//Authenticated Routes
Route::get('/dashboard', function () {
    return Inertia::render('Admin/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/adminNews', [NewsController::class, 'index'])->name('AdminNews');
    Route::post('/news', [NewsController::class, 'store']);
    Route::post('/news/{news}', [NewsController::class, 'update'])->name('news.update');
    Route::delete('/news/{news}', [NewsController::class, 'destroy']);
    Route::patch('/news/{news}/toggle-status', [NewsController::class, 'toggleStatus']);

    Route::inertia('/adminJobVacancies', 'Admin/AdminJobVacancies')->name('AdminJobVacancies');
    Route::inertia('/adminLGUs', 'Admin/AdminLGUs')->name('AdminLGUs');
    Route::inertia('/adminFAQ', 'Admin/AdminFAQ')->name('AdminFAQ');
    Route::inertia('/adminIssuances', 'Admin/AdminIssuances')->name('AdminIssuances');
    Route::inertia('/adminDownloadables', 'Admin/AdminDownloadables')->name('AdminDownloadables');
    Route::inertia('/adminKnowledgeMaterials', 'Admin/AdminKnowledgeMaterials')->name('AdminKnowledgeMaterials');
    Route::inertia('/adminProvOfficials', 'Admin/AdminProvOfficials')->name('AdminProvOfficials');
    Route::inertia('/adminCitizensCharter', 'Admin/AdminCitizensCharter')->name('AdminCitizensCharter');
    Route::inertia('/adminLogs', 'Admin/AdminLogs')->name('AdminLogs');
    Route::inertia('/adminUsers', 'Admin/AdminUsers')->name('AdminUsers');
});

require __DIR__.'/auth.php';
