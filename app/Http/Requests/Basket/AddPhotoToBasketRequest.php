<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Basket;

use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\Enum;

class AddPhotoToBasketRequest extends BaseBasketRequest
{
	/**
	 * @var Photo
	 */
	public Photo $photo;

	/**
	 * @var string
	 */
	public string $album_id;

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
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		// Validate that the order support new additions
		// Validate that the photo is in the specified album
		return $this->order?->canAddItems() === true && $this->photo->albums()->where('id', $this->album_id)->exists();
	}

	/**
	 * Get the validation rules that apply to the request.
	 */
	public function rules(): array
	{
		return [
			'photo_id' => ['required', 'string'],
			'album_id' => ['required', 'string'],
			'size_variant' => ['required', new Enum(PurchasableSizeVariantType::class)],
			'license_type' => ['required', new Enum(PurchasableLicenseType::class)],
			'email' => ['sometimes', 'nullable', 'email'],
			'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
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
		$this->photo = Photo::query()->findOrFail($values['photo_id']);
		$this->album_id = $values['album_id'];
		$this->size_variant = PurchasableSizeVariantType::from($values['size_variant']);
		$this->license_type = PurchasableLicenseType::from($values['license_type']);
		$this->email = $values['email'] ?? null;
		$this->notes = $values['notes'] ?? null;
	}
}
