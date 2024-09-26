<?php

namespace App\Http\Requests\Search;

use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\HasTerms;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Http\Requests\Traits\HasTermsTrait;
use App\Models\Configs;
use App\Policies\AlbumPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class GetSearchRequest extends BaseApiRequest implements HasAbstractAlbum, HasTerms
{
	use HasTermsTrait;
	use HasAbstractAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if (!Auth::check() && !Configs::getValueAsBool('search_public')) {
			return false;
		}

		return Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::TERM_ATTRIBUTE => ['required', 'string'],
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$albumId = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE] ?? null;
		$this->album = $albumId === null ? null : $this->albumFactory->findAbstractAlbumOrFail($albumId);

		// Escape special characters for a LIKE query
		$this->terms = explode(' ', str_replace(
			['\\', '%', '_'],
			['\\\\', '\\%', '\\_'],
			$values[RequestAttribute::TERM_ATTRIBUTE]
		));
	}
}