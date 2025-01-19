<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Sharing;

use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\UnauthenticatedException;
use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasBaseAlbum;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\HasBaseAlbumTrait;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Rules\IntegerIDRule;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Represents a request for listing shares.
 *
 * The result can be filtered by
 *  - a specific album via `albumID`
 *  - a specific user with whom the something is shared via `participantID`, or
 *  - a specific user who owns the albums which are shared via `ownerID`
 * if the respective ID is included in the request.
 *
 * Non-admin user must only query for shares of albums they own or for
 * all shares they participate in.
 * In other words, non-admin user must include at least their own user ID as
 * user ID or owner ID or an album ID they own in the request.
 * Only the admin is allowed to make an unrestricted query.
 */
final class ListSharingRequest extends BaseApiRequest implements HasBaseAlbum
{
	use HasBaseAlbumTrait;
	public const OWNER_ID_ATTRIBUTE = 'ownerID';
	public const PARTICIPANT_ID_ATTRIBUTE = 'participantID';

	/**
	 * @var User|null
	 */
	protected ?User $owner;

	/**
	 * @var User|null
	 */
	protected ?User $participant;

	/**
	 * {@inheritDoc}
	 *
	 * @codeCoverageIgnore Legacy stuff we don't care.
	 */
	public function authorize(): bool
	{
		/** @var User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();

		if (!Gate::check(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, $this->album])) {
			return false;
		}

		if ($user->may_administrate === true) {
			return true;
		}

		if (
			($this->owner?->id === $user->id) ||
			($this->participant?->id === $user->id)
		) {
			return true;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['sometimes', new RandomIDRule(false)],
			self::OWNER_ID_ATTRIBUTE => ['sometimes', new IntegerIDRule(false)],
			self::PARTICIPANT_ID_ATTRIBUTE => ['sometimes', new IntegerIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = key_exists(RequestAttribute::ALBUM_ID_ATTRIBUTE, $values) ?
			// @codeCoverageIgnoreStart
			$this->albumFactory->findBaseAlbumOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]) :
			// @codeCoverageIgnoreEnd
			null;

		$this->owner = null;
		$this->participant = null;
		if (key_exists(self::OWNER_ID_ATTRIBUTE, $values)) {
			// @codeCoverageIgnoreStart
			/** @var int $ownerID */
			$ownerID = $values[self::OWNER_ID_ATTRIBUTE];
			$this->owner = User::query()->findOrFail($ownerID);
			// @codeCoverageIgnoreEnd
		}
		if (key_exists(self::PARTICIPANT_ID_ATTRIBUTE, $values)) {
			// @codeCoverageIgnoreStart
			/** @var int $participantID */
			$participantID = $values[self::PARTICIPANT_ID_ATTRIBUTE];
			$this->participant = User::query()->findOrFail($participantID);
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Returns the optional album owner to which the list of shares shall be
	 * restricted.
	 *
	 * @return User|null
	 */
	public function owner(): ?User
	{
		return $this->owner;
	}

	/**
	 * Returns the optional share participant to which the list of shares
	 * shall be restricted.
	 *
	 * @return User|null
	 */
	public function participant(): ?User
	{
		return $this->participant;
	}
}
