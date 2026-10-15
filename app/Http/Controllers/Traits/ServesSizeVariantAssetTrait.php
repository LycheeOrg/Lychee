<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Traits;

use App\Enum\SizeVariantType;
use App\Image\Files\FlysystemFile;
use App\Image\Watermarker;
use App\Models\SizeVariant;
use App\Repositories\ConfigManager;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves a resolved size variant file: local file response, temporary
 * redirect for S3, watermarked path, and the size fallback chain when a
 * file is missing. Shared by `GET /api/v3/Asset/...` (Feature 056) and
 * `GET /api/v3/Faces/{face_id}/photo/{variant}` (Feature 093, ADR-093-01).
 */
trait ServesSizeVariantAssetTrait
{
	/**
	 * Serve `$size_variant`, falling back to smaller sizes when its file is missing.
	 *
	 * @return JsonResponse|mixed|BinaryFileResponse
	 */
	protected function serveSizeVariant(SizeVariant $size_variant, Watermarker $watermarker)
	{
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
		$fallback = $type->fallbackType();

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
