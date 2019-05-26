<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Middleware;

use App\ControllerFunctions\ReadAccessFunctions;
use App\Logs;
use App\Photo;
use Closure;
use Illuminate\Http\Request;

class ReadCheck
{
    /**
     * @var ReadAccessFunctions
     */
    private $readAccessFunctions;

    public function __construct(ReadAccessFunctions $readAccessFunctions)
    {
        $this->readAccessFunctions = $readAccessFunctions;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->has('albumID')) {
            $sess = $this->readAccessFunctions->album($request['albumID']);
            if ($sess === 0) {
                Logs::error(__METHOD__, __LINE__, 'Could not find specified album');

                return response('false');
            }
            if ($sess === 2) {
                return response('"Warning: Album private!"');
            }
            if ($sess === 3) {
                return response('"Warning: Wrong password!"');
            }
        }

        if ($request->has('photoID')) {
            $photo = Photo::with('album')->find($request['photoID']);
            if ($photo === null) {
                Logs::error(__METHOD__, __LINE__, 'Could not find specified photo');

                return response('false');
            }
            if ($this->readAccessFunctions->photo($photo) === false) {
                return response('false');
            }
        }

        return $next($request); // access granted
    }
}
