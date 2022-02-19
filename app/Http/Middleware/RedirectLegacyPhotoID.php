<?php

namespace App\Http\Middleware;

use App\Exceptions\ConfigurationException;
use App\Exceptions\Internal\FrameworkException;
use App\Http\Controllers\ViewController;
use App\Http\Requests\View\GetPhotoViewRequest;
use App\Legacy\Legacy;
use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Class RedirectLegacyPhotoID.
 *
 * This middleware is specifically crafted for
 * {@link GetPhotoViewRequest} and {@link ViewController::view()} to
 * redirect old photo IDs to new photo ID.
 */
class RedirectLegacyPhotoID
{
	/**
	 * Handle an incoming request.
	 *
	 * @param Request $request
	 * @param Closure $next
	 *
	 * @return mixed
	 *
	 * @throws ConfigurationException
	 * @throws FrameworkException
	 */
	public function handle(Request $request, Closure $next): mixed
	{
		try {
			$photoID = $request->query->get(GetPhotoViewRequest::URL_QUERY_PARAM);

			if (Legacy::isLegacyModelID($photoID)) {
				$photoID = Legacy::translateLegacyPhotoID($photoID, $request);
				if ($photoID) {
					return redirect()->route('view', ['p' => $photoID]);
				}
			}

			return $next($request);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		} catch (RouteNotFoundException $e) {
			throw new FrameworkException('Symfony\'s redirection component', $e);
		}
	}
}