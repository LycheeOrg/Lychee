<?php

namespace App\Http\Resources;

use App\Http\Resources\Models\UserResource;
use App\Http\Resources\Rights\GlobalRightsResource;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Models\Configs;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class InitResource extends JsonResource
{
	public function __construct()
	{
		// Laravel applies a shortcut when this value === null but not when it is something else.
		parent::__construct('must_not_be_null');
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array
	 */
	public function toArray($request): array
	{
		$fileVersion = resolve(FileVersion::class);
		$gitHubVersion = resolve(GitHubVersion::class);

		if (Configs::getValueAsBool('check_for_updates')) {
			$fileVersion->hydrate();
			$gitHubVersion->hydrate();
		}

		// we also return the locale
		$locale = include base_path('lang/' . app()->getLocale() . '/lychee.php');

		return [
			'user' => $this->when(Auth::check(), UserResource::make(Auth::user()), null),
			'rights' => GlobalRightsResource::make(),
			'config' => ConfigurationResource::make(),
			'update_json' => !$fileVersion->isUpToDate(),
			'update_available' => !$gitHubVersion->isUpToDate(),
			'locale' => $locale,
		];
	}
}
