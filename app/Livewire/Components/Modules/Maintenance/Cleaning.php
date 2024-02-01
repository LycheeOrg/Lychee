<?php

namespace App\Livewire\Components\Modules\Maintenance;

use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use function Safe\rmdir;
use function Safe\unlink;

/**
 * When an upload/extract job fail, they tend to leave files behind.
 * This provides the ability to cleans this up.
 */
class Cleaning extends Component
{
	#[Locked] public array $result = [];
	#[Locked] public string $path = '';
	private array $skip = ['.gitignore'];

	/**
	 * Mount depending of the path.
	 *
	 * @param string $path to check/clean
	 *
	 * @return void
	 */
	public function mount(string $path): void
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);

		$this->path = $path;
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.modules.maintenance.cleaning');
	}

	/**
	 * Clean the path from all files excluding $this->skip.
	 *
	 * @return void
	 */
	public function do(): void
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);

		if ($this->getNoFileFoundProperty()) {
			return;
		}

		$dirs = [];
		$files = [];
		foreach (new \DirectoryIterator($this->path) as $fileInfo) {
			if ($fileInfo->isDot()) {
				continue;
			}
			if (in_array($fileInfo->getFilename(), $this->skip, true)) {
				continue;
			}
			$this->result[] = sprintf(__('maintenance.cleaning.result'), $fileInfo->getFilename());

			if ($fileInfo->isDir()) {
				$dirs[] = $fileInfo->getRealPath();
				rmdir($fileInfo->getRealPath());
				continue;
			}
			$files[] = $fileInfo->getRealPath();
			unlink($fileInfo->getRealPath());
		}
	}

	/**
	 * Check whether there are files to be removed.
	 * If not, we will not display the module to reduce complexity.
	 *
	 * @return bool
	 */
	public function getNoFileFoundProperty(): bool
	{
		if (!is_dir($this->path)) {
			Log::warning('directory ' . $this->path . ' not found!');

			return true;
		}

		if (!(new \FilesystemIterator($this->path))->valid()) {
			return true;
		}

		$count = 0;
		foreach (new \DirectoryIterator($this->path) as $fileInfo) {
			if ($fileInfo->isDot()) {
				continue;
			}
			if (in_array($fileInfo->getFilename(), $this->skip, true)) {
				continue;
			}
			$count = 1;
		}

		return 0 === $count;
	}
}
