<?php

/*
|--------------------------------------------------------------------------
| Module Routes
|--------------------------------------------------------------------------
| Master data + operational module routes. All require authentication; fine
| grained authorization is enforced by Policies inside controllers, with
| `role:` middleware as a coarse gate. `whereNumber` on id params prevents
| `/create` (and other word segments) from colliding with `/{id}` routes.
*/

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AirlineController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\ManasikController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PilgrimController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    /* ===================== MASTER DATA ===================== */

    Route::resource('branches', BranchController::class)
        ->whereNumber('branch')->except('show')->middleware('role:super_admin');

    Route::resource('hotels', HotelController::class)
        ->whereNumber('hotel')->except('show')->middleware('role:super_admin,admin');

    Route::resource('airlines', AirlineController::class)
        ->whereNumber('airline')->except('show')->middleware('role:super_admin,admin');

    Route::resource('bank-accounts', BankAccountController::class)
        ->whereNumber('bank_account')->except('show')->middleware('role:super_admin,admin');

    // Packages: staff manage; agents may browse (index/show) to register jamaah.
    Route::resource('packages', PackageController::class)
        ->whereNumber('package')->only(['index', 'show'])
        ->middleware('role:super_admin,admin,agent');
    Route::resource('packages', PackageController::class)
        ->whereNumber('package')->except(['index', 'show'])
        ->middleware('role:super_admin,admin');

    Route::resource('agents', AgentController::class)
        ->whereNumber('agent')->except('show')->middleware('role:super_admin,admin');

    Route::resource('guides', GuideController::class)
        ->whereNumber('guide')->except('show')->middleware('role:super_admin,admin');

    // Jamaah: list/detail visible to staff/agent/guide (scoped); writes for staff/agent.
    Route::resource('pilgrims', PilgrimController::class)
        ->whereNumber('pilgrim')->only(['index', 'show'])
        ->middleware('role:super_admin,admin,agent,guide');
    Route::resource('pilgrims', PilgrimController::class)
        ->whereNumber('pilgrim')->except(['index', 'show'])
        ->middleware('role:super_admin,admin,agent');

    /* ===================== OPERASIONAL ===================== */

    // Pendaftaran / Booking (staff + agent; authorization via RegistrationPolicy)
    Route::put('registrations/{registration}/status', [RegistrationController::class, 'updateStatus'])
        ->whereNumber('registration')->name('registrations.status');
    Route::resource('registrations', RegistrationController::class)
        ->whereNumber('registration');

    // Dokumen Jamaah (checklist + private upload + verify)
    Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('documents/pilgrim/{pilgrim}', [DocumentController::class, 'show'])
        ->whereNumber('pilgrim')->name('documents.show');
    Route::post('documents/pilgrim/{pilgrim}', [DocumentController::class, 'store'])
        ->whereNumber('pilgrim')->name('documents.store');
    Route::post('documents/{document}/upload', [DocumentController::class, 'upload'])
        ->whereNumber('document')->name('documents.upload');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])
        ->whereNumber('document')->name('documents.download');
    Route::put('documents/{document}/verify', [DocumentController::class, 'verify'])
        ->whereNumber('document')->name('documents.verify')->middleware('role:super_admin,admin');
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])
        ->whereNumber('document')->name('documents.destroy');

    // Pembayaran & Cicilan
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/generate-massal', [PaymentController::class, 'generateMassalForm'])
        ->name('payments.generate-massal')->middleware('role:super_admin,admin');
    Route::post('payments/generate-massal', [PaymentController::class, 'generateMassal'])
        ->middleware('role:super_admin,admin');
    Route::get('payments/registration/{registration}', [PaymentController::class, 'show'])
        ->whereNumber('registration')->name('payments.bills');
    Route::post('payments/registration/{registration}/generate', [PaymentController::class, 'generate'])
        ->whereNumber('registration')->name('payments.generate')->middleware('role:super_admin,admin');
    Route::post('payments/{payment}/record', [PaymentController::class, 'record'])
        ->whereNumber('payment')->name('payments.record')->middleware('role:super_admin,admin');

    // Manasik (jadwal + absensi)
    Route::post('manasik/{manasik}/attendance', [ManasikController::class, 'storeAttendance'])
        ->whereNumber('manasik')->name('manasik.attendance')
        ->middleware('role:super_admin,admin,guide');
    Route::resource('manasik', ManasikController::class)
        ->parameters(['manasik' => 'manasik'])->whereNumber('manasik')
        ->middleware('role:super_admin,admin,agent,guide,pilgrim');

    // Manifest & Rooming (staff)
    Route::get('manifests', [ManifestController::class, 'index'])
        ->name('manifests.index')->middleware('role:super_admin,admin');
    Route::get('manifests/{package}', [ManifestController::class, 'show'])
        ->whereNumber('package')->name('manifests.show')->middleware('role:super_admin,admin');
    Route::put('manifests/{package}', [ManifestController::class, 'update'])
        ->whereNumber('package')->name('manifests.update')->middleware('role:super_admin,admin');

    // Perlengkapan (staff)
    Route::resource('equipment', EquipmentController::class)
        ->parameters(['equipment' => 'equipment'])->whereNumber('equipment')
        ->except('show')->middleware('role:super_admin,admin');

    // Komisi Agen (index visible to agent — scoped; status change staff-only)
    Route::get('commissions', [CommissionController::class, 'index'])->name('commissions.index');
    Route::put('commissions/{commission}', [CommissionController::class, 'updateStatus'])
        ->whereNumber('commission')->name('commissions.status')->middleware('role:super_admin,admin');

    // Pengumuman (staff)
    Route::resource('announcements', AnnouncementController::class)
        ->whereNumber('announcement')->except('show')->middleware('role:super_admin,admin');

    /* ===================== SISTEM ===================== */

    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->whereNumber('user')->name('users.reset-password')->middleware('role:super_admin,admin');
    Route::resource('users', UserController::class)
        ->whereNumber('user')->except('show')->middleware('role:super_admin,admin');

    Route::get('activity-logs', [ActivityLogController::class, 'index'])
        ->name('activity-logs.index')->middleware('role:super_admin,admin');

    /* ===================== PDF / CETAK ===================== */

    Route::get('pdf/payment/{payment}/receipt', [PdfController::class, 'receipt'])
        ->whereNumber('payment')->name('pdf.receipt');
    Route::get('pdf/package/{package}/manifest', [PdfController::class, 'manifest'])
        ->whereNumber('package')->name('pdf.manifest')->middleware('role:super_admin,admin');
    Route::get('pdf/registration/{registration}/card', [PdfController::class, 'pilgrimCard'])
        ->whereNumber('registration')->name('pdf.card');
});
