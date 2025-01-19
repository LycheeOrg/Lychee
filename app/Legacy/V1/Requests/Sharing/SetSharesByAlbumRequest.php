<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Sharing;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasBaseAlbum;
use App\Legacy\V1\Contracts\Http\Requests\HasUserIDs;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\HasBaseAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasUserIDsTrait;
use App\Policies\AlbumPolicy;
use App\Rules\IntegerIDRule;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

/**
 * Represents a request for setting the shares of a specific album.
 *
 * Only the owner (or the admin) of the album can set the shares.
 *
 * @codeCoverageIgnore Legacy stuff we don't care.
 */
final class SetSharesByAlbumRequest extends BaseApiRequest implements HasBaseAlbum, HasUserIDs
{
	use HasBaseAlbumTrait;
	use HasUserIDsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::USER_IDS_ATTRIBUTE => 'present|array',
			RequestAttribute::USER_IDS_ATTRIBUTE . '.*' => ['required', new IntegerIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findBaseAlbumOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);
		$this->userIDs = $values[RequestAttribute::USER_IDS_ATTRIBUTE];
	}
}