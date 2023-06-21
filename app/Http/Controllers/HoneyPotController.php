<?php

namespace App\Http\Controllers;

use App\Exceptions\HttpHoneyPotException;
use Illuminate\Routing\Controller;
use function Safe\preg_match;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * This is a HoneyPot. We use this to allow Fail2Ban to stop scanning.
 * The goal is pretty simple, if you are hitting this controller, and touch the honey,
 * then this means that you have no interest in our pictures.
 */
class HoneyPotController extends Controller
{
	public function __invoke(string $path = ''): void
	{
		// Check if Honey is available
		if (config('honeypot.enabled', true) !== true) {
			$this->throwNotFound($path);
		}

		/** @var array<int,string> $honeypot_paths_array */
		$honeypot_paths_array = config('honeypot.paths', []);

		/** @var array<int,string> $honeypot_xpaths_array_prefix */
		$honeypot_xpaths_array_prefix = config('honeypot.xpaths.prefix', []);

		/** @var array<int,string> $honeypot_xpaths_array_suffix */
		$honeypot_xpaths_array_suffix = config('honeypot.xpaths.suffix', []);

		foreach ($honeypot_xpaths_array_prefix as $prefix) {
			foreach ($honeypot_xpaths_array_suffix as $suffix) {
				$honeypot_paths_array[] = $prefix . '.' . $suffix;
			}
		}

		// Turn the path array into a regex pattern.
		// We escape . and / to avoid confusions with other regex characters
		$honeypot_paths = '/^(' . str_replace(['.', '/'], ['\.', '\/'], implode('|', $honeypot_paths_array)) . ')/i';

		// If the user tries to access a honeypot path, fail with the teapot code.
		if (preg_match($honeypot_paths, $path) !== 0) {
			$this->throwTeaPot($path);
		}

		// Otherwise just display our regular 404 page.
		$this->throwNotFound($path);
	}

	/**
	 * using abort(404) does not give the info which path was called.
	 * This could be very useful when debugging.
	 * By throwing a proper exception we preserve this info.
	 * This will generate a log line of type ERROR.
	 *
	 * @param string $path called
	 *
	 * @return never
	 *
	 * @throws NotFoundHttpException
	 */
	public function throwNotFound(string $path)
	{
		throw new NotFoundHttpException(sprintf('The route %s could not be found.', $path));
	}

	/**
	 * Similar to abort(404), abort(418) does not give info.
	 * It is more interesting to raise a proper exception.
	 * By having a proper exception we are also able to decrease the severity to NOTICE.
	 *
	 * @param string $path called
	 *
	 * @return never
	 *
	 * @throws HttpHoneyPotException
	 */
	public function throwTeaPot(string $path)
	{
		throw new HttpHoneyPotException($path);
	}
}
