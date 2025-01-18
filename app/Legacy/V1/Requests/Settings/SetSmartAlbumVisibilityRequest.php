<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Settings;

use App\Enum\SmartAlbumType;
use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasAbstractAlbum;
use App\Legacy\V1\Contracts\Http\Requests\HasIsPublic;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\HasAbstractAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasIsPublicTrait;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\In;

final class SetSmartAlbumVisibilityRequest extends BaseApiRequest implements HasAbstractAlbum, HasIsPublic
{
	use HasAbstractAlbumTrait;
	use HasIsPublicTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, Configs::class);
	}

	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => [
				'required',
				// We could use the Enum(SmartAlbumType::class) rule, but this is more targetted.
				new In([
					SmartAlbumType::RECENT->value,
					SmartAlbumType::STARRED->value,
					SmartAlbumType::ON_THIS_DAY->value,
				]),
			],
			RequestAttribute::IS_PUBLIC_ATTRIBUTE => 'required|boolean',
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findAbstractAlbumOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);
		$this->is_public = self::toBoolean($values[RequestAttribute::IS_PUBLIC_ATTRIBUTE]);
	}
}
