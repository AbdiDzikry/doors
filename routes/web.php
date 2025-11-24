<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Master\ExternalParticipantController;
use App\Http\Controllers\Master\PantryItemController;
use App\Http\Controllers\Master\RoomController;
use App\Http\Controllers\Master\PriorityGuestController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Meeting\RoomReservationController;
use App\Http\Controllers\Meeting\RecurringMeetingController;
use App\Http\Controllers\Meeting\MeetingListController;
use App\Http\Controllers\Meeting\AnalyticsController;
use App\Http\Controllers\Meeting\BookingController;
use App\Http\Controllers\Settings\ConfigurationController;
use App\Http\Controllers\Settings\RolePermissionController;
use App\Http\Controllers\Dashboard\ReceptionistDashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('master/rooms/images/{filename}', [RoomController::class, 'getImage'])->name('master.rooms.image');
});

Route::middleware(['auth', 'role:Super Admin|Admin'])->name('master.')->prefix('master')->group(function () {
    // ... existing routes
    Route::get('external-participants/template', [ExternalParticipantController::class, 'downloadTemplate'])->name('external-participants.template');
    Route::post('external-participants/import', [ExternalParticipantController::class, 'import'])->name('external-participants.import');
    Route::get('external-participants/export', [ExternalParticipantController::class, 'export'])->name('external-participants.export');
    Route::resource('external-participants', ExternalParticipantController::class);
    Route::resource('pantry-items', PantryItemController::class);
    Route::resource('rooms', RoomController::class);
    Route::resource('priority-guests', PriorityGuestController::class);
    Route::get('users/template', [UserController::class, 'downloadTemplate'])->name('users.template');
    Route::post('users/import', [UserController::class, 'import'])->name('users.import');
    Route::get('users/export', [UserController::class, 'export'])->name('users.export');
    Route::resource('users', UserController::class);
    Route::resource('recurring-meetings', RecurringMeetingController::class);
});

Route::middleware(['auth', 'verified'])->prefix('meeting')->name('meeting.')->group(function () {
    Route::resource('room-reservations', RoomReservationController::class);
    Route::resource('bookings', BookingController::class);
    Route::resource('meeting-lists', MeetingListController::class);
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
});

Route::middleware(['auth', 'role:Super Admin'])->name('settings.')->prefix('settings')->group(function () {
    Route::resource('configurations', ConfigurationController::class);
    Route::resource('role-permissions', RolePermissionController::class)->parameters(['role-permissions' => 'role']);
});

Route::middleware(['auth', 'role:Resepsionis'])->name('dashboard.')->prefix('dashboard')->group(function () {
    Route::get('/receptionist', [ReceptionistDashboardController::class, 'index'])->name('receptionist');
    Route::put('/receptionist/pantry-orders/{pantry_order}/update', [ReceptionistDashboardController::class, 'update'])->name('receptionist.pantry-orders.update');
    Route::get('/receptionist/pantry-orders-partial', [ReceptionistDashboardController::class, 'getPantryOrdersPartial'])->name('receptionist.pantry-orders-partial');
});

require __DIR__.'/auth.php';
