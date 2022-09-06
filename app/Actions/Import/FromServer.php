<?php

namespace App\Actions\Import;

use App\Actions\Photo\Strategies\ImportMode;
use App\Models\Album;
use function Safe\ini_get;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FromServer
{
	/**
	 * @param string[]   $paths      the server path to import from
	 * @param Album|null $album      the album to import into
	 * @param ImportMode $importMode the import mode
	 *
	 * @return StreamedResponse
	 */
	public function do(array $paths, ?Album $album, ImportMode $importMode): StreamedResponse
	{
		$exec = new Exec($importMode, false, $this->determineMemLimit());

		$response = new StreamedResponse();
		$response->headers->set('Content-Type', 'application/json');
		$response->headers->set('Cache-Control', 'no-store');
		// nginx-specific voodoo, as per https://symfony.com/doc/current/components/http_foundation.html#streaming-a-response
		$response->headers->set('X-Accel-Buffering', 'no');
		$response->setCallback(function () use ($paths, $album, $exec) {
			// Surround the response by `[]` to make it a valid JSON array.
			echo '[';
			foreach ($paths as $path) {
				$exec->do($path, $album);
			}
			echo ']';
		});

		return $response;
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
