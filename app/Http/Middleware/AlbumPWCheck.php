<?php
/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Middleware;

use App\ControllerFunctions\ReadAccessFunctions;
use Closure;
use Illuminate\Http\Request;

class AlbumPWCheck
{
	/**
	 * @var ReadAccessFunctions
	 */
	private $readAccessFunctions;



	function __construct(ReadAccessFunctions $readAccessFunctions)
	{
		$this->readAccessFunctions = $readAccessFunctions;
	}



	/**
	 * Handle an incoming request.
	 *
	 * @param Request $request
	 * @param Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if ($request->has('albumID')) {
			$sess = $this->readAccessFunctions->albums($request['albumID']);
			if ($sess === 0) {
				return response('false');
			}
			if ($sess === 1) {
				return $next($request);
			}
			if ($sess === 2) {
				return response('"Warning: Album private!"');
			}
			if ($sess === 3) {
				return response('"Warning: Wrong password!"');
			}
			// should not happen
			return response('false');
		}

		return response('"Error: no AlbumID provided"');
	}
}
