<?php

namespace App\Actions\Photo;

use AccessControl;
use App\Actions\Photo\Extensions\Constants;
use App\Assets\Helpers;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

class Archive
{
	use Constants;

	private $badChars;

	public function __construct()
	{
		// Illicit chars
		$this->badChars = array_merge(array_map('chr', range(0, 31)), ['<', '>', ':', '"', '/', '\\', '|', '?', '*']);
	}

	/**
	 * @param string $albumID
	 *
	 * @return StreamedResponse
	 */
	public function do(array $photoIDs, $kind_request)
	{
		if (count($photoIDs) === 1) {
			$response = $this->file($photoIDs[0], $kind_request);
		} else {
			$response = $this->zip($photoIDs, $kind_request);
		}

		return $response;
	}

	public function file($photoID, $kind_request)
	{
		$ret = $this->extract_names($photoID, $kind_request);
		if ($ret === null) {
			return abort(404);
		}

		list($title, $kind, $extension, $url) = $ret;

		// Set title for photo
		$file = $title . $kind . $extension;

		$response = new BinaryFileResponse($url);

		return $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file);
	}

	public function zip(array $photoIDs, string $kind_request)
	{
		$response = new StreamedResponse(function () use ($kind_request, $photoIDs) {
			$options = new \ZipStream\Option\Archive();
			$options->setEnableZip64(Configs::get_value('zip64', '1') === '1');
			$zip = new ZipStream(null, $options);

			$files = [];
			foreach ($photoIDs as $photoID) {
				$ret = $this->extract_names($photoID, $kind_request);
				if ($ret == null) {
					return abort(404);
				}

				list($title, $kind, $extension, $url) = $ret;

				// Set title for photo
				$file = $title . $kind . $extension;
				// Check for duplicates
				if (!empty($files)) {
					$i = 1;
					$tmp_file = $file;
					while (in_array($tmp_file, $files)) {
						// Set new title for photo
						$tmp_file = $title . $kind . '-' . $i . $extension;
						$i++;
					}
					$file = $tmp_file;
				}
				// Add to array
				$files[] = $file;

				// Reset the execution timeout for every iteration.
				set_time_limit(ini_get('max_execution_time'));

				$zip->addFileFromPath($file, $url);
			} // foreach ($photoIDs)

			// finish the zip stream
			$zip->finish();
		});

		// Set file type and destination
		$response->headers->set('Content-Type', 'application/x-zip');
		$disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, 'Photos.zip');
		$response->headers->set('Content-Disposition', $disposition);

		return $response;
	}

	/**
	 * extract the file names.
	 *
	 * @param $photoID
	 * @param $request
	 *
	 * @return array|null
	 */
	public function extract_names($photoID, $kind_input)
	{
		$photo = Photo::with('album')->findOrFail($photoID);

		if (!AccessControl::is_current_user($photo->owner_id)) {
			if ($photo->album_id !== null && !$photo->album->is_downloadable()) {
				return null;
			} elseif (Configs::get_value('downloadable', '0') === '0') {
				return null;
			}
		}

		$title = str_replace($this->badChars, '', $photo->title) ?: 'Untitled';

		$prefix_path = $photo->type == 'raw' ? 'raw/' : 'big/';

		// determine the file based on given size
		switch ($kind_input) {
			case 'FULL':
				$path = $prefix_path . $photo->url;
				$kind = '';
				break;
			case 'LIVEPHOTOVIDEO':
				$path = $prefix_path . $photo->livePhotoUrl;
				$kind = '';
				break;
			case 'MEDIUM2X':
				if ($this->isVideo($photo) === false) {
					$fileName = $photo->url;
				} else {
					$fileName = $photo->thumbUrl;
				}
				$path = 'medium/' . Helpers::ex2x($fileName);
				$kind = '-' . $photo->medium2x;
				break;
			case 'MEDIUM':
				if ($this->isVideo($photo) === false) {
					$path = 'medium/' . $photo->url;
				} else {
					$path = 'medium/' . $photo->thumbUrl;
				}
				$kind = '-' . $photo->medium;
				break;
			case 'SMALL2X':
				if ($this->isVideo($photo) === false) {
					$fileName = $photo->url;
				} else {
					$fileName = $photo->thumbUrl;
				}
				$path = 'small/' . Helpers::ex2x($fileName);
				$kind = '-' . $photo->small2x;
				break;
			case 'SMALL':
				if ($this->isVideo($photo) === false) {
					$path = 'small/' . $photo->url;
				} else {
					$path = 'small/' . $photo->thumbUrl;
				}
				$kind = '-' . $photo->small;
				break;
			case 'THUMB2X':
				$path = 'thumb/' . Helpers::ex2x($photo->thumbUrl);
				$kind = '-400x400';
				break;
			case 'THUMB':
				$path = 'thumb/' . $photo->thumbUrl;
				$kind = '-200x200';
				break;
			default:
				Logs::error(__METHOD__, __LINE__, 'Invalid kind ' . $kind_input);

				return null;
		}

		$fullpath = Storage::path($path);

		// Check the file actually exists
		if (!Storage::exists($path)) {
			Logs::error(__METHOD__, __LINE__, 'File is missing: ' . $fullpath . ' (' . $title . ')');

			return null;
		}

		// Get extension of image
		$extension = '';
		if ($photo->type != 'raw') {
			$extension = Helpers::getExtension($fullpath, false);
		}

		return [$title, $kind, $extension, $fullpath];
	}
}
