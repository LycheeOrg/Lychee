<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\ShopManagement;

use App\Contracts\Http\Requests\HasDescription;
use App\Contracts\Http\Requests\RequestAttribute;
use App\DTO\PixelSizeAssignment;
use App\DTO\PrintSizeAssignment;
use App\DTO\PurchasableOptionCreate;
use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Models\Purchasable;
use App\Services\MoneyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class UpdatePurchasablePriceRequest extends BaseApiRequest implements HasDescription
{
	use HasDescriptionTrait;

	public ?string $notes;
	/** @var PurchasableOptionCreate[] */
	public array $prices;
	/** @var PrintSizeAssignment[] */
	public array $print_sizes;
	/** @var PixelSizeAssignment[] */
	public array $pixel_sizes;
	public Purchasable $purchasable;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		/** @var int|null $user_id */
		$user_id = Auth::id();
		if ($user_id === null) {
			return false;
		}

		return $this->configs()->getValueAsInt('owner_id') === $user_id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PURCHASABLE_ID_ATTRIBUTE => 'required|integer|exists:purchasables,id',
			RequestAttribute::DESCRIPTION_ATTRIBUTE => 'present|nullable|string|max:1000',
			RequestAttribute::NOTE_ATTRIBUTE => 'present|nullable|string|max:1000',
			RequestAttribute::PRICES_ATTRIBUTE => 'sometimes|array',
			RequestAttribute::PRICES_ATTRIBUTE . '.*.size_variant_type' => ['required', new Enum(PurchasableSizeVariantType::class)],
			RequestAttribute::PRICES_ATTRIBUTE . '.*.license_type' => ['required', new Enum(PurchasableLicenseType::class)],
			RequestAttribute::PRICES_ATTRIBUTE . '.*.price' => 'required|integer|min:0|max:1000000', // max 10,000.00 in cents
			RequestAttribute::PRINT_SIZES_ATTRIBUTE => 'sometimes|array',
			RequestAttribute::PRINT_SIZES_ATTRIBUTE . '.*.print_size_id' => 'required|integer|exists:print_sizes,id',
			RequestAttribute::PRINT_SIZES_ATTRIBUTE . '.*.price' => 'required|integer|min:0|max:1000000',
			RequestAttribute::PIXEL_SIZES_ATTRIBUTE => 'sometimes|array',
			RequestAttribute::PIXEL_SIZES_ATTRIBUTE . '.*.pixel_size_id' => 'required|integer|exists:pixel_sizes,id',
			RequestAttribute::PIXEL_SIZES_ATTRIBUTE . '.*.license_type' => ['required', new Enum(PurchasableLicenseType::class)],
			RequestAttribute::PIXEL_SIZES_ATTRIBUTE . '.*.price' => 'required|integer|min:0|max:1000000',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$purchasable_id = $values[RequestAttribute::PURCHASABLE_ID_ATTRIBUTE];
		$this->purchasable = Purchasable::findOrFail($purchasable_id);

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

		$this->print_sizes = [];
		foreach ($values[RequestAttribute::PRINT_SIZES_ATTRIBUTE] ?? [] as $item) {
			$this->print_sizes[] = new PrintSizeAssignment(
				print_size_id: $item['print_size_id'],
				price: $money_service->createFromCents($item['price']),
			);
		}

		$this->pixel_sizes = [];
		foreach ($values[RequestAttribute::PIXEL_SIZES_ATTRIBUTE] ?? [] as $item) {
			$this->pixel_sizes[] = new PixelSizeAssignment(
				pixel_size_id: $item['pixel_size_id'],
				price: $money_service->createFromCents($item['price']),
				license_type: PurchasableLicenseType::from($item['license_type']),
			);
		}
	}
}
