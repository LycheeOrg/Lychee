<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Basket;

use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Models\Album;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\Enum;

class AddAlbumToBasketRequest extends BaseBasketRequest
{
	/**
	 * @var Album
	 */
	public Album $album;

	/**
	 * @var PurchasableSizeVariantType
	 */
	public PurchasableSizeVariantType $size_variant;

	/**
	 * @var PurchasableLicenseType
	 */
	public PurchasableLicenseType $license_type;

	/**
	 * @var string|null
	 */
	public ?string $email = null;

	/**
	 * @var string|null
	 */
	public ?string $notes = null;

	/**
	 * @var bool
	 */
	public bool $include_subalbums = false;

	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		return $this->order?->canAddItems() === true; // Requires an active order which accept items.
	}

	/**
	 * Get the validation rules that apply to the request.
	 */
	public function rules(): array
	{
		return [
			'album_id' => ['required', 'string'],
			'size_variant' => ['required', new Enum(PurchasableSizeVariantType::class)],
			'license_type' => ['required', new Enum(PurchasableLicenseType::class)],
			'email' => ['sometimes', 'nullable', 'email'],
			'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
			'include_subalbums' => ['sometimes', 'boolean'],
		];
	}

	/**
	 * Process the validated values.
	 *
	 * @param array<string,mixed>        $values
	 * @param array<string,UploadedFile> $files
	 *
	 * @return void
	 *
	 * @throws ModelNotFoundException
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$album = Album::query()->findOrFail($values['album_id']);
		if (!($album instanceof Album)) {
			throw new ModelNotFoundException('Invalid album');
		}
		$this->album = $album;
		$this->size_variant = PurchasableSizeVariantType::from($values['size_variant']);
		$this->license_type = PurchasableLicenseType::from($values['license_type']);
		$this->email = $values['email'] ?? null;
		$this->notes = $values['notes'] ?? null;
		$this->include_subalbums = self::toBoolean($values['include_subalbums'] ?? false);
	}
}
