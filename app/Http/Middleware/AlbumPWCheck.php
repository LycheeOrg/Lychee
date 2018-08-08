<?php

namespace App\Http\Middleware;

use App\Http\Controllers\SessionController;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class AlbumPWCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return SessionController::checkAccess($request, $next($request),Response::HTTP_FORBIDDEN);
    }
}
