<?php

namespace App\Http\Requests\Settings;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\AlbumDecorationOrientation;
use App\Enum\AlbumDecorationType;
use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class SetAlbumDecorationRequest extends BaseApiRequest
{
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
		return [
			RequestAttribute::ALBUM_DECORATION_ATTRIBUTE => ['required', new Enum(AlbumDecorationType::class)],
			RequestAttribute::ALBUM_DECORATION_ORIENTATION_ATTRIBUTE => ['required', new Enum(AlbumDecorationOrientation::class)],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumDecoration = $values[RequestAttribute::ALBUM_DECORATION_ATTRIBUTE];
		$this->albumDecorationOrientation = $values[RequestAttribute::ALBUM_DECORATION_ORIENTATION_ATTRIBUTE];
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
