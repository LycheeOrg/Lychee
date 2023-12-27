<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QueryStringFixer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (isset($_SERVER['QUERY_STRING']) && Str::startsWith($_SERVER['QUERY_STRING'], "/")) {
            $parts = explode('&', htmlspecialchars_decode($_SERVER["QUERY_STRING"]));
            array_shift($parts);
            $new = [];
            foreach ($parts as $v) {
                $x = explode('=', $v);
                $key = array_shift($x);
                $value = implode('=', $x);
                $new[$key] = $value;
            }
            $_SERVER['QUERY_STRING'] = http_build_query($new);
            $request->server->set("QUERY_STRING", $_SERVER['QUERY_STRING']);
        }
        return $next($request);
    }
}
