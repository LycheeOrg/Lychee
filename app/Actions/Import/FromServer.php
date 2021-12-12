<?php

namespace App\Actions\Import;

use App\Actions\Photo\Strategies\ImportMode;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FromServer
{
	private Exec $exec;

	public function __construct(Exec $exec)
	{
		$this->exec = $exec;
	}

	/**
	 * @param string      $path       the server path to import from
	 * @param string|null $albumID    the ID of the album to import into
	 * @param ImportMode  $importMode the import mode
	 *
	 * @return StreamedResponse
	 */
	public function do(string $path, ?string $albumID, ImportMode $importMode): StreamedResponse
	{
		$this->exec->importMode = $importMode;
		$this->exec->memLimit = $this->determineMemLimit();

		$response = new StreamedResponse();
		$response->setCallback(function () use ($path, $albumID) {
			// Surround the response in '"' characters to make it a valid
			// JSON string.
			echo '"';
			$this->exec->do($path, $albumID);
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

	/**
	 * Determines the memory limit set by PHP configuration.
	 *
	 * The option `memory_limit` may have a K/M/etc. suffix which makes
	 * querying it more complicated...
	 *
	 * @return int the memory limit in bytes
	 */
	protected function determineMemLimit(): int
	{
		$value = 0;
		$suffix = '';
		if (sscanf(ini_get('memory_limit'), '%d%c', $value, $suffix) === 2) {
			switch (strtolower($suffix)) {
				// @codeCoverageIgnoreStart
				case 'k':
					$value *= 1024;
					break;
				case 'm':
					$value *= 1024 * 1024;
					break;
				case 'g':
					$value *= 1024 * 1024 * 1024;
					break;
				case 't':
					$value *= 1024 * 1024 * 1024 * 1024;
					break;
				default:
					break;
				// @codeCoverageIgnoreEnd
			}
		}

		// We set the warning threshold at 90% of the limit.
		return intval($value * 0.9);
	}
}
