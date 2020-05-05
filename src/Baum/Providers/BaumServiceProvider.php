<?php

namespace Baum\Providers;

use Illuminate\Support\ServiceProvider;

class BaumServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {

        // Load Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Baum\Console\ModelMakeCommand::class,
            ]);
        }
    }
}
