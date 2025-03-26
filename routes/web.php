<?php

use App\Http\Controllers\AdminBoholIssuanceController;
use App\Http\Controllers\AdminFaqController;
use App\Http\Controllers\AdminField_OfficersController;
use App\Http\Controllers\AdminJobController;
use App\Http\Controllers\AdminLguController;
use App\Http\Controllers\AdminNewsController;
use App\Http\Controllers\AdminOrganizational_StructureController;
use App\Http\Controllers\AdminPDMUController;
use App\Http\Controllers\AdminProvincial_OfficialsController;

use App\Http\Controllers\GuestBoholIssuanceController;
use App\Http\Controllers\GuestFaqController;
use App\Http\Controllers\GuestField_OfficersController;
use App\Http\Controllers\GuestJobController;
use App\Http\Controllers\GuestLguController;
use App\Http\Controllers\GuestNewsController;
use App\Http\Controllers\GuestOrganizational_StructureController;
use App\Http\Controllers\GuestProvincial_OfficialsController;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
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
Route::get('/jobVacancies', [GuestJobController::class, 'index'])->name('guest.job');
Route::get('/fieldOfficers', [GuestField_OfficersController::class, 'index'])->name('guest.fieldOfficers');
Route::get('organizationalStructure', [GuestOrganizational_StructureController::class, 'index'])->name('guest.organizationalStructure');
Route::inertia('/knowledgeMaterials', 'Guest/KnowledgeMaterials')->name('KnowledgeMaterials');
Route::inertia('/legalOpinions', 'Guest/LegalOpinions')->name('LegalOpinions');
Route::inertia('/aboutUs', 'Guest/AboutUs')->name('AboutUs');
// Route::inertia('/organizationalStructure', 'Guest/OrganizationalStructure')->name('OrganizationalStructure');
Route::inertia('/citizensCharter', 'Guest/CitizensCharter')->name('CitizensCharter');
Route::inertia('/DILGFAMILY', 'Guest/DILGFAMILY')->name('DILGFAMILY');
Route::inertia('/contactInformation', 'Guest/ContactInformation')->name('ContactInformation');
Route::inertia('/downloadables', 'Guest/Downloadables')->name('Downloadables');

//Authenticated Routes
Route::get('/dashboard', function () {
    return Inertia::render('Admin/Dashboard');
})->middleware(['auth', 'verified', 'role:Publisher|Admin|Super-Admin'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:Publisher|Admin|Super-Admin')->group(function(){
        //News Admin Routes
        Route::get('/admin/news', [AdminNewsController::class, 'index'])->name('AdminNews');
        Route::post('/admin/news', [AdminNewsController::class, 'store'])->name('news.store');
        Route::post('/admin/news/{news}', [AdminNewsController::class, 'update'])->name('news.update');
        Route::delete('/admin/news/{news}', [AdminNewsController::class, 'destroy'])->name('news.destroy');
        Route::patch('/admin/news/{news}/toggle-status', [AdminNewsController::class, 'toggleStatus'])->name('news.toggle-status');

        //Job Vacancies Admin Routes
        Route::get('/admin/jobs', [AdminJobController::class, 'index'])->name('AdminJobs');
        Route::post('/admin/jobs', [AdminJobController::class, 'store'])->name('jobs.store');
        Route::post('/admin/jobs/{job}', [AdminJobController::class, 'update'])->name('jobs.update');
        Route::delete('/admin/jobs/{job}', [AdminJobController::class, 'destroy'])->name('jobs.destroy');
    });

    Route::middleware('role:Admin|Super-Admin')->group(function(){
        //Organizational Structure Admin Routes
        Route::get('/admin/organizational-structure', [AdminOrganizational_StructureController::class, 'index'])->name('AdminOrganizationalStructure');
        Route::post('/admin/organizational-structure', [AdminOrganizational_StructureController::class, 'store'])->name('organizational_structure.store');
        Route::post('/admin/organizational-structure/{organizational_structure}', [AdminOrganizational_StructureController::class, 'update'])->name('organizational_structure.update');
        Route::delete('/admin/organizational-structure/{organizational_structure}', [AdminOrganizational_StructureController::class, 'destroy'])->name('organizational_structure.destroy');

        //PDMU Admin Routes
        Route::get('/admin/pdmu', [AdminPDMUController::class, 'index'])->name('AdminPDMU');
        Route::post('/admin/pdmu', [AdminPDMUController::class, 'store'])->name('pdmu.store');
        Route::post('/admin/pdmu/{pdmu}', [AdminPDMUController::class, 'update'])->name('pdmu.update');
        Route::delete('/admin/pdmu/{pdmu}', [AdminPDMUController::class, 'destroy'])->name('pdmu.destroy');

        //Field Officers Admin Routes
        Route::get('/admin/field-officers', [AdminField_OfficersController::class, 'index'])->name('AdminFieldOfficers');
        Route::post('/admin/field-officers', [AdminField_OfficersController::class, 'store'])->name('field_officers.store');
        Route::post('/admin/field-officers/{field_officer}', [AdminField_OfficersController::class, 'update'])->name('field_officers.update');
        Route::delete('/admin/field-officers/{field_officer}', [AdminField_OfficersController::class, 'destroy'])->name('field_officers.destroy');

        //LGU Admin Routes
        Route::get('/admin/lgus', [AdminLguController::class, 'index'])->name('AdminLGUs');
        Route::post('/admin/lgus', [AdminLguController::class, 'store'])->name('lgu.store');
        Route::post('/admin/lgus/{lgu}', [AdminLguController::class, 'update'])->name('lgu.update');
        Route::delete('/admin/lgus/{lgu}', [AdminLguController::class, 'destroy'])->name('lgu.destroy');

        //Faq Admin Routes
        Route::get('/admin/faqs', [AdminFaqController::class, 'index'])->name('AdminFaqs');
        Route::post('/admin/faqs', [AdminFaqController::class, 'store'])->name('faqs.store');
        Route::post('/admin/faqs/{faq}', [AdminFaqController::class, 'update'])->name('faqs.update');
        Route::delete('/admin/faqs/{faq}', [AdminFaqController::class, 'destroy'])->name('faqs.destroy');

        //Issuances Admin Routes
        Route::get('/admin/issuances', [AdminBoholIssuanceController::class, 'index'])->name('AdminIssuances');
        Route::post('/admin/issuances', [AdminBoholIssuanceController::class, 'store'])->name('issuances.store');
        Route::post('/admin/issuances/{issuances}', [AdminBoholIssuanceController::class, 'update'])->name('issuances.update');
        Route::delete('/admin/issuances/{issuances}', [AdminBoholIssuanceController::class, 'destroy'])->name('issuances.destroy');

        //Downloadables Admin Routes
        Route::inertia('/adminDownloadables', 'Admin/AdminDownloadables')->name('AdminDownloadables');

        //Knowledge Materials Admin Routes
        Route::inertia('/adminKnowledgeMaterials', 'Admin/AdminKnowledgeMaterials')->name('AdminKnowledgeMaterials');

        //Prov Officials Admin Routes
        Route::get('/admin/provincial-officials', [AdminProvincial_OfficialsController::class, 'index'])->name('AdminProvincialOfficials');
        Route::post('/admin/provincial-officials', [AdminProvincial_OfficialsController::class, 'store'])->name('provincial_officials.store');
        Route::post('/admin/provincial-officials/{provincial_official}', [AdminProvincial_OfficialsController::class, 'update'])->name('provincial_officials.update');
        Route::delete('/admin/provincial-officials/{provincial_official}', [AdminProvincial_OfficialsController::class, 'destroy'])->name('provincial_officials.destroy');

        //Citizens Charter Admin Routes
        Route::inertia('/adminCitizensCharter', 'Admin/AdminCitizensCharter')->name('AdminCitizensCharter');

    });

    Route::middleware('role:Super-Admin')->group(function(){
        //User Admin Routes
        Route::get('/admin/users', [UserController::class, 'index'])->name('AdminUsers');
        Route::post('/admin/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/admin/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        //Logs Admin Routes
        Route::inertia('/adminLogs', 'Admin/AdminLogs')->name('AdminLogs');
    });
});

require __DIR__ . '/auth.php';
