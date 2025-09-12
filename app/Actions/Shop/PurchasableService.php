<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Shop;

use App\Constants\PhotoAlbum as PA;
use App\DTO\PurchasableOption;
use App\DTO\PurchasableOptionCreate;
use App\Models\Album;
use App\Models\Photo;
use App\Models\Purchasable;
use App\Models\PurchasablePrice;
use Illuminate\Support\Facades\DB;

class PurchasableService
{
	/**
	 * Determine if a photo is purchasable and get its pricing information.
	 *
	 * @param Photo  $photo    The photo to check
	 * @param string $album_id The album ID to consider for hierarchical pricing
	 *
	 * @return Purchasable|null The purchasable item or null if not available for purchase
	 */
	public function getEffectivePurchasableForPhoto(Photo $photo, string $album_id): ?Purchasable
	{
		// First check for photo-specific pricing
		$photo_specific_price = Purchasable::where('photo_id', $photo->id)
			->where('is_active', true)
			->first();

		if ($photo_specific_price !== null) {
			return $photo_specific_price;
		}

		return Purchasable::query()
			->where('album_id', $album_id)
			->where('is_active', true)
			->whereNull('photo_id')
			->first();
	}

	/**
	 * Check if a photo is purchasable and get its pricing options.
	 *
	 * @param Photo  $photo    The photo to check
	 * @param string $album_id The album ID to consider for hierarchical pricing
	 *
	 * @return PurchasableOption[] Array of available pricing options, empty if not purchasable
	 */
	public function getPhotoOptions(Photo $photo, string $album_id): array
	{
		/** @var ?Purchasable $pricing */
		$pricing = $this->getEffectivePurchasableForPhoto($photo, $album_id);
		if ($pricing === null) {
			return [];
		}

		$options = [];

		/** @var PurchasablePrice $price */
		foreach ($pricing->prices as $price) {
			$options[] = new PurchasableOption(
				$price->size_variant,
				$price->license_type,
				$price->price_cents,
				$pricing->id,
			);
		}

		return $options;
	}

	/**
	 * Get all purchasable photos in an album (including sub-albums if applicable).
	 *
	 * @param Album $album             The album to check
	 * @param bool  $include_subalbums Whether to include photos in sub-albums
	 *
	 * @return \Illuminate\Support\Collection Collection of purchasable photos
	 */
	public function getPurchasablePhotosInAlbum(Album $album, bool $include_subalbums = false)
	{
		$query = Photo::query()->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id');

		if ($include_subalbums) {
			// Get photos from this album and all sub-albums
			$query->whereIn(PA::ALBUM_ID, DB::table('albums')->select('id')->where('_lft', '>=', $album->_lft)
				   ->where('_rgt', '<=', $album->_rgt));
		} else {
			// Just this album
			$query->where(PA::ALBUM_ID, $album->id);
		}

		// Get all photos that might be purchasable
		$photos = $query->get();

		// Filter to only include photos that have pricing available
		return $photos->filter(function (Photo $photo) use ($album): bool {
			return $this->getEffectivePurchasableForPhoto($photo, $album->id) !== null;
		});
	}

	/**
	 * Create a purchasable item for a photo.
	 *
	 * @param Photo                     $photo       The photo to make purchasable
	 * @param PurchasableOptionCreate[] $prices      Array PurchasableOptionCreate structures
	 * @param string|null               $description Public description
	 * @param string|null               $owner_notes Private notes for the owner
	 *
	 * @return Purchasable The created purchasable item
	 */
	public function createPurchasableForPhoto(
		Photo $photo,
		string $album_id,
		array $prices,
		?string $description = null,
		?string $owner_notes = null,
	): Purchasable {
		return DB::transaction(function () use ($photo, $album_id, $prices, $description, $owner_notes): Purchasable {
			// Remove any existing purchasable for this photo to avoid duplicates
			DB::table('purchasable_prices')->where('purchasable_id', function ($query) use ($photo): void {
				$query->select('id')->from('purchasables')->where('photo_id', $photo->id);
			})->delete();
			DB::table('purchasables')->where('photo_id', $photo->id)->delete();

			$purchasable = Purchasable::create([
				'photo_id' => $photo->id,
				'album_id' => $album_id,
				'description' => $description,
				'owner_notes' => $owner_notes,
				'is_active' => true,
			]);

			$this->updatePrices($purchasable, $prices);

			return $purchasable;
		});
	}

	/**
	 * Create a purchasable item for an album.
	 *
	 * @param Album                     $album                The album to make purchasable
	 * @param PurchasableOptionCreate[] $prices               Array of PurchasableOptionCreate structures
	 * @param bool                      $applies_to_subalbums Whether pricing applies to sub-albums
	 * @param string|null               $description          Public description
	 * @param string|null               $owner_notes          Private notes for the owner
	 *
	 * @return Purchasable The created purchasable item
	 */
	public function createPurchasableForAlbum(
		Album $album,
		array $prices,
		bool $applies_to_subalbums = false,
		?string $description = null,
		?string $owner_notes = null,
	): Purchasable {
		// Remove any existing purchasable for this album to avoid duplicates
		if ($applies_to_subalbums) {
			$album_ids = DB::table('albums')->select('id')->where('_lft', '>=', $album->_lft)
				->where('_rgt', '<=', $album->_rgt)
				->pluck('id')
				->toArray();
		} else {
			$album_ids = [$album->id];
		}

		// Clean the existing purchasables and their prices
		DB::table('purchasable_prices')->whereIn('purchasable_id', function ($query) use ($album_ids): void {
			$query->select('id')->from('purchasables')->whereNull('photo_id')->whereIn('album_id', $album_ids);
		})->delete();
		DB::table('purchasables')->whereNull('photo_id')->whereIn('album_id', $album_ids)->delete();

		$purchasable = [];
		foreach ($album_ids as $aid) {
			$purchasable[] = [
				'album_id' => $aid,
				'photo_id' => null,
				'description' => $description,
				'owner_notes' => $owner_notes,
				'is_active' => true,
			];
		}

		DB::table('purchasables')->insert($purchasable);

		// clear memory.
		unset($purchasable);

		$purchasables = Purchasable::query()->whereNull('photo_id')->whereIn('album_id', $album_ids)->get();

		foreach ($purchasables as $purchasable) {
			$this->updatePrices($purchasable, $prices);
		}

		return Purchasable::whereNull('photo_id')->where('album_id', $album->id)->first();
	}

	/**
	 * Update prices for a purchasable item.
	 *
	 * @param Purchasable               $purchasable The purchasable item to update
	 * @param PurchasableOptionCreate[] $prices      Array of PurchasableOptionCreate structures
	 *
	 * @return Purchasable The updated purchasable item
	 */
	public function updatePrices(Purchasable $purchasable, array $prices): Purchasable
	{
		// Clear existing prices
		$purchasable->prices()->delete();

		foreach ($prices as $price) {
			$purchasable->setPriceFor($price->size_variant, $price->license_type, $price->price);
		}

		return $purchasable;
	}

	/**
	 * Delete a purchasable item.
	 *
	 * @param Purchasable $purchasable The purchasable to delete
	 *
	 * @return bool True if deletion was successful
	 */
	public function deletePurchasable(Purchasable $purchasable): bool
	{
		// Delete all prices first
		$purchasable->prices()->delete();

		// Then delete the purchasable itself
		return $purchasable->delete();
	}
}
