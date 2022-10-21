<?php

namespace App\Image;

use App\Exceptions\MediaFileOperationException;
use Safe\Exceptions\PcreException;
use function Safe\fclose;
use function Safe\fopen;
use function Safe\parse_url;
use function Safe\preg_match;

/**
 * Represents a temporary local file which has been downloaded.
 *
 * It provides the server-side MIME type in case the MIME type cannot be
 * inferred from the temporary, local copy of the file.
 */
class DownloadedFile extends TemporaryLocalFile
{
	protected ?string $originalMimeType = null;

	/**
	 * @param string $url
	 *
	 * @throws MediaFileOperationException
	 */
	public function __construct(string $url)
	{
		try {
			/** @var string $path -- because we provide directly PHP_URL_PATH */
			$path = parse_url($url, PHP_URL_PATH);
			$basename = pathinfo($path, PATHINFO_FILENAME);
			$extension = '.' . pathinfo($path, PATHINFO_EXTENSION);
			parent::__construct($extension, $basename);

			$downloadStream = fopen($url, 'rb');
			$downloadStreamData = stream_get_meta_data($downloadStream);
			// Find the server-side MIME type; the HTTP headers are part of
			// the protocol-specific meta-data of the stream handler
			foreach ($downloadStreamData['wrapper_data'] as $http_header) {
				$matches = [];
				preg_match(
					'#^Content-Type: ([-a-z]+/[-a-z]+)#i',
					$http_header,
					$matches,
					PREG_UNMATCHED_AS_NULL
				);
				if (count($matches) === 2 && $matches[1]) {
					$this->originalMimeType = $matches[1];
					break;
				}
			}
			$this->write($downloadStream);
			fclose($downloadStream);
		} catch (\ErrorException|PcreException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * Returns the MIME type of the file.
	 *
	 * @param bool $fallbackToClientMimeType flag to use the provided MIME
	 *                                       type by client-side, if the
	 *                                       internal PHP mechanism detects
	 *                                       "application/octet-stream"
	 *
	 * @return string the MIME type
	 *
	 * @throws MediaFileOperationException
	 */
	public function getMimeType(bool $fallbackToClientMimeType = true): string
	{
		parent::getMimeType();
		if ($this->cachedMimeType === 'application/octet-stream' && $fallbackToClientMimeType) {
			return $this->originalMimeType;
		} else {
			return $this->cachedMimeType;
		}
	}
}
