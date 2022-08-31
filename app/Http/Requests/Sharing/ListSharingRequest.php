<?php

namespace App\Http\Requests\Sharing;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasBaseAlbum;
use App\Http\Requests\Contracts\HasOptionalUser;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Http\Requests\Traits\HasOptionalUserTrait;
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
 * The result can be filtered by a specific album or user if the respective
 * ID is included in the request.
 *
 * Non-admin user must only query for shares of albums they own or for
 * all shares they participate in.
 * In other words, non-admin user must include at least their own user ID or
 * an album ID they own in the request.
 * Only the admin is allowed to make an unrestricted query.
 */
class ListSharingRequest extends BaseApiRequest implements HasBaseAlbum, HasOptionalUser
{
	use HasBaseAlbumTrait;
	use HasOptionalUserTrait;

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

		if ($this->user2 !== null && $this->user2->id === Auth::id()) {
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
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['sometimes', new RandomIDRule(false)],
			HasOptionalUser::USER_ID_ATTRIBUTE => ['sometimes', new IntegerIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = key_exists(HasAbstractAlbum::ALBUM_ID_ATTRIBUTE, $values) ?
			$this->albumFactory->findBaseAlbumOrFail($values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE]) :
			null;
		$this->user2 = key_exists(HasOptionalUser::USER_ID_ATTRIBUTE, $values) ?
			User::query()->find($values[HasOptionalUser::USER_ID_ATTRIBUTE]) :
			null;
	}
}
