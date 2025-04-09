<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminBoholIssuanceController;
use App\Http\Controllers\AdminCitizens_CharterController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminDownloadableController;
use App\Http\Controllers\AdminFaqController;
use App\Http\Controllers\AdminField_OfficersController;
use App\Http\Controllers\AdminJobController;
use App\Http\Controllers\AdminKnowledge_MaterialsController;
use App\Http\Controllers\AdminLguController;
use App\Http\Controllers\AdminNewsController;
use App\Http\Controllers\AdminOrganizational_StructureController;
use App\Http\Controllers\AdminPDMUController;
use App\Http\Controllers\AdminProvincial_OfficialsController;

use App\Http\Controllers\GuestBoholIssuanceController;
use App\Http\Controllers\GuestCitizens_CharterController;
use App\Http\Controllers\GuestDownloadableController;
use App\Http\Controllers\GuestFaqController;
use App\Http\Controllers\GuestField_OfficersController;
use App\Http\Controllers\GuestJobController;
use App\Http\Controllers\GuestKnowledge_MaterialsController;
use App\Http\Controllers\GuestLguController;
use App\Http\Controllers\GuestNewsController;
use App\Http\Controllers\GuestOrganizational_StructureController;
use App\Http\Controllers\GuestProvincial_OfficialsController;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\JointCircularController;
use App\Http\Controllers\LegalOpinionController;
use App\Http\Controllers\PresidentialDirectiveController;
use App\Http\Controllers\PageVisitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProvincialDirectorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RepublicActController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//Guest Routes
Route::get('/', [HomeController::class, 'Index'])->name('home');
Route::get('/News', [GuestNewsController::class, 'index'])->name('guest.news');
Route::get('/News/{id}', [GuestNewsController::class, 'show'])->name('guest.news.show');
Route::get('/LGUs', [GuestLguController::class, 'index'])->name('guest.lgus');
Route::get('/latestIssuances', [GuestBoholIssuanceController::class, 'index'])->name('guest.latestIssuances');
Route::get('/provincialOfficials', [GuestProvincial_OfficialsController::class, 'index'])->name('guest.provincialOfficials');
Route::get('/FAQs', [GuestFaqController::class, 'index'])->name('guest.faqs');
Route::get('/downloadables', [GuestDownloadableController::class, 'index'])->name('guest.downloadables');
Route::get('/jobVacancies', [GuestJobController::class, 'index'])->name('guest.job');
Route::get('/fieldOfficers', [GuestField_OfficersController::class, 'index'])->name('guest.fieldOfficers');
Route::get('organizationalStructure', [GuestOrganizational_StructureController::class, 'index'])->name('guest.organizationalStructure');
Route::get('/citizensCharter', [GuestCitizens_CharterController::class, 'index'])->name('guest.citizensCharter');
Route::get('/citizens-charter/download-pdf', [GuestCitizens_CharterController::class, 'downloadPdf'])->name('citizens-charter.download-pdf');
Route::get('/legalOpinions', [LegalOpinionController::class, 'index'])->name('guest.legalOpinions');
Route::get('/republicActs', [RepublicActController::class, 'index'])->name('guest.republicActs');
Route::get('/presidentialDirectives', [PresidentialDirectiveController::class, 'index'])->name('guest.presidentialDirectives');
Route::get('/jointCirculars', [JointCircularController::class, 'index'])->name('guest.jointCirculars');
Route::get('/knowledgeMaterials', [GuestKnowledge_MaterialsController::class, 'index'])->name('guest.knowledgeMaterials');
Route::get('/knowledgeMaterials/download/{knowledgeMaterial}', [GuestKnowledge_MaterialsController::class, 'download'])->name('guest.knowledgeMaterials.download');
Route::get('/aboutUs', [AboutController::class, 'index'])->name('AboutUs');

Route::get('/provincialDirector', [ProvincialDirectorController::class, 'index'])->name('guest.provincialDirector');
Route::inertia('/DILGFAMILY', 'Guest/DILGFAMILY')->name('DILGFAMILY');
Route::inertia('/contactInformation', 'Guest/ContactInformation')->name('ContactInformation');
// Route::inertia('/downloadables', 'Guest/Downloadables')->name('Downloadables');

Route::post('/track-visit', [PageVisitController::class, 'trackVisit']);
Route::get('/visit-count', [PageVisitController::class, 'getVisitCount']);

//Authenticated Routes
Route::get('/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:Publisher|Admin|Super-Admin'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/image', [ProfileController::class, 'updateImage'])->name('profile.image.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/PDMessage', [ProfileController::class, 'storePDMessage'])->name('profile.PDMessage');

    Route::middleware('role:Publisher|Admin|Super-Admin')->group(function () {
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

    Route::middleware('role:Admin|Super-Admin')->group(function () {
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
        // Route::inertia('/adminDownloadables', 'Admin/AdminDownloadables')->name('AdminDownloadables');

        //Job Vacancies Admin Routes
        Route::get('/admin/downloadables', [AdminDownloadableController::class, 'index'])->name('AdminDownloadables');
        Route::post('/admin/downloadables', [AdminDownloadableController::class, 'store'])->name('downloadables.store');
        Route::post('/admin/downloadables/{downloadable}', [AdminDownloadableController::class, 'update'])->name('downloadables.update');
        Route::delete('/admin/downloadables/{downloadable}', [AdminDownloadableController::class, 'destroy'])->name('downloadables.destroy');


        //Knowledge Materials Admin Routes
        Route::get('/admin/knowledge-materials', [AdminKnowledge_MaterialsController::class, 'index'])->name('AdminKnowledgeMaterials');
        Route::post('/admin/knowledge-materials', [AdminKnowledge_MaterialsController::class, 'store'])->name('knowledge-materials.store');
        Route::post('/admin/knowledge-materials/{knowledgeMaterial}', [AdminKnowledge_MaterialsController::class, 'update'])->name('knowledge-materials.update');
        Route::delete('/admin/knowledge-materials/{knowledgeMaterial}', [AdminKnowledge_MaterialsController::class, 'destroy'])->name('knowledge-materials.destroy');
        Route::get('/admin/knowledge-materials/{knowledgeMaterial}/download', [AdminKnowledge_MaterialsController::class, 'download'])->name('knowledge-materials.download');

        //Prov Officials Admin Routes
        Route::get('/admin/provincial-officials', [AdminProvincial_OfficialsController::class, 'index'])->name('AdminProvincialOfficials');
        Route::post('/admin/provincial-officials', [AdminProvincial_OfficialsController::class, 'store'])->name('provincial_officials.store');
        Route::post('/admin/provincial-officials/{provincial_official}', [AdminProvincial_OfficialsController::class, 'update'])->name('provincial_officials.update');
        Route::delete('/admin/provincial-officials/{provincial_official}', [AdminProvincial_OfficialsController::class, 'destroy'])->name('provincial_officials.destroy');

        //Citizens Charter Admin Routes
        Route::get('/admin/citizens-charter', [AdminCitizens_CharterController::class, 'index'])->name('AdminCitizensCharter');
        Route::post('/admin/citizens-charter', [AdminCitizens_CharterController::class, 'store'])->name('citizens_charter.store');
        Route::post('/admin/citizens-charter/{citizens_charter}', [AdminCitizens_CharterController::class, 'update'])->name('citizens_charter.update');
        Route::delete('/admin/citizens-charter/{citizens_charter}', [AdminCitizens_CharterController::class, 'destroy'])->name('citizens_charter.destroy');
        Route::post('/citizens-charter/pdf', [AdminCitizens_CharterController::class, 'storePdf'])->name('citizens-charter.storePdf');
        Route::get('/citizens-charter/pdf/download', [AdminCitizens_CharterController::class, 'downloadPdf'])->name('citizens-charter.downloadPdf');

        Route::post('/home-images', [AdminDashboardController::class, 'storeImage'])->name('home-images.store');
        Route::post('/home-audio', [AdminDashboardController::class, 'storeAudio'])->name('home-audio.store');
    });

    Route::middleware('role:Super-Admin')->group(function () {
        //User Admin Routes
        Route::get('/admin/users', [UserController::class, 'index'])->name('AdminUsers');
        Route::post('/admin/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/admin/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        //Logs Admin Routes
        Route::get('/adminLogs', [ActivityLogController::class, 'index'])->name('AdminLogs');

    });
});

require __DIR__ . '/auth.php';
