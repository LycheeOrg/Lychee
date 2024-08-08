<?php

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Requests\Maintenance\MaintenanceRequest;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use function Safe\rmdir;
use function Safe\unlink;

/**
 * When an upload/extract job fail, they tend to leave files behind.
 * This provides the ability to cleans this up.
 */
class Cleaning extends Component
{
	/** @var string[] */
	private array $skip = ['.gitignore'];

	/**
	 * Clean the path from all files excluding $this->skip.
	 *
	 * @return string[]
	 */
	public function do(MaintenanceRequest $request): array
	{
		if ($this->check($request)) {
			return [];
		}

		$results = [];
		$dirs = [];
		$files = [];
		foreach (new \DirectoryIterator($request->path()) as $fileInfo) {
			if ($fileInfo->isDot()) {
				continue;
			}
			if (in_array($fileInfo->getFilename(), $this->skip, true)) {
				continue;
			}
			$results[] = sprintf(__('maintenance.cleaning.result'), $fileInfo->getFilename());

			if ($fileInfo->isDir()) {
				$dirs[] = $fileInfo->getRealPath();
				rmdir($fileInfo->getRealPath());
				continue;
			}
			$files[] = $fileInfo->getRealPath();
			unlink($fileInfo->getRealPath());
		}

		return $results;
	}

	/**
	 * Check whether there are files to be removed.
	 * If not, we will not display the module to reduce complexity.
	 *
	 * @return bool
	 */
	public function check(MaintenanceRequest $request): bool
	{
		if (!is_dir($request->path())) {
			Log::warning('directory ' . $request->path() . ' not found!');

			return true;
		}

		if (!(new \FilesystemIterator($request->path()))->valid()) {
			return true;
		}

		$files_found = false;
		foreach (new \DirectoryIterator($request->path()) as $fileInfo) {
			if ($fileInfo->isDot()) {
				continue;
			}
			if (in_array($fileInfo->getFilename(), $this->skip, true)) {
				continue;
			}
			$files_found = true;
		}

		return $files_found === false;
	}
}
