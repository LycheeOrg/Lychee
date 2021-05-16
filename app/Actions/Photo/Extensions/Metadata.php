<?php

namespace App\Actions\Photo\Extensions;

use App\Metadata\Extractor;

trait Metadata
{
	/**
	 * Central function for retrieving the metadata since this has to be called in more than one place.
	 *
	 * @param array  $file
	 * @param string $path
	 * @param string $kind
	 * @param string $extension
	 *
	 * @return array
	 */
	private function getFileMetadata($file, $path, $kind, $extension): array
	{
		/* @var  Extractor $metadataExtractor */
		$metadataExtractor = resolve(Extractor::class);

		$info = $metadataExtractor->extract($path, $kind);
		if ($kind == 'raw') {
			$info['type'] = 'raw';
		}

		// Use title of file if IPTC title missing
		if ($info['title'] === '') {
			if ($kind == 'raw') {
				$info['title'] = substr(basename($file['name']), 0, 98);
			} elseif ($info['title'] === '') {
				$info['title'] = substr(basename($file['name'], $extension), 0, 98);
			}
		}

		return $info;
	}
}
