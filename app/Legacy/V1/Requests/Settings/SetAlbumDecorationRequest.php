<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Settings;

use App\Enum\AlbumDecorationOrientation;
use App\Enum\AlbumDecorationType;
use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

/**
 * @codeCoverageIgnore Legacy stuff
 */
final class SetAlbumDecorationRequest extends BaseApiRequest
{
	protected AlbumDecorationType $albumDecoration;
	protected AlbumDecorationOrientation $albumDecorationOrientation;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, Configs::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_DECORATION_ATTRIBUTE => ['required', new Enum(AlbumDecorationType::class)],
			RequestAttribute::ALBUM_DECORATION_ORIENTATION_ATTRIBUTE => ['required', new Enum(AlbumDecorationOrientation::class)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumDecoration = AlbumDecorationType::from($values[RequestAttribute::ALBUM_DECORATION_ATTRIBUTE]);
		$this->albumDecorationOrientation = AlbumDecorationOrientation::from($values[RequestAttribute::ALBUM_DECORATION_ORIENTATION_ATTRIBUTE]);
	}

	/**
	 * @return AlbumDecorationType
	 */
	public function albumDecoration(): AlbumDecorationType
	{
		return $this->albumDecoration;
	}

	/**
	 * @return AlbumDecorationOrientation
	 */
	public function albumDecorationOrientation(): AlbumDecorationOrientation
	{
		return $this->albumDecorationOrientation;
	}
}
