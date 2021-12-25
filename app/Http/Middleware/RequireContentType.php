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
 * The supported parameters are `:json`, `:html` and `:any'.
 */
class RequireContentType
{
	public const ANY = 'any';
	public const JSON = 'json';
	public const HTML = 'html';

	/**
	 * Handles the incoming request.
	 *
	 * The content type {@link self::ANY} is handled asymmetric between
	 * requests and responses.
	 * For a response this middleware explicitly ensures that the client
	 * is willing to accept any content type, i.e. the client has explicitly
	 * sent the header `Accept: * / *`.
	 * For a request this middleware accepts any request, i.e. does nothing.
	 * This is particularly helpful for GET requests, because GET requests
	 * usually do not have any content at all.
	 *
	 * @param Request  $request             the incoming request to serve
	 * @param \Closure $next                the next operation to be applied
	 *                                      to the request
	 * @param string   $responseContentType the content type which must be
	 *                                      acceptable by the client; either
	 *                                      {@link self::JSON},
	 *                                      {@link self::HTML}, or
	 *                                      {@link self::ANY}
	 * @param string   $requestContentType  the content type of the request's
	 *                                      body; either
	 *                                      {@link self::JSON},
	 *                                      {@link self::HTML}, or
	 *                                      {@link self::ANY}
	 *
	 * @return mixed
	 *
	 * @throws UnexpectedContentType
	 * @throws LycheeInvalidArgumentException
	 */
	public function handle(Request $request, \Closure $next, string $responseContentType, string $requestContentType): mixed
	{
		if ($responseContentType === self::JSON) {
			if (!$request->expectsJson()) {
				throw new UnexpectedContentType(self::JSON);
			}
		} elseif ($responseContentType === self::HTML) {
			if (!$request->acceptsHtml()) {
				throw new UnexpectedContentType(self::HTML);
			}
		} elseif ($responseContentType === self::ANY) {
			if (!$request->acceptsAnyContentType()) {
				throw new UnexpectedContentType(self::ANY);
			}
		} else {
			throw new LycheeInvalidArgumentException('$contentType must either be "' . self::JSON . '" or "' . self::HTML . '"');
		}

		if ($requestContentType === self::JSON) {
			if (!$request->isJson()) {
				throw new UnexpectedContentType(self::JSON);
			}
		} elseif ($requestContentType === self::HTML) {
			if ($request->getContentType() !== 'html') {
				throw new UnexpectedContentType(self::HTML);
			}
		} elseif ($requestContentType === self::ANY) {
			// sic! Do nothing
		} else {
			throw new LycheeInvalidArgumentException('$contentType must either be "' . self::JSON . '" or "' . self::HTML . '"');
		}

		return $next($request);
	}
}
