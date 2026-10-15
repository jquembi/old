<?php

use App\Livewire\PerfilOverview;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/overview');

    Route::get('settings/overview', PerfilOverview::class)->name('profile.overview');
    Route::get('settings/profile', Profile::class)->name('profile.edit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
    Route::get('settings/qr-layouts', \App\Livewire\ConfiguracaoDocumentoQr::class)
        ->middleware(['nivel:1,2'])
        ->name('settings.qr-layouts');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
