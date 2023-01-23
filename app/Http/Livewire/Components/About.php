<?php

namespace App\Http\Livewire\Components;

use App\Http\Livewire\Traits\InteractWithModal;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\InstalledVersion;
use App\Models\Configs;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * This defines the about modal that can be found in the Left Menu.
 */
class About extends Component
{
	use InteractWithModal;

	public bool $is_new_release_available = false;
	public bool $is_git_update_available = false;
	public ?string $version = null;

	/**
	 * Mount the component. We set the attributes here.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		if (!Configs::getValueAsBool('hide_version_number')) {
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

	/**
	 * Renders the About component.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.components.about');
	}
}
