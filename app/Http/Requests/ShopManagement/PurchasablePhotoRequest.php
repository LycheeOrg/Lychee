<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\ShopManagement;

use App\Constants\PhotoAlbum as PA;
use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\HasDescription;
use App\Contracts\Http\Requests\HasPhotos;
use App\Contracts\Http\Requests\RequestAttribute;
use App\DTO\PurchasableOptionCreate;
use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Rules\RandomIDRule;
use App\Services\MoneyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class PurchasablePhotoRequest extends BaseApiRequest implements HasPhotos, HasAlbum, HasDescription
{
	use HasPhotosTrait;
	use HasAlbumTrait;
	use HasDescriptionTrait;

	public ?string $notes;
	/** @var PurchasableOptionCreate[] */
	public array $prices;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		$user_id = Auth::id();
		if ($user_id === null) {
			return false;
		}

		return Configs::getValueAsInt('owner_id') === $user_id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::DESCRIPTION_ATTRIBUTE => 'present|nullable|string|max:1000',
			RequestAttribute::NOTE_ATTRIBUTE => 'present|nullable|string|max:1000',
			RequestAttribute::PRICES_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PRICES_ATTRIBUTE . '.*.size_variant_type' => ['required', new Enum(PurchasableSizeVariantType::class)],
			RequestAttribute::PRICES_ATTRIBUTE . '.*.license_type' => ['required', new Enum(PurchasableLicenseType::class)],
			RequestAttribute::PRICES_ATTRIBUTE . '.*.price' => 'required|integer|min:0|max:1000000', // max 10,000.00 in cents
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var array<int,string> $photos_ids */
		$photos_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->photos = Photo::query()->findOrFail($photos_ids);

		/** @var string */
		$target_album_id = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		$this->album = Album::query()->findOrFail($target_album_id);

		if (DB::table(PA::PHOTO_ALBUM)
			->whereIn(PA::PHOTO_ID, $photos_ids)
			->where(PA::ALBUM_ID, $target_album_id)
			->count() !== $this->photos->count()) {
		{
			throw new LycheeLogicException('One or more photos do not belong to the target album');
		}

		$this->description = $values[RequestAttribute::DESCRIPTION_ATTRIBUTE] ?? null;
		$this->notes = $values[RequestAttribute::NOTE_ATTRIBUTE] ?? null;

		$money_service = resolve(MoneyService::class);

		$this->prices = [];
		foreach ($values[RequestAttribute::PRICES_ATTRIBUTE] as $price) {
			$this->prices[] = new PurchasableOptionCreate(
				PurchasableSizeVariantType::from($price['size_variant_type']),
				PurchasableLicenseType::from($price['license_type']),
				$money_service->createFromCents($price['price']),
			);
		}
	}
}