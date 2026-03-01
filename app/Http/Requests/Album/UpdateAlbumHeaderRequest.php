<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\HasHeaderFocus;
use App\Contracts\Http\Requests\HasTitleCustomization;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\AlbumTitleColor;
use App\Enum\AlbumTitlePosition;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasHeaderFocusTrait;
use App\Http\Requests\Traits\HasTitleCustomizationTrait;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class UpdateAlbumHeaderRequest extends BaseApiRequest implements HasAlbum, HasTitleCustomization, HasHeaderFocus
{
	use HasAlbumTrait;
	use HasTitleCustomizationTrait;
	use HasHeaderFocusTrait;

	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * Prepare the data for validation.
	 */
	protected function prepareForValidation(): void
	{
		if ($this->has(RequestAttribute::HEADER_PHOTO_FOCUS_ATTRIBUTE)) {
			$focus = $this->input(RequestAttribute::HEADER_PHOTO_FOCUS_ATTRIBUTE);
			if (is_array($focus)) {
				if (isset($focus['x'])) {
					$focus['x'] = max(-1.0, min(1.0, floatval($focus['x'])));
				}
				if (isset($focus['y'])) {
					$focus['y'] = max(-1.0, min(1.0, floatval($focus['y'])));
				}
				$this->merge([RequestAttribute::HEADER_PHOTO_FOCUS_ATTRIBUTE => $focus]);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::ALBUM_TITLE_COLOR_ATTRIBUTE => ['present', 'nullable', new Enum(AlbumTitleColor::class)],
			RequestAttribute::ALBUM_TITLE_POSITION_ATTRIBUTE => ['present', 'nullable', new Enum(AlbumTitlePosition::class)],
			RequestAttribute::HEADER_PHOTO_FOCUS_ATTRIBUTE => ['present', 'array'],
			RequestAttribute::HEADER_PHOTO_FOCUS_ATTRIBUTE . '.x' => ['numeric', 'between:-1,1'],
			RequestAttribute::HEADER_PHOTO_FOCUS_ATTRIBUTE . '.y' => ['numeric', 'between:-1,1'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$album = $this->album_factory->findBaseAlbumOrFail(
			$values[RequestAttribute::ALBUM_ID_ATTRIBUTE]
		);

		if (!$album instanceof Album) {
			throw ValidationException::withMessages([RequestAttribute::ALBUM_ID_ATTRIBUTE => 'album type not supported.']);
		}

		$this->album = $album;
		$this->title_color = AlbumTitleColor::tryFrom($values[RequestAttribute::ALBUM_TITLE_COLOR_ATTRIBUTE] ?? null);
		$this->title_position = AlbumTitlePosition::tryFrom($values[RequestAttribute::ALBUM_TITLE_POSITION_ATTRIBUTE] ?? null);
		$this->header_photo_focus = $values[RequestAttribute::HEADER_PHOTO_FOCUS_ATTRIBUTE] ?? null;
	}
}
