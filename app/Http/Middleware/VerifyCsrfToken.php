<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;
use App\Configs;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        // entry point...
        '/php/index.php'
    ];

    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/*')) {
            $apiKey = Configs::get_value('api_key');
            if ($apiKey && $request->header('Authorization') === $apiKey) {
                return $next($request);
            }
        }

        return parent::handle($request, $next);
    }
}
