<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use App\Rules\AlbumIDRule;
use Illuminate\Support\Facades\Gate;

class FullTreeUpdateRequest extends BaseApiRequest
{
	/**
	 * @var array<int,array{id:string,_lft:int,_rgt:int}>
	 */
	private array $albums;

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
			'albums' => 'required|array|min:1',
			'albums.*' => 'required|array',
			'albums.*.id' => ['required', new AlbumIDRule(false)],
			'albums.*._lft' => 'required|integer|min:1',
			'albums.*._rgt' => 'required|integer|min:1',
		];
	}

	protected function processValidatedValues(
		array $values,
		array $files,
	): void {
		$this->albums = $values['albums'];
	}

	/**
	 * @return array<int,array{id:string,_lft:int,_rgt:int}>
	 */
	public function albums(): array
	{
		return $this->albums;
	}
}
