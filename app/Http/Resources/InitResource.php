<?php

namespace App\Http\Resources;

use App\Http\Resources\Rights\GlobalRightsResource;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;

class InitResource extends JsonResource
{
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

		return [
			'user' => $this->when(Auth::check(), UserResource::make(Auth::user()), null),
			'rights' => GlobalRightsResource::make(),
			'config' => ConfigurationResource::make(),
			'update_json' => !$fileVersion->isUpToDate(),
			'update_available' => !$gitHubVersion->isUpToDate(),
		];
	}
}
