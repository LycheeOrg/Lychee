<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Sharing;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Policies\AlbumPolicy;
use App\Rules\IntegerIDRule;
use Illuminate\Support\Facades\Gate;

/**
 * @codeCoverageIgnore Legacy stuff we don't care.
 */
final class DeleteSharingRequest extends BaseApiRequest
{
	public const SHARE_IDS_ATTRIBUTE = 'shareIDs';

	/**
	 * @var array<int>
	 */
	protected array $shareIDs = [];

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_SHARE_ID, [AbstractAlbum::class, $this->shareIDs]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			self::SHARE_IDS_ATTRIBUTE => 'required|array|min:1',
			self::SHARE_IDS_ATTRIBUTE . '.*' => ['required', new IntegerIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->shareIDs = $values[self::SHARE_IDS_ATTRIBUTE];
	}

	/**
	 * @return array<int>
	 */
	public function shareIDs(): array
	{
		return $this->shareIDs;
	}
}
