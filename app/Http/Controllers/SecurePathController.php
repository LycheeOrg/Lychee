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
use App\Exceptions\SecurePaths\WrongPathException;
use App\Models\Configs;
use App\Models\Extensions\HasUrlGenerator;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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

		Log::warning('url:', $request->url());

		if (Configs::getValueAsBool('secure_image_link_enabled')) {
			try {
				$path = Crypt::decryptString($path);
			} catch (DecryptException) {
				throw new InvalidPayloadException();
			}
		}

		if (!self::shouldNotUseSignedUrl() && !$request->hasValidSignature(true)) {
			throw new InvalidSignatureException();
		}

		$file = Storage::disk(StorageDiskType::LOCAL->value)->path($path);
		if (!file_exists($file)) {
			throw new WrongPathException();
		}

		return response()->file($file);
	}
}