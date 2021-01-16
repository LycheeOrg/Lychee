<?php

namespace App\Actions\Import;

use App\Models\Configs;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FromServer
{
	/** @var Exec */
	private $exec;

	public function __construct(Exec $exec)
	{
		$this->exec = $exec;
	}

	public function do($validated)
	{
		if (isset($validated['delete_imported'])) {
			$this->exec->delete_imported = $validated['delete_imported'] === '1';
		} else {
			$this->exec->delete_imported = Configs::get_value('delete_imported', '0') === '1';
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
		$response->setCallback(function () use ($validated) {
			// Surround the response in '"' characters to make it a valid
			// JSON string.
			echo '"';
			$this->exec->do($validated['path'], $validated['albumID']);
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
