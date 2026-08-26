<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Enum\SizeVariantType;
use App\Http\Requests\Photo\GetPhotoAssetRequest;
use App\Image\Files\FlysystemFile;
use App\Image\Watermarker;
use App\Models\SizeVariant;
use App\Repositories\ConfigManager;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves a single photo size-variant asset (Feature 056, API-056-01).
 */
class PhotoAssetController extends Controller
{
	public function show(GetPhotoAssetRequest $request, Watermarker $watermarker)
	{
		$size_variant = $request->sizeVariant();

		$response = $this->tryServe($size_variant, $watermarker);
		if ($response !== null) {
			return $response;
		}

		return $this->fallback($size_variant->photo_id, $size_variant->type, $watermarker);
	}

	/**
	 * Fallback on diferent smaller sizes.
	 *
	 * @param string          $photo_id
	 * @param SizeVariantType $type
	 * @param Watermarker     $watermarker
	 *
	 * @return JsonResponse|mixed|BinaryFileResponse
	 */
	private function fallback(string $photo_id, SizeVariantType $type, Watermarker $watermarker)
	{
		$fallback = match ($type) {
			SizeVariantType::SMALL2X => SizeVariantType::SMALL,
			SizeVariantType::SMALL => SizeVariantType::THUMB,
			SizeVariantType::THUMB2X => SizeVariantType::THUMB,
			default => null,
		};

		if ($fallback === null) {
			return response()->json(['error' => 'File not found'], 404);
		}

		$size_variant = SizeVariant::where('photo_id', $photo_id)
			->where('type', $fallback)
			->first();

		if ($size_variant === null) {
			return $this->fallback($photo_id, $fallback, $watermarker);
		}

		$response = $this->tryServe($size_variant, $watermarker);
		if ($response !== null) {
			return $response;
		}

		return $this->fallback($photo_id, $fallback, $watermarker);
	}

	/**
	 * Resolve the given size variant's own disk and serve it from there,
	 * or return null if it isn't actually present on that disk.
	 *
	 * @param SizeVariant $size_variant
	 * @param Watermarker $watermarker
	 *
	 * @return JsonResponse|mixed|BinaryFileResponse|null
	 */
	private function tryServe(SizeVariant $size_variant, Watermarker $watermarker)
	{
		$path = $watermarker->get_path($size_variant);
		$disk = Storage::disk($size_variant->storage_disk->value);

		/** @disregard P1013 */
		if ($disk->getAdapter() instanceof AwsS3V3Adapter) {
			$life_in_seconds = resolve(ConfigManager::class)->getValueAsInt('temporary_image_link_life_in_seconds');

			/** @disregard P1013 */
			return redirect()->away($disk->temporaryUrl($path, now()->addSeconds($life_in_seconds)));
		}

		if (!$disk->exists($path)) {
			return null;
		}

		return $this->responseFile($disk, $path);
	}

	/**
	 * Return the damn file!
	 *
	 * @param FilesystemAdapter $disk
	 * @param string            $path
	 *
	 * @return BinaryFileResponse
	 */
	private function responseFile(FilesystemAdapter $disk, string $path): BinaryFileResponse
	{
		$file = new FlysystemFile($disk, $path);

		return response()->file($file->toLocalFile()->getPath());
	}
}
