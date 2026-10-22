<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\StructOfArrays;

use App\Contracts\Models\AbstractAlbum;
use App\Enum\SizeVariantType;
use App\Http\Resources\V3\PhotoUrlResource;
use App\Image\Watermarker;
use App\Models\SizeVariant;
use App\Models\User;
use App\Repositories\ConfigManager;
use App\Services\UrlGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

/**
 * Query logic for `GET /api/v3/Albums/{album_id}/Photos/urls` (Feature 102,
 * FR-102-05, ADR-102-01): direct tile URLs, or `null` where the file needs
 * Lychee-side protection for this viewer.
 *
 * Two queries: the visible ids among the requested ones (same scoping as
 * {@see QueryPhotoDetails}), then their `small2x`/`small`/`thumb` rows.
 */
class QueryPhotoUrls
{
	use ResolvesPhotoSource;

	/** Grid tiles take the first existing row along the chain of their density; `thumb` is a square crop, so it comes last. */
	private const GRID_CHAINS = [
		1 => [SizeVariantType::SMALL, SizeVariantType::SMALL2X, SizeVariantType::THUMB],
		2 => [SizeVariantType::SMALL2X, SizeVariantType::SMALL, SizeVariantType::THUMB],
	];

	public function __construct(
		private readonly ConfigManager $config_manager,
		private readonly UrlGenerator $url_generator,
		private readonly Watermarker $watermarker,
	) {
	}

	/**
	 * @param string[] $photo_ids ids not in the album or not visible to the caller are left out, never a 4xx
	 * @param 1|2      $density   screen density of the grid
	 */
	public function do(AbstractAlbum $album, ?User $user, array $photo_ids, int $density): PhotoUrlResource
	{
		$grid_chain = self::GRID_CHAINS[$density];

		$visible = array_flip($this->resolvePhotoQuery($album, $user)
			->whereIn('photos.id', $photo_ids)
			->toBase()
			->pluck('photos.id')
			->all());
		// Request order, each id once.
		$ids = array_values(array_unique(array_filter($photo_ids, fn (string $id) => array_key_exists($id, $visible))));

		/** @var Collection<string,Collection<int,SizeVariant>> $variants_by_photo */
		$variants_by_photo = SizeVariant::query()
			->whereIn('photo_id', $ids)
			->whereIn('type', $grid_chain)
			->get()
			->groupBy('photo_id');

		$local_is_direct = $this->localFilesAreDirect();
		$grid_urls = [];
		$thumb_urls = [];
		foreach ($ids as $id) {
			$by_type = ($variants_by_photo->get($id) ?? collect())->keyBy(fn (SizeVariant $variant) => $variant->type->value);
			$grid = collect($grid_chain)->map(fn (SizeVariantType $type) => $by_type->get($type->value))->first(fn (?SizeVariant $variant) => $variant !== null);
			$grid_urls[] = $this->directUrl($grid, $local_is_direct);
			$thumb_urls[] = $this->directUrl($by_type->get(SizeVariantType::THUMB->value), $local_is_direct);
		}

		return new PhotoUrlResource(ids: $ids, grid_urls: $grid_urls, thumb_urls: $thumb_urls);
	}

	/**
	 * Local files are reachable by path for this viewer: no secure links,
	 * no temporary link required.
	 */
	private function localFilesAreDirect(): bool
	{
		return !$this->config_manager->getValueAsBool('secure_image_link_enabled') &&
			$this->url_generator->shouldNotUseSignedUrl();
	}

	/**
	 * The watermark-aware URL of `$variant`: always on S3 (bucket or
	 * presigned URL, what `/Asset` redirects to), only when
	 * `$local_is_direct` on other disks.
	 */
	private function directUrl(?SizeVariant $variant, bool $local_is_direct): ?string
	{
		if ($variant === null) {
			return null;
		}

		$disk = $variant->storage_disk->value;
		/** @disregard P1013 */
		$is_s3 = Storage::disk($disk)->getAdapter() instanceof AwsS3V3Adapter;
		if (!$is_s3 && !$local_is_direct) {
			return null;
		}

		return $this->url_generator->pathToUrl($this->watermarker->get_path($variant), $disk, $variant->type);
	}
}
