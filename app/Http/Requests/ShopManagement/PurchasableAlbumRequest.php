<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\ShopManagement;

use App\Contracts\Http\Requests\HasAlbums;
use App\Contracts\Http\Requests\HasDescription;
use App\Contracts\Http\Requests\RequestAttribute;
use App\DTO\PurchasableOptionCreate;
use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Models\Album;
use App\Models\Configs;
use App\Rules\RandomIDRule;
use App\Services\MoneyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class PurchasableAlbumRequest extends BaseApiRequest implements HasAlbums, HasDescription
{
	use HasAlbumsTrait;
	use HasDescriptionTrait;

	public ?string $notes;
	/** @var PurchasableOptionCreate[] */
	public array $prices;
	public bool $applies_to_subalbums;

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
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			RequestAttribute::DESCRIPTION_ATTRIBUTE => 'present|nullable|string|max:1000',
			RequestAttribute::NOTE_ATTRIBUTE => 'present|nullable|string|max:1000',
			RequestAttribute::PRICES_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PRICES_ATTRIBUTE . '.*.size_variant_type' => ['required', new Enum(PurchasableSizeVariantType::class)],
			RequestAttribute::PRICES_ATTRIBUTE . '.*.license_type' => ['required', new Enum(PurchasableLicenseType::class)],
			RequestAttribute::PRICES_ATTRIBUTE . '.*.price' => 'required|integer|min:0|max:1000000', // max 10,000.00 in cents
			RequestAttribute::APPLIES_TO_SUBALBUMS_ATTRIBUTE => 'required|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var array<int,string> $album_ids */
		$album_ids = $values[RequestAttribute::ALBUM_IDS_ATTRIBUTE];
		$this->albums = Album::query()->findOrFail($album_ids);

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

		$this->applies_to_subalbums = self::toBoolean($values[RequestAttribute::APPLIES_TO_SUBALBUMS_ATTRIBUTE]);
	}
}