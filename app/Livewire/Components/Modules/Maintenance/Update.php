<?php

namespace App\Livewire\Components\Modules\Maintenance;

use App\Actions\Diagnostics\Pipes\Infos\VersionInfo;
use App\Enum\VersionChannelType;
use App\Metadata\Versions\GitHubVersion;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * This modules takes care of applying updates
 * and checkimg if new versions are available.
 */
class Update extends Component
{
	#[Locked] public array $result = [];
	#[Locked] public string $channelName;
	#[Locked] public string $info;
	#[Locked] public string $extra = '';
	#[Locked] public bool $can_check = true;
	#[Locked] public bool $can_update = false;
	private VersionInfo $versionInfo;

	/**
	 * Load version info.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		$this->versionInfo = resolve(VersionInfo::class);
	}

	/**
	 * Mount the component for the first time and load the data.
	 */
	public function mount(): void
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);

		$this->getData();
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.modules.maintenance.update');
	}

	/**
	 * Checking if any updates are available.
	 *
	 * @return void
	 */
	public function check(): void
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);
		$this->can_check = false;

		$gitHubFunctions = resolve(GitHubVersion::class);
		$gitHubFunctions->hydrate(true, false);
		$this->extra = $gitHubFunctions->getBehindTest();
		$this->can_update = !$gitHubFunctions->isUpToDate() || !$this->versionInfo->fileVersion->isUpToDate();
	}

	/**
	 * Fetching the data of the installation.
	 *
	 * @return void
	 */
	private function getData(): void
	{
		/** @var VersionChannelType $channelName */
		$channelName = $this->versionInfo->getChannelName();
		$this->info = $this->versionInfo->fileVersion->getVersion()->toString();

		if ($channelName !== VersionChannelType::RELEASE) {
			if ($this->versionInfo->gitHubFunctions->localHead !== null) {
				$branch = $this->versionInfo->gitHubFunctions->localBranch ?? '??';
				$commit = $this->versionInfo->gitHubFunctions->localHead ?? '??';
				$this->info = sprintf('%s (%s)', $branch, $commit);
				$this->extra = $this->versionInfo->gitHubFunctions->getBehindTest();
			} else {
				// @codeCoverageIgnoreStart
				$this->info = 'No git data found.';
				// @codeCoverageIgnoreEnd
			}
		}

		$this->channelName = $channelName->value;
	}
}
