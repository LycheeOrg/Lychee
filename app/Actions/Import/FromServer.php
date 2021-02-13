<?php

namespace App\Actions\Import;

use App\Models\Configs;
use Illuminate\Session\Store;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FromServer
{
	/** @var Exec */
	private $exec;

	public function __construct(Exec $exec)
	{
		$this->exec = $exec;
	}

	public function do($validated, Store $store)
	{
		if (isset($validated['delete_imported'])) {
			$this->exec->delete_imported = ($validated['delete_imported'] === '1');
		} else {
			$this->exec->delete_imported = (Configs::get_value('delete_imported', '0') === '1');
		}
		if (isset($validated['import_via_symlink'])) {
			$this->exec->import_via_symlink = ($validated['import_via_symlink'] === '1');
		} else {
			$this->exec->import_via_symlink = (Configs::get_value('import_via_symlink', '0') === '1');
		}
		if (isset($validated['skip_duplicates'])) {
			$this->exec->skip_duplicates = ($validated['skip_duplicates'] === '1');
		} else {
			$this->exec->skip_duplicates = (Configs::get_value('skip_duplicates', '0') === '1');
		}
		if (isset($validated['resync_metadata'])) {
			$this->exec->resync_metadata = ($validated['resync_metadata'] === '1');
		} else {
			// do we need a default?
//			$this->exec->resync_metadata = (Configs::get_value('resync_metadata', '0') === '1');
			$this->exec->resync_metadata = false;
		}

		// memory_limit can have a K/M/etc suffix which makes querying it
		// more complicated...
		if (sscanf(ini_get('memory_limit'), '%d%c', $this->exec->memLimit, $memExt) === 2) {
			switch (strtolower($memExt)) {
					// @codeCoverageIgnoreStart
				case 'k':
					$this->exec->memLimit *= 1024;
					break;
				case 'm':
					$this->exec->memLimit *= 1024 * 1024;
					break;
				case 'g':
					$this->exec->memLimit *= 1024 * 1024 * 1024;
					break;
				case 't':
					$this->exec->memLimit *= 1024 * 1024 * 1024 * 1024;
					break;
				default:
					break;
					// @codeCoverageIgnoreEnd
			}
		}
		// We set the warning threshold at 90% of the limit.
		$this->exec->memLimit = intval($this->exec->memLimit * 0.9);

		$response = new StreamedResponse();
		$response->setCallback(function () use ($validated, $store) {
			// Surround the response in '"' characters to make it a valid
			// JSON string.
			echo '"';
			$this->exec->do($validated['path'], $validated['albumID'], $store);
			echo '"';
		});
		// nginx-specific voodoo, as per https://symfony.com/doc/current/components/http_foundation.html#streaming-a-response
		$response->headers->set('X-Accel-Buffering', 'no');

		return $response;
	}

	public function enableCLIStatus()
	{
		$this->exec->statusCLIFormatting = true;
	}

	public function disableMemCheck()
	{
		$this->exec->memCheck = false;
	}
}
