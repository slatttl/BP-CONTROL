<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        // A fresh desktop database has no accounts, so the login screen would be unusable.
        if (Schema::hasTable('users') && ! User::query()->exists()) {
            Artisan::call('db:seed', ['--force' => true]);
        }

        Window::open()
            ->title('BP Control')
            ->width(1366)
            ->height(900);
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
