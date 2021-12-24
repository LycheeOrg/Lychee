<?php

namespace App\Http\Middleware;

use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\UnexpectedContentType;
use Illuminate\Http\Request;

/**
 * Class RequireContentType.
 *
 * This middleware ensures that the request has the correct content type
 * and that the client also accepts a suitable content type for a response.
 * The supported parameters are `:json` and `:html`.
 */
class RequireContentType
{
	public const JSON = 'json';
	public const HTML = 'html';

	/**
	 * Handles the incoming request.
	 *
	 * This middleware handles JSON and HTML request slightly differently.
	 * For JSON requests this middleware ensures that the transmitted
	 * content type (i.e. what the server receives) as well as what the client
	 * expects back is both JSON.
	 * For HTML request this middleware ensures that the client expects HTML.
	 * We do not check what the client sends, because traditionally HTML
	 * requests are pure GET requests and have no content at all.
	 *
	 * @param Request  $request     the incoming request to serve
	 * @param \Closure $next        the next operation to be applied to the
	 *                              request
	 * @param string   $contentType the required content type; either
	 *                              {@link self::JSON} or {@link self::HTML}
	 *
	 * @return mixed
	 *
	 * @throws UnexpectedContentType
	 * @throws LycheeInvalidArgumentException
	 */
	public function handle(Request $request, \Closure $next, string $contentType): mixed
	{
		if ($contentType === self::JSON) {
			if ($request->expectsJson() && $request->isJson()) {
				return $next($request);
			} else {
				throw new UnexpectedContentType(self::JSON);
			}
		} elseif ($contentType === self::HTML) {
			if ($request->acceptsHtml()) {
				return $next($request);
			} else {
				throw new UnexpectedContentType(self::HTML);
			}
		} else {
			throw new LycheeInvalidArgumentException('$contentType must either be "' . self::JSON . '" or "' . self::HTML . '"');
		}
	}
}
