<?php

namespace App\Http\Requests\Settings;

use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SetAlbumDecorationRequest extends BaseApiRequest
{
	public const ALBUM_DECORATION_ATTRIBUTE = 'album_decoration';
	public const ALBUM_DECORATION_ORIENTATION_ATTRIBUTE = 'album_decoration_orientation';

	protected string $albumDecoration;
	protected string $albumDecorationOrientation;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, Configs::class);
	}

	public function rules(): array
	{
		return [self::ALBUM_DECORATION_ATTRIBUTE => [
			'required',
			'string',
			Rule::in(['none', 'original', 'album', 'photo', 'all']),
		],
			self::ALBUM_DECORATION_ORIENTATION_ATTRIBUTE => [
				'required',
				'string',
				Rule::in(['row', 'row-reverse', 'column', 'column-reverse']),
			],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumDecoration = $values[self::ALBUM_DECORATION_ATTRIBUTE];
		$this->albumDecorationOrientation = $values[self::ALBUM_DECORATION_ORIENTATION_ATTRIBUTE];
	}

	/**
	 * @return string
	 */
	public function albumDecoration(): string
	{
		return $this->albumDecoration;
	}

	/**
	 * @return string
	 */
	public function albumDecorationOrientation(): string
	{
		return $this->albumDecorationOrientation;
	}
}
