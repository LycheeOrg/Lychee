<?php

namespace App\Http\Middleware;

use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\UnexpectedContentType;
use Illuminate\Http\Request;

/**
 * Class ContentType.
 *
 * This middleware ensures that the request has sent the correct content type
 * The only supported parameter value is `:json`.
 */
class ContentType
{
	public const JSON = 'json';
	public const MULTIPART = 'multipart';

	/**
	 * Handles the incoming request.
	 *
	 * @param Request  $request     the incoming request to serve
	 * @param \Closure $next        the next operation to be applied to the
	 *                              request
	 * @param string   $contentType the content type of the request's body;
	 *                              either {@link ContentType::JSON} or
	 *                              {@link ContentType::MULTIPART}
	 *
	 * @return mixed
	 *
	 * @throws UnexpectedContentType
	 * @throws LycheeInvalidArgumentException
	 */
	public function handle(Request $request, \Closure $next, string $contentType): mixed
	{
		if ($contentType === self::JSON) {
			if (!$request->isJson()) {
				throw new UnexpectedContentType(self::JSON);
			}
		} elseif ($contentType === self::MULTIPART) {
			if ($request->getContentType() !== 'form') {
				throw new UnexpectedContentType(self::JSON);
			}
		} else {
			throw new LycheeInvalidArgumentException('$contentType must be "' . self::JSON . '"');
		}

		return $next($request);
	}
}
