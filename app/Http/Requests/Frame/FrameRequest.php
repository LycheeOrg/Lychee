<?php

namespace App\Http\Requests\Frame;

use App\Contracts\Http\Requests\HasAlbumId;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumIdTrait;
use App\Models\Configs;
use App\Policies\AlbumPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

class FrameRequest extends BaseApiRequest implements HasAlbumId
{
	use HasAlbumIdTrait;

	private AbstractAlbum $album;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		if (!Configs::getValueAsBool('mod_frame_enabled')) {
			throw new UnauthorizedException();
		}

		$randomAlbumId = Configs::getValueAsString('random_album_id');
		$this->albumId = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE] ?? (($randomAlbumId !== '') ? $randomAlbumId : null);

		$this->album = $this->albumId === null ? null : $this->albumFactory->findAbstractAlbumOrFail($this->albumId);
	}
}