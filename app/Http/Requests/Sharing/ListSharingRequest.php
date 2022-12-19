<?php

namespace App\Http\Requests\Sharing;

use App\Contracts\Http\Requests\HasBaseAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\UserPolicy;
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
class ListSharingRequest extends BaseApiRequest implements HasBaseAlbum
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
	 */
	public function authorize(): bool
	{
		if (Gate::check(UserPolicy::IS_ADMIN)) {
			return true;
		}

		if (!Gate::check(UserPolicy::CAN_UPLOAD, User::class)) {
			return false;
		}

		if ($this->album !== null && Gate::check(AlbumPolicy::IS_OWNER, $this->album)) {
			return true;
		}

		if (
			($this->owner !== null && $this->owner->id === Auth::id()) ||
			($this->participant !== null && $this->participant->id === Auth::id())
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
			$this->albumFactory->findBaseAlbumOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]) :
			null;
		$this->owner = key_exists(self::OWNER_ID_ATTRIBUTE, $values) ?
			User::query()->findOrFail($values[self::OWNER_ID_ATTRIBUTE]) :
			null;
		$this->participant = key_exists(self::PARTICIPANT_ID_ATTRIBUTE, $values) ?
			User::query()->findOrFail($values[self::PARTICIPANT_ID_ATTRIBUTE]) :
			null;
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
