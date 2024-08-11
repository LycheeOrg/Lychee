<?php

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbumIds;
use App\Contracts\Http\Requests\HasBaseAlbum;
use App\Contracts\Http\Requests\HasUserId;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumIdsTrait;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Http\Requests\Traits\HasUserIdTrait;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use App\Rules\IntegerIDRule;
use Illuminate\Support\Facades\Gate;

class TransferAlbumRequest extends BaseApiRequest implements HasAlbumIds, HasBaseAlbum, HasUserId
{
	use HasAlbumIdsTrait;
	use HasBaseAlbumTrait;
	use HasUserIdTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_DELETE_ID, [AbstractAlbum::class, $this->albumIds()]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
			RequestAttribute::USER_ID_ATTRIBUTE => ['required', new IntegerIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->userId = $values[RequestAttribute::USER_ID_ATTRIBUTE];
		// As we are going to delete the albums anyway, we don't load the
		// models for efficiency reasons.
		// Instead, we use mass deletion via low-level SQL queries later.
		$this->albumIds = [$values[RequestAttribute::ALBUM_ID_ATTRIBUTE]];
		$this->album = $this->albumFactory->findBaseAlbumOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);
	}
}
