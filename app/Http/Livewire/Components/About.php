<?php

namespace App\Http\Livewire\Components;

use App\Http\Livewire\Traits\InteractWithModal;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\InstalledVersion;
use App\Models\Configs;
use Livewire\Component;

/**
 * This defines the Login Form used in modals.
 */
class About extends Component
{
	use InteractWithModal;

	public bool $is_new_release_available = false;
	public bool $is_git_update_available = false;
	public ?string $version = null;

	/**
	 * Mount the component.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		if (!Configs::getValueAsBool('hide_version_number')){
			$this->version = resolve(InstalledVersion::class)->getVersion()->toString();
		}

		$fileVersion = resolve(FileVersion::class);
		$gitHubVersion = resolve(GitHubVersion::class);
		if (Configs::getValueAsBool('check_for_updates')) {
			$fileVersion->hydrate();
			$gitHubVersion->hydrate();
		}
		$this->is_new_release_available = !$fileVersion->isUpToDate();
		$this->is_git_update_available = !$gitHubVersion->isUpToDate();
	}

	public function render()
	{
		return view('livewire.components.about');
	}
}
