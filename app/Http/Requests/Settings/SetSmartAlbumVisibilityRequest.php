<?php

namespace App\Http\Requests\Settings;

use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\HasIsPublic;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\SmartAlbumType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Http\Requests\Traits\HasIsPublicTrait;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\In;

class SetSmartAlbumVisibilityRequest extends BaseApiRequest implements HasAbstractAlbum, HasIsPublic
{
	use HasAbstractAlbumTrait;
	use HasIsPublicTrait;

	protected bool $is_public;

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
