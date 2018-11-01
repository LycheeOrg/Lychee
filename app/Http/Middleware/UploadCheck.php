<?php

namespace App\Http\Middleware;


use App\User;
use Closure;
use Illuminate\Support\Facades\Session;

class UploadCheck
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
        // not logged!
        if (!Session::get('login'))
            return response('false');

        $id = Session::get('UserID');
        $user = User::find($id);

        // is admin or has upload rights
        if ($id == 0 || $user->upload)
            return $next($request);
        return response('false');
    }

}