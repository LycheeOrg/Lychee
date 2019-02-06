<?php

namespace App\Providers;

use App\Configs;
use App\Image;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\PhotoFunctions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        AlbumFunctions::class => AlbumFunctions::class,
        PhotoFunctions::class => PhotoFunctions::class
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    	if(config('app.debug', false))
	    {
		    DB::listen(function($query) {
			    Log::info(
				    $query->sql,
				    $query->bindings,
				    $query->time
			    );
		    });
	    }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Image\ImageHandlerInterface::class, function ($app) {
            if (Configs::hasImagick()) {
                return new Image\ImagickHandler;
            }

            return new Image\GdHandler;
        });
    }
}
