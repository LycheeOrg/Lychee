<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Enum\StorageDiskType;
use App\Exceptions\SecurePaths\InvalidPayloadException;
use App\Exceptions\SecurePaths\InvalidSignatureException;
use App\Exceptions\SecurePaths\SignatureExpiredException;
use App\Exceptions\SecurePaths\WrongPathException;
use App\Models\Configs;
use App\Models\Extensions\HasUrlGenerator;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Controller responsible for serving files securely.
 */
class SecurePathController extends Controller
{
	use HasUrlGenerator;

	public function __invoke(Request $request, ?string $path)
	{
		if (is_null($path)) {
			throw new WrongPathException();
		}

		if (Configs::getValueAsBool('secure_image_link_enabled')) {
			try {
				$path = Crypt::decryptString($path);
			} catch (DecryptException) {
				throw new InvalidPayloadException();
			}
		}

		// In theory we should use the `$request->hasCorrectSignature()` method here.
		// However, for some stupid unknown reason, the path value is added to the server Query String.
		// This completely invalitates the signature check.
		// For example the url http://localhost:8000/image/small2x/c3/3d/c661c594a5a781cd44db06828783.png?expires=1748380289
		// will be verified as :
		// http://localhost:8000/image/small2x/c3/3d/c661c594a5a781cd44db06828783.png?/image/small2x/c3/3d/c661c594a5a781cd44db06828783.png&expires=1748380289
		// which makes the signature check fail as the hmac does not match.
		if (!self::shouldNotUseSignedUrl() && !$this->hasCorrectSignature($request)) {
			Log::error('Invalid signature for secure path request. Verify that the url generated for the image match.', [
				'candidate url' => $this->getUrl($request),
			]);
			throw new InvalidSignatureException();
		}

		// On the bright side, we can now differentiate between a missing/failed signature and an expired one.
		if (!self::shouldNotUseSignedUrl() && !$this->signatureHasNotExpired($request)) {
			throw new SignatureExpiredException();
		}

		$file = Storage::disk(StorageDiskType::LOCAL->value)->path($path);
		if (!file_exists($file)) {
			throw new WrongPathException();
		}

		return response()->file($file);
	}

	private function getUrl(Request $request, bool $absolute = true): string
	{
		$ignore_query = ['signature'];

		$url = $absolute ? $request->url() : ('/' . $request->path());

		$query_string = '';
		$query_string = (new Collection(explode('&', (string) $request->server->get('QUERY_STRING'))))
			->reject(fn ($parameter) => in_array(\Str::before($parameter, '='), $ignore_query, true))
			->reject(fn ($parameter) => count(explode('=', $parameter)) === 1) // Ignore parameters without value => avoid problem above mentionned.
			->join('&');

		return rtrim($url . '?' . $query_string, '?');
	}

	/**
	 * Determine if the signature from the given request matches the URL.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param bool                     $absolute
	 *
	 * @return bool
	 */
	private function hasCorrectSignature(Request $request, bool $absolute = true): bool
	{
		$original = $this->getUrl($request, $absolute);
		$key = new \SensitiveParameterValue(config('app.key'));
		if (hash_equals(
			hash_hmac('sha256', $original, $key->getValue()),
			$request->query('signature', '')
		)) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the expires timestamp from the given request is not from the past.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return bool
	 */
	private function signatureHasNotExpired(Request $request)
	{
		$expires = $request->query('expires');

		return !($expires !== null && $expires !== '' && Carbon::now()->getTimestamp() > $expires);
	}
}