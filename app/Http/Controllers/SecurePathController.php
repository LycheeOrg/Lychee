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
use App\Exceptions\SecurePaths\PathTraversalException;
use App\Exceptions\SecurePaths\SignatureExpiredException;
use App\Exceptions\SecurePaths\WrongPathException;
use App\Http\Requests\SecurePath\SecurePathRequest;
use App\Models\Configs;
use App\Models\Extensions\HasUrlGenerator;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Controller responsible for serving files securely.
 */
class SecurePathController extends Controller
{
	use HasUrlGenerator;

	public function __invoke(SecurePathRequest $request, ?string $path)
	{
		// First we verify that the request has not expired.
		if (!self::shouldNotUseSignedUrl() && !$this->signatureHasNotExpired($request)) {
			throw new SignatureExpiredException();
		}

		// Then we verify that the request has a valid signature.
		// @phpstan-ignore staticMethod.dynamicCall (laravel magic strikes again)
		if (!self::shouldNotUseSignedUrl() && !$request->hasValidSignature()) {
			Log::error('Invalid signature for secure path request. Verify that the url generated for the image match.', [
				'candidate url' => $this->getUrl($request),
			]);
			throw new InvalidSignatureException();
		}

		if (is_null($path)) {
			throw new WrongPathException();
		}

		if ($request->configs()->getValueAsBool('secure_image_link_enabled')) {
			try {
				$path = Crypt::decryptString($path);
			} catch (DecryptException) {
				throw new InvalidPayloadException();
			}
		}

		$file = Storage::disk(StorageDiskType::LOCAL->value)->path($path);
		$valid_path_start = Storage::disk(StorageDiskType::LOCAL->value)->path('');
		if (!str_starts_with($file, $valid_path_start)) {
			Log::error('Invalid path for secure path request.', [
				'path' => $file,
				'valid_path_start' => $valid_path_start,
			]);
			throw new PathTraversalException('Invalid path for secure path request.');
		}

		// Do not leak whether the file exists or not before checking for traversal.
		if (!file_exists($file)) {
			throw new WrongPathException();
		}

		return response()->file($file);
	}

	private function getUrl(Request $request): string
	{
		$ignore_query = ['signature'];

		$query_string = '';
		$query_string = (new Collection(explode('&', (string) $request->server->get('QUERY_STRING'))))
			->reject(fn ($parameter) => in_array(\Str::before($parameter, '='), $ignore_query, true))
			->join('&');

		return rtrim($request->url() . '?' . $query_string, '?');
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
		/** @var int|null $expires */
		$expires = $request->query('expires') !== null ? intval($request->query('expires'), 10) : null;

		return !($expires !== null && $expires !== '' && Carbon::now()->getTimestamp() > $expires);
	}
}