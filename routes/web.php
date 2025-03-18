<?php

use App\Http\Controllers\AdminBoholIssuanceController;
use App\Http\Controllers\AdminFaqController;
use App\Http\Controllers\AdminLguController;
use App\Http\Controllers\AdminNewsController;
use App\Http\Controllers\AdminProvincial_OfficialsController;
use App\Http\Controllers\GuestBoholIssuanceController;
use App\Http\Controllers\GuestFaqController;
use App\Http\Controllers\GuestLguController;
use App\Http\Controllers\GuestNewsController;
use App\Http\Controllers\GuestProvincial_OfficialsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//Guest Routes
Route::get('/', [HomeController::class, 'Index'])->name('home');
Route::get('/guestNews', [GuestNewsController::class, 'index'])->name('guest.news');
Route::get('/guestLGUs', [GuestLguController::class, 'index'])->name('guest.lgus');
Route::get('/latestIssuances', [GuestBoholIssuanceController::class, 'index'])->name('guest.latestIssuances');
Route::get('/provincialOfficials', [GuestProvincial_OfficialsController::class, 'index'])->name('guest.provincialOfficials');
Route::get('/FAQs', [GuestFaqController::class, 'index'])->name('guest.faqs');
// Route::inertia('/provincialOfficials', 'Guest/ProvincialOfficials')->name('provincialOfficials');
Route::inertia('/knowledgeMaterials', 'Guest/KnowledgeMaterials')->name('KnowledgeMaterials');
Route::inertia('/legalOpinions', 'Guest/LegalOpinions')->name('LegalOpinions');
Route::inertia('/aboutUs', 'Guest/AboutUs')->name('AboutUs');
Route::inertia('/organizationalStructure', 'Guest/OrganizationalStructure')->name('OrganizationalStructure');
Route::inertia('/fieldOfficers', 'Guest/FieldOfficers')->name('FieldOfficers');
Route::inertia('/citizensCharter', 'Guest/CitizensCharter')->name('CitizensCharter');
Route::inertia('/DILGFAMILY', 'Guest/DILGFAMILY')->name('DILGFAMILY');
Route::inertia('/contactInformation', 'Guest/ContactInformation')->name('ContactInformation');
Route::inertia('/downloadables', 'Guest/Downloadables')->name('Downloadables');
// Route::inertia('/FAQs', 'Guest/FAQs')->name('FAQs');
Route::inertia('/jobVacancies', 'Guest/JobVacancies')->name('JobVacancies');

//Authenticated Routes
Route::get('/dashboard', function () {
    return Inertia::render('Admin/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //News Admin Routes
    Route::get('/admin/news', [AdminNewsController::class, 'index'])->name('AdminNews');
    Route::post('/admin/news', [AdminNewsController::class, 'store'])->name('news.store');
    Route::post('/admin/news/{news}', [AdminNewsController::class, 'update'])->name('news.update');
    Route::delete('/admin/news/{news}', [AdminNewsController::class, 'destroy'])->name('news.destroy');
    Route::patch('/admin/news/{news}/toggle-status', [AdminNewsController::class, 'toggleStatus'])->name('news.toggle-status');

    //LGU Admin Routes
    Route::get('/admin/lgus', [AdminLguController::class, 'index'])->name('AdminLGUs');
    Route::post('/admin/lgus', [AdminLguController::class, 'store'])->name('lgu.store');
    Route::post('/admin/lgus/{lgu}', [AdminLguController::class, 'update'])->name('lgu.update');
    Route::delete('/admin/lgus/{lgu}', [AdminLguController::class, 'destroy'])->name('lgu.destroy');

    //Issuances Admin Routes
    Route::get('/admin/issuances', [AdminBoholIssuanceController::class, 'index'])->name('AdminIssuances');
    Route::post('/admin/issuances', [AdminBoholIssuanceController::class, 'store'])->name('issuances.store');
    Route::post('/admin/issuances/{issuances}', [AdminBoholIssuanceController::class, 'update'])->name('issuances.update');
    Route::delete('/admin/issuances/{issuances}', [AdminBoholIssuanceController::class, 'destroy'])->name('issuances.destroy');

    //Prov Officials Admin Routes
    Route::get('/admin/provincial-officials', [AdminProvincial_OfficialsController::class, 'index'])->name('AdminProvincialOfficials');
    Route::post('/admin/provincial-officials', [AdminProvincial_OfficialsController::class, 'store'])->name('provincial_officials.store');
    Route::post('/admin/provincial-officials/{provincial_official}', [AdminProvincial_OfficialsController::class, 'update'])->name('provincial_officials.update');
    Route::delete('/admin/provincial-officials/{provincial_official}', [AdminProvincial_OfficialsController::class, 'destroy'])->name('provincial_officials.destroy');

    //Faq Admin Routes
    Route::get('/admin/faqs', [AdminFaqController::class, 'index'])->name('AdminFaqs');
    Route::post('/admin/faqs', [AdminFaqController::class, 'store'])->name('faqs.store');
    Route::post('/admin/faqs/{faq}', [AdminFaqController::class, 'update'])->name('faqs.update');
    Route::delete('/admin/faqs/{faq}', [AdminFaqController::class, 'destroy'])->name('faqs.destroy');


    Route::inertia('/adminJobVacancies', 'Admin/AdminJobVacancies')->name('AdminJobVacancies');
    // Route::inertia('/adminFAQ', 'Admin/AdminFAQ')->name('AdminFAQ');
    Route::inertia('/adminDownloadables', 'Admin/AdminDownloadables')->name('AdminDownloadables');
    Route::inertia('/adminKnowledgeMaterials', 'Admin/AdminKnowledgeMaterials')->name('AdminKnowledgeMaterials');
    // Route::inertia('/adminProvOfficials', 'Admin/AdminProvOfficials')->name('AdminProvOfficials');
    Route::inertia('/adminCitizensCharter', 'Admin/AdminCitizensCharter')->name('AdminCitizensCharter');
    Route::inertia('/adminLogs', 'Admin/AdminLogs')->name('AdminLogs');
    Route::inertia('/adminUsers', 'Admin/AdminUsers')->name('AdminUsers');
});

require __DIR__ . '/auth.php';
