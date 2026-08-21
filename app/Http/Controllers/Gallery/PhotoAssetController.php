<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Http\Requests\Photo\GetPhotoAssetRequest;
use App\Image\Files\FlysystemFile;
use App\Image\Watermarker;
use App\Repositories\ConfigManager;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

/**
 * Serves a single photo size-variant asset (Feature 056, API-056-01).
 */
class PhotoAssetController extends Controller
{
	public function show(GetPhotoAssetRequest $request)
	{
		$size_variant = $request->sizeVariant();
		$path = resolve(Watermarker::class)->get_path($size_variant);
		$disk = Storage::disk($size_variant->storage_disk->value);

		/** @disregard P1013 */
		if ($disk->getAdapter() instanceof AwsS3V3Adapter) {
			$life_in_seconds = resolve(ConfigManager::class)->getValueAsInt('temporary_image_link_life_in_seconds');

			/** @disregard P1013 */
			return redirect()->away($disk->temporaryUrl($path, now()->addSeconds($life_in_seconds)));
		}

		$file = new FlysystemFile($disk, $path);

		return response()->file($file->toLocalFile()->getPath());
	}
}
