<?php

namespace Riipandi\Midtrans\Providers;

use Riipandi\Midtrans\Midtrans;
use Illuminate\Support\ServiceProvider;

class MidtransServiceProvider extends ServiceProvider {

    public function boot() {
        $this->app->singleton('midtrans', function ($app) {
            return new Midtrans;
        });
    }

    public function register() {
        //
    }

}
