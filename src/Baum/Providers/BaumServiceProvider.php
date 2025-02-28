<?php

namespace Baum\Providers;

use Illuminate\Support\ServiceProvider;

class BaumServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {

        // Load Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Baum\Console\ModelMakeCommand::class,
            ]);
        }
    }
}
