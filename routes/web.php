<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TriageController; 
use App\Http\Controllers\Doctor\ConsultationController;
use App\Http\Controllers\NotificationController;


/*
|--------------------------------------------------------------------------
| Public Routes (Authentication)
|--------------------------------------------------------------------------
| ...
*/

// Redirect root to login page if not authenticated
Route::get('/', function () {
    return redirect()->route('login');
});

// Login and Logout
Route::group(['middleware' => 'guest'], function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Outpatient Module)
|--------------------------------------------------------------------------
| ...
*/

Route::group(['middleware' => 'auth'], function () {
    
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // DASHBOARD
    Route::get('/dashboard/outpatient', [DashboardController::class, 'index'])->name('outpatient.dashboard');
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/notifications/fetch', [DashboardController::class, 'fetchNotifications']) ->name('notifications.fetch');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');



    
    // PATIENT REGISTRATION (Receptionist Role)
    //WORKFLOW STEP 1: REGISTRATION (Receptionist Role)
    Route::prefix('outpatient')->group(function () {
        Route::get('/register', [PatientController::class, 'showRegistrationForm'])->name('outpatient.register');
        Route::post('/search', [PatientController::class, 'searchPatient'])->name('outpatient.search');
        Route::post('/store', [PatientController::class, 'storePatientAndVisit'])->name('outpatient.store');
        Route::get('/patient/view/{id}', function ($id) {
        $patient = \App\Models\Patient::with('visits')->findOrFail($id);
         return view('outpatient.patient-view', compact('patient'));
         })->name('patient.view');
        Route::get('/patient/edit/{id}', [PatientController::class, 'edit'])->name('patient.edit');
        Route::put('/patient/{id}', [PatientController::class, 'update'])->name('patient.update');
        

    });

    // WORKFLOW STEP 2: TRIAGE (Nurse Role)
    Route::prefix('triage')->group(function () {
        //this is triage for the receptionist top send tken to the nurse.
        Route::post('/triage/queue/{token}', [TriageController::class, 'sendToTriageQueue'])->name('triage.send_to_queue');
        Route::get('/', [DashboardController::class, 'index'])->name('triage.index');
        Route::get('/start/{visit_token}', [TriageController::class, 'startTriage'])->name('triage.start');
        Route::post('/store/{visit_id}', [TriageController::class, 'storeTriage'])->name('triage.store');
        Route::get('/triage/{token}/view', [TriageController::class, 'viewTriage'])->name('triage.view');
        Route::get('/triage/{visit_token}/edit', [TriageController::class, 'edit'])->name('triage.edit');
        Route::patch('/triage/{visit_token}/update', [TriageController::class, 'update'])->name('triage.update');
    });

     // WORKFLOW STEP 3: CONSULTATION (Doctor Role)
    Route::prefix('consultation')->group(function () { // <-- New Route Group
        Route::get('/start/{visit_token}', [ConsultationController::class, 'startConsultation'])->name('consultation.start');
        Route::post('/store/{visit}', [ConsultationController::class, 'storeOrUpdate'])->name('consultation.store'); // Assuming route model binding for Visit
    });

    // ... other future workflow steps (Lab, Pharmacy, Billing) will follow here ...
});
