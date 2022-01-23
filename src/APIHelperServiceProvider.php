<?php

namespace Mpokket\APIHelper;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Mpokket\APIHelper\Middleware\DeprecationMiddleware;

class APIHelperServiceProvider extends ServiceProvider {

    /**
     * @throws BindingResolutionException
     */
    public function boot()
    {
        app()->make(Kernel::class)->prependMiddleware(DeprecationMiddleware::class);
    }
}
