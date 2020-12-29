<?php

namespace App\Actions\Album;

use AccessControl;
use App\Actions\Albums\Extensions\PublicIds;
use App\Assets\Helpers;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

class Archive extends Action
{
	use PublicIds;

	private $badChars;

	public function __construct()
	{
		parent::__construct();
		// Illicit chars
		$this->badChars = array_merge(array_map('chr', range(0, 31)), ['<', '>', ':', '"', '/', '\\', '|', '?', '*']);
	}

	/**
	 * @param string $albumID
	 *
	 * @return StreamedResponse
	 */
	public function do(array $albumIDs): StreamedResponse
	{
		$zipTitle = $this->setTitle($albumIDs);

		$response = new StreamedResponse(function () use ($albumIDs) {
			$options = new \ZipStream\Option\Archive();
			$options->setEnableZip64(Configs::get_value('zip64', '1') === '1');
			$zip = new ZipStream(null, $options);

			$dirs = [];
			foreach ($albumIDs as $albumID) {
				//! may Fail
				$album = $this->albumFactory->make($albumID);

				$dir = $album->title;
				if ($album->smart) {
					$publicAlbums = $this->getPublicAlbumsId();
					$album->setAlbumIDs($publicAlbums);
				}
				$photos_sql = $album->get_photos();

				$this->compress_album($photos_sql, $dir, $dirs, '', $album, $albumID, $zip);

				// $compress_album = function ($photos_sql, $dir, &$dirs, $parent_dir, $album, $albumID) use (&$zip, &$compress_album) {
				// 	if (!$album->is_downloadable()) {
				// 		if ($this->albumFactory->is_smart($albumID)) {
				// 			if (!AccessControl::is_logged_in()) {
				// 				return;
				// 			}
				// 		} elseif (!AccessControl::is_current_user($album->owner_id)) {
				// 			return;
				// 		}
				// 	}

				// 	$dir = str_replace($this->badChars, '', $dir) ?: 'Untitled';

				// 	// Check for duplicates
				// 	if (!empty($dirs)) {
				// 		$i = 1;
				// 		$tmp_dir = $dir;
				// 		while (in_array($tmp_dir, $dirs)) {
				// 			// Set new directory name
				// 			$tmp_dir = $dir . '-' . $i;
				// 			$i++;
				// 		}
				// 		$dir = $tmp_dir;
				// 	}
				// 	$dirs[] = $dir;

				// 	if ($parent_dir !== '') {
				// 		$dir = $parent_dir . '/' . $dir;
				// 	}

				// 	$files = [];
				// 	$photos = $photos_sql->get();
				// 	// We don't bother with additional sorting here; who
				// 	// cares in what order photos are zipped?

				// 	foreach ($photos as $photo) {
				// 		// For photos in public smart albums, skip the ones
				// 		// that are not downloadable based on their actual
				// 		// parent album.
				// 		if (
				// 			$this->albumFactory->is_smart($albumID) && !AccessControl::is_logged_in() &&
				// 			$photo->album_id !== null && !$photo->album->is_downloadable()
				// 		) {
				// 			continue;
				// 		}

				// 		$is_raw = ($photo->type == 'raw');

				// 		$prefix_url = $is_raw ? 'raw/' : 'big/';
				// 		$url = Storage::path($prefix_url . $photo->url);
				// 		// Check if readable
				// 		if (!@is_readable($url)) {
				// 			Logs::error(__METHOD__, __LINE__, 'Original photo missing: ' . $url);
				// 			continue;
				// 		}

				// 		// Get extension of image
				// 		$extension = Helpers::getExtension($url, false);

				// 		// Set title for photo
				// 		$title = str_replace($this->badChars, '', $photo->title);
				// 		if (!isset($title) || $title === '') {
				// 			$title = 'Untitled';
				// 		}

				// 		$file = $title . ($is_raw ? '' : $extension);

				// 		// Check for duplicates
				// 		if (!empty($files)) {
				// 			$i = 1;
				// 			$tmp_file = $file;
				// 			$pos = strrpos($tmp_file, '.');
				// 			while (in_array($tmp_file, $files)) {
				// 				// Set new title for photo
				// 				$tmp_file = substr_replace($file, '-' . $i, $pos, 0);
				// 				$i++;
				// 			}
				// 			$file = $tmp_file;
				// 		}
				// 		// Add to array
				// 		$files[] = $file;

				// 		// Reset the execution timeout for every iteration.
				// 		set_time_limit(ini_get('max_execution_time'));

				// 		// add a file named 'some_image.jpg' from a local file 'path/to/image.jpg'
				// 		$zip->addFileFromPath($dir . '/' . $file, $url);
				// 	} // foreach ($photos)

				// 	// Recursively compress subalbums
				// 	if (!$album->smart) {
				// 		$subDirs = [];
				// 		foreach ($album->children as $subAlbum) {
				// 			if ($this->readAccessFunctions->album($subAlbum, true) === 1) {
				// 				$subSql = Photo::set_order(Photo::where('album_id', '=', $subAlbum->id));
				// 				$compress_album($subSql, $subAlbum->title, $subDirs, $dir, $subAlbum, $subAlbum->id);
				// 			}
				// 		}
				// 	}
				// }; // $compress_album
			}

			// finish the zip stream
			$zip->finish();
		});

		// Set file type and destination
		$response->headers->set('Content-Type', 'application/x-zip');
		$disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $zipTitle . '.zip', mb_check_encoding($zipTitle, 'ASCII') ? '' : 'Album.zip');
		$response->headers->set('Content-Disposition', $disposition);

		// Disable caching
		$response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
		$response->headers->set('Pragma', 'no-cache');
		$response->headers->set('Expires', '0');

		return $response;
	}

	/**
	 * Set the Archive title.
	 */
	private function setTitle(array $albumIDs)
	{
		if (count($albumIDs) === 1) {
			return $this->makeTitle($albumIDs[0]);
		}

		return 'Albums';
	}

	/**
	 * Given an ID return the desired title (may need refactor).
	 */
	private function makeTitle(string $id)
	{
		if ($this->albumFactory->is_smart($id)) {
			return $id;
		}

		//! will fail if not found
		$album = $this->albumFactory->make($id);

		return str_replace($this->badChars, '', $album->title) ?: 'Untitled'; // 'Untitled' if empty string.
	}

	/**
	 * Album compression
	 * ! include recursive call.
	 */
	private function compress_album($photos_sql, $dir, &$dirs, $parent_dir, $album, $albumID, &$zip)
	{
		if (!$album->is_downloadable()) {
			if ($this->albumFactory->is_smart($albumID)) {
				if (!AccessControl::is_logged_in()) {
					return;
				}
			} elseif (!AccessControl::is_current_user($album->owner_id)) {
				return;
			}
		}

		$dir = str_replace($this->badChars, '', $dir) ?: 'Untitled';

		// Check for duplicates
		if (!empty($dirs)) {
			$i = 1;
			$tmp_dir = $dir;
			while (in_array($tmp_dir, $dirs)) {
				// Set new directory name
				$tmp_dir = $dir . '-' . $i;
				$i++;
			}
			$dir = $tmp_dir;
		}
		$dirs[] = $dir;

		if ($parent_dir !== '') {
			$dir = $parent_dir . '/' . $dir;
		}

		$files = [];
		$photos = $photos_sql->get();
		// We don't bother with additional sorting here; who
		// cares in what order photos are zipped?

		foreach ($photos as $photo) {
			// For photos in public smart albums, skip the ones
			// that are not downloadable based on their actual
			// parent album.
			if (
				$this->albumFactory->is_smart($albumID) && !AccessControl::is_logged_in() &&
				$photo->album_id !== null && !$photo->album->is_downloadable()
			) {
				continue;
			}

			$is_raw = ($photo->type == 'raw');

			$prefix_url = $is_raw ? 'raw/' : 'big/';
			$url = Storage::path($prefix_url . $photo->url);
			// Check if readable
			if (!@is_readable($url)) {
				Logs::error(__METHOD__, __LINE__, 'Original photo missing: ' . $url);
				continue;
			}

			// Get extension of image
			$extension = Helpers::getExtension($url, false);

			// Set title for photo
			$title = str_replace($this->badChars, '', $photo->title);
			if (!isset($title) || $title === '') {
				$title = 'Untitled';
			}

			$file = $title . ($is_raw ? '' : $extension);

			// Check for duplicates
			if (!empty($files)) {
				$i = 1;
				$tmp_file = $file;
				$pos = strrpos($tmp_file, '.');
				while (in_array($tmp_file, $files)) {
					// Set new title for photo
					$tmp_file = substr_replace($file, '-' . $i, $pos, 0);
					$i++;
				}
				$file = $tmp_file;
			}
			// Add to array
			$files[] = $file;

			// Reset the execution timeout for every iteration.
			set_time_limit(ini_get('max_execution_time'));

			// add a file named 'some_image.jpg' from a local file 'path/to/image.jpg'
			$zip->addFileFromPath($dir . '/' . $file, $url);
		} // foreach ($photos)

		// Recursively compress subalbums
		if (!$album->smart) {
			$subDirs = [];
			foreach ($album->children as $subAlbum) {
				if ($this->readAccessFunctions->album($subAlbum, true) === 1) {
					$subSql = Photo::set_order(Photo::where('album_id', '=', $subAlbum->id));
					$this->compress_album($subSql, $subAlbum->title, $subDirs, $dir, $subAlbum, $subAlbum->id, $zip);
				}
			}
		}
	}
}
