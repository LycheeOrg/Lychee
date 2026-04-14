<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Contracts\Models\AbstractAlbum;
use App\DTO\ChunkSlice;
use App\Enum\DownloadVariantType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Handler;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Image\Files\BaseMediaFile;
use App\Image\Files\FlysystemFile;
use App\Models\Album;
use App\Models\Photo;
use App\Models\TagAlbum;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use App\Repositories\ConfigManager;
use App\SmartAlbums\BaseSmartAlbum;
use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Safe\Exceptions\InfoException;
use function Safe\ini_get;
use function Safe\set_time_limit;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\Exception\FileNotFoundException;
use ZipStream\Exception\FileNotReadableException;
use ZipStream\ZipStream;

abstract class BaseArchive
{
	public const BAD_CHARS = [
		"\x00", "\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07",
		"\x08", "\x09", "\x0a", "\x0b", "\x0c", "\x0d", "\x0e", "\x0f",
		"\x10", "\x11", "\x12", "\x13", "\x14", "\x15", "\x16", "\x17",
		"\x18", "\x19", "\x1a", "\x1b", "\x1c", "\x1d", "\x1e", "\x1f",
		'<', '>', ':', '"', '/', '\\', '|', '?', '*',
	];

	protected int $deflate_level = -1;

	/**
	 * Resolve which version of the archive to use.
	 *
	 * @return BaseArchive
	 */
	public static function resolve(): self
	{
		if (InstalledVersions::satisfies(new VersionParser(), 'maennchen/zipstream-php', '^3.1')) {
			return new Archive64();
		}
		// @codeCoverageIgnoreStart
		if (InstalledVersions::satisfies(new VersionParser(), 'maennchen/zipstream-php', '^2.1')) {
			return new Archive32();
		}

		throw new LycheeLogicException('Unsupported version of maennchen/zipstream-php');
		// @codeCoverageIgnoreEnd
	}

	/**
	 * @param Collection<int,AbstractAlbum> $albums
	 * @param DownloadVariantType|null      $variant the desired size variant (defaults to ORIGINAL)
	 * @param ChunkSlice|null               $slice   optional chunk slice for chunked downloads
	 *
	 * @return StreamedResponse
	 *
	 * @throws FrameworkException
	 * @throws ConfigurationKeyMissingException
	 */
	public function do(Collection $albums, ?DownloadVariantType $variant = null, ?ChunkSlice $slice = null): StreamedResponse
	{
		// Issue #1950: Setting Model::shouldBeStrict(); in /app/Providers/AppServiceProvider.php breaks recursive album download.
		//
		// From my understanding it is because when we query an album with it's relations (photos & children),
		// the relations of the children are not populated.
		// As a result, when we try to query the picture list of those, it breaks.
		// In that specific case, it is better to simply disable Model::shouldBeStrict() and eat the recursive SQL queries:
		// for this specific case we must allow lazy loading.
		Model::shouldBeStrict(false);

		$config_manager = app(ConfigManager::class);
		$this->deflate_level = $config_manager->getValueAsInt('zip_deflate_level');

		$effective_variant = $variant ?? DownloadVariantType::ORIGINAL;

		if ($slice !== null) {
			return $this->doSliced($albums, $effective_variant, $slice);
		}

		$response_generator = function () use ($albums, $effective_variant): void {
			$zip = $this->createZip();

			$used_dir_names = [];
			foreach ($albums as $album) {
				$this->compressAlbum($album, $used_dir_names, null, $zip, $effective_variant);
			}

			// finish the zip stream
			$zip->finish();
		};

		try {
			$response = new StreamedResponse($response_generator);
			// Set file type and destination
			$zip_title = self::createZipTitle($albums);
			$disposition = HeaderUtils::makeDisposition(
				HeaderUtils::DISPOSITION_ATTACHMENT,
				$zip_title . '.zip',
				mb_check_encoding($zip_title, 'ASCII') ? '' : 'Album.zip'
			);
			$response->headers->set('Content-Type', 'application/x-zip');
			$response->headers->set('Content-Disposition', $disposition);

			// Disable caching
			$response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
			$response->headers->set('Pragma', 'no-cache');
			$response->headers->set('Expires', '0');
			// @codeCoverageIgnoreStart
		} catch (\InvalidArgumentException $e) {
			throw new FrameworkException('Symfony\'s response component', $e);
		}
		// @codeCoverageIgnoreEnd

		return $response;
	}

	/**
	 * Produces a chunked (partial) ZIP archive containing only the photos in the given slice.
	 *
	 * @param Collection<int,AbstractAlbum> $albums
	 * @param DownloadVariantType           $variant
	 * @param ChunkSlice                    $slice
	 *
	 * @return StreamedResponse
	 *
	 * @throws FrameworkException
	 */
	private function doSliced(Collection $albums, DownloadVariantType $variant, ChunkSlice $slice): StreamedResponse
	{
		// First pass: build the complete ordered list of [id => zip_path] for all photos.
		$all_filenames = $this->gatherAllFilenames($albums, $variant);

		// Extract only the photos in the requested slice.
		$sliced = array_slice($all_filenames, $slice->offset, $slice->limit);

		// Build a lookup map: photo_id => zip_path.
		$included_map = [];
		foreach ($sliced as $item) {
			$included_map[$item['id']] = $item['zip_path'];
		}

		$response_generator = function () use ($albums, $variant, $included_map): void {
			$zip = $this->createZip();
			$used_dir_names = [];
			foreach ($albums as $album) {
				$this->compressAlbumSliced($album, $used_dir_names, null, $zip, $variant, $included_map);
			}
			$zip->finish();
		};

		try {
			$zip_title = self::createZipTitle($albums);
			$part_filename = $zip_title . '.part' . $slice->chunk . '.zip';
			$fallback = 'Album.part' . $slice->chunk . '.zip';
			$disposition = HeaderUtils::makeDisposition(
				HeaderUtils::DISPOSITION_ATTACHMENT,
				$part_filename,
				mb_check_encoding($part_filename, 'ASCII') ? '' : $fallback
			);

			$response = new StreamedResponse($response_generator);
			$response->headers->set('Content-Type', 'application/x-zip');
			$response->headers->set('Content-Disposition', $disposition);
			$response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
			$response->headers->set('Pragma', 'no-cache');
			$response->headers->set('Expires', '0');
			// @codeCoverageIgnoreStart
		} catch (\InvalidArgumentException $e) {
			throw new FrameworkException('Symfony\'s response component', $e);
		}
		// @codeCoverageIgnoreEnd

		return $response;
	}

	/**
	 * @return ZipStream
	 *
	 * @throws ConfigurationKeyMissingException
	 *
	 * @codeCoverageIgnore
	 */
	abstract protected function createZip(): ZipStream;

	/**
	 * Create the title of the ZIP archive.
	 *
	 * @param Collection<int,AbstractAlbum> $albums
	 *
	 * @return string
	 */
	private static function createZipTitle(Collection $albums): string
	{
		return $albums->hasSole() ?
			self::createValidTitle($albums->first()->get_title()) :
			'Albums';
	}

	/**
	 * Creates a title which only contains valid characters.
	 *
	 * Removes all invalid characters from the given title.
	 * If the title happens to become the empty string after removing all
	 * illegal characters, the fixed string 'Untitled'  is returned.
	 *
	 * @param string $title the title with possibly invalid characters
	 *
	 * @return string the title without any invalid characters
	 */
	private static function createValidTitle(string $title): string
	{
		$valid_title = str_replace(self::BAD_CHARS, '', $title);
		$valid_title = pathinfo($valid_title, PATHINFO_FILENAME);

		return $valid_title !== '' ? $valid_title : 'Untitled';
	}

	/**
	 * Returns a unique string.
	 *
	 * Returns the input value `$str` possibly augmented by a counter
	 * suffix `-<n>` such that the returned value is not contained in the
	 * input array `$used`.
	 * The method adds the return value to `$used`.
	 *
	 * @param string        $str  the input string which shall be made unique
	 * @param array<string> $used an input array of previously used strings;
	 *                            the output array will contain the result value
	 *
	 * @return string the unique string
	 */
	private function makeUnique(string $str, array &$used): string
	{
		if (count($used) > 0) {
			// @codeCoverageIgnoreStart
			$i = 1;
			$tmp = $str;
			while (in_array($tmp, $used, true)) {
				$tmp = $str . '-' . $i;
				$i++;
			}
			$str = $tmp;
			// @codeCoverageIgnoreEnd
		}
		$used[] = $str;

		return $str;
	}

	/**
	 * Compresses an album recursively.
	 *
	 * @param AbstractAlbum       $album               the album which shall be added to the archive
	 * @param array<string>       $used_dir_names      the list of already used directory names on the same level as `$album`
	 *                                                 ("siblings" of `$album`)
	 * @param string|null         $full_name_of_parent the fully qualified path name of the parent directory
	 * @param ZipStream           $zip                 the archive
	 * @param DownloadVariantType $variant             the desired size variant
	 *
	 * @throws FileNotFoundException
	 * @throws FileNotReadableException
	 */
	private function compressAlbum(AbstractAlbum $album, array &$used_dir_names, ?string $full_name_of_parent, ZipStream $zip, DownloadVariantType $variant): void
	{
		$full_name_of_parent = $full_name_of_parent ?? '';

		if (!Gate::check(AlbumPolicy::CAN_DOWNLOAD, [AbstractAlbum::class, $album])) {
			return;
		}

		$full_name_of_directory = $this->makeUnique(self::createValidTitle($album->get_title()), $used_dir_names);
		if ($full_name_of_parent !== '') {
			$full_name_of_directory = $full_name_of_parent . '/' . $full_name_of_directory;
		}

		$used_file_names = [];
		// TODO: Ensure that the size variant `original` for each photo is eagerly loaded as it is needed below. This must be solved in close coordination with `ArchiveAlbumRequest`.
		$photos = $album->get_photos();

		// For smart albums, get_photos() returns a paginator. We need to iterate through all pages.
		if ($photos instanceof LengthAwarePaginator) {
			$this->compressPhotosFromPaginator($photos, $album, $full_name_of_directory, $used_file_names, $zip, $variant);
		} else {
			$this->compressPhotosFromCollection($photos, $album, $full_name_of_directory, $used_file_names, $zip, $variant);
		}

		// Recursively compress sub-albums
		if ($album instanceof Album) {
			$sub_dirs = [];
			// TODO: For higher efficiency, ensure that the photos of each child album together with the original size variant are eagerly loaded.
			$sub_albums = $album->children;
			foreach ($sub_albums as $sub_album) {
				try {
					$this->compressAlbum($sub_album, $sub_dirs, $full_name_of_directory, $zip, $variant);
					// @codeCoverageIgnoreStart
				} catch (\Throwable $e) {
					Handler::reportSafely($e);
				}
				// @codeCoverageIgnoreEnd
			}
		}
	}

	/**
	 * Gathers an ordered list of all photos across the given albums with their computed ZIP paths.
	 *
	 * @param Collection<int,AbstractAlbum> $albums
	 * @param DownloadVariantType           $variant
	 *
	 * @return array<int,array{id:string,zip_path:string}>
	 */
	private function gatherAllFilenames(Collection $albums, DownloadVariantType $variant): array
	{
		$result = [];
		$used_dir_names = [];
		foreach ($albums as $album) {
			$this->gatherAlbumFilenames($album, $used_dir_names, null, $variant, $result);
		}

		return $result;
	}

	/**
	 * Recursive helper that collects photo id + zip_path pairs for an album and its descendants.
	 *
	 * @param AbstractAlbum                               $album
	 * @param array<string>                               $used_dir_names
	 * @param string|null                                 $full_name_of_parent
	 * @param DownloadVariantType                         $variant
	 * @param array<int,array{id:string,zip_path:string}> $result              (by reference)
	 */
	private function gatherAlbumFilenames(AbstractAlbum $album, array &$used_dir_names, ?string $full_name_of_parent, DownloadVariantType $variant, array &$result): void
	{
		$full_name_of_parent = $full_name_of_parent ?? '';

		if (!Gate::check(AlbumPolicy::CAN_DOWNLOAD, [AbstractAlbum::class, $album])) {
			return;
		}

		$full_name_of_directory = $this->makeUnique(self::createValidTitle($album->get_title()), $used_dir_names);
		if ($full_name_of_parent !== '') {
			$full_name_of_directory = $full_name_of_parent . '/' . $full_name_of_directory;
		}

		$used_file_names = [];
		$photos = $album->get_photos();
		$photo_collection = $photos instanceof LengthAwarePaginator
			? $this->paginatorToCollection($photos, $album)
			: $photos;

		/** @var Photo $photo */
		foreach ($photo_collection as $photo) {
			if (
				($album instanceof BaseSmartAlbum || $album instanceof TagAlbum) &&
				!Gate::check(PhotoPolicy::CAN_DOWNLOAD, $photo)
			) {
				// @codeCoverageIgnoreStart
				continue;
				// @codeCoverageIgnoreEnd
			}

			$size_variant_type = $variant->getSizeVariantType();
			$size_variant = $size_variant_type !== null
				? $photo->size_variants->getSizeVariant($size_variant_type)
				: null;
			if ($size_variant === null) {
				$size_variant = $photo->size_variants->getOriginal();
			}
			if ($size_variant === null) {
				continue;
			}
			$file = $size_variant->getFile();

			$file_base_name = $this->makeUnique(self::createValidTitle($photo->title), $used_file_names);
			$zip_path = $full_name_of_directory . '/' . $file_base_name . $file->getExtension();
			$file->close();

			$result[] = ['id' => $photo->id, 'zip_path' => $zip_path];
		}

		if ($album instanceof Album) {
			$sub_dirs = [];
			foreach ($album->children as $sub_album) {
				try {
					$this->gatherAlbumFilenames($sub_album, $sub_dirs, $full_name_of_directory, $variant, $result);
					// @codeCoverageIgnoreStart
				} catch (\Throwable $e) {
					Handler::reportSafely($e);
				}
				// @codeCoverageIgnoreEnd
			}
		}
	}

	/**
	 * Collects all photos from a paginator across all pages.
	 *
	 * @param LengthAwarePaginator<int,Photo> $paginator
	 * @param AbstractAlbum                   $album
	 *
	 * @return Collection<int,Photo>
	 */
	private function paginatorToCollection(LengthAwarePaginator $paginator, AbstractAlbum $album): Collection
	{
		/** @var Collection<int,Photo> $photos */
		$photos = $paginator->getCollection();
		$current_page = 1;
		$last_page = $paginator->lastPage();

		while ($current_page < $last_page) {
			$current_page++;
			/** @var LengthAwarePaginator<int,Photo> $next_page */
			$next_page = $album->photos()->paginate($paginator->perPage(), ['*'], 'page', $current_page);
			$photos = $photos->merge($next_page->getCollection());
		}

		return $photos;
	}

	/**
	 * Compresses an album recursively using a pre-computed inclusion map (for chunked downloads).
	 *
	 * @param AbstractAlbum        $album
	 * @param array<string>        $used_dir_names
	 * @param string|null          $full_name_of_parent
	 * @param ZipStream            $zip
	 * @param DownloadVariantType  $variant
	 * @param array<string,string> $included_map        photo_id => zip_path for photos to include
	 */
	private function compressAlbumSliced(AbstractAlbum $album, array &$used_dir_names, ?string $full_name_of_parent, ZipStream $zip, DownloadVariantType $variant, array $included_map): void
	{
		$full_name_of_parent = $full_name_of_parent ?? '';

		if (!Gate::check(AlbumPolicy::CAN_DOWNLOAD, [AbstractAlbum::class, $album])) {
			return;
		}

		$full_name_of_directory = $this->makeUnique(self::createValidTitle($album->get_title()), $used_dir_names);
		if ($full_name_of_parent !== '') {
			$full_name_of_directory = $full_name_of_parent . '/' . $full_name_of_directory;
		}

		$photos = $album->get_photos();

		if ($photos instanceof LengthAwarePaginator) {
			$this->compressPhotosFromPaginatorSliced($photos, $album, $zip, $variant, $included_map, $full_name_of_directory);
		} else {
			$this->compressPhotosFromCollectionSliced($photos, $album, $zip, $variant, $included_map);
		}

		if ($album instanceof Album) {
			$sub_dirs = [];
			foreach ($album->children as $sub_album) {
				try {
					$this->compressAlbumSliced($sub_album, $sub_dirs, $full_name_of_directory, $zip, $variant, $included_map);
					// @codeCoverageIgnoreStart
				} catch (\Throwable $e) {
					Handler::reportSafely($e);
				}
				// @codeCoverageIgnoreEnd
			}
		}
	}

	/**
	 * Streams photos from a paginator using the inclusion map.
	 *
	 * @param LengthAwarePaginator<int,Photo> $paginator
	 * @param AbstractAlbum                   $album
	 * @param ZipStream                       $zip
	 * @param DownloadVariantType             $variant
	 * @param array<string,string>            $included_map
	 * @param string                          $full_name_of_directory unused — path comes from map
	 */
	private function compressPhotosFromPaginatorSliced(LengthAwarePaginator $paginator, AbstractAlbum $album, ZipStream $zip, DownloadVariantType $variant, array $included_map, string $full_name_of_directory): void
	{
		$this->compressPhotosFromCollectionSliced($paginator->getCollection(), $album, $zip, $variant, $included_map);

		$current_page = 1;
		$last_page = $paginator->lastPage();
		while ($current_page < $last_page) {
			$current_page++;
			/** @var LengthAwarePaginator<int,Photo> $next_page */
			$next_page = $album->photos()->paginate($paginator->perPage(), ['*'], 'page', $current_page);
			$this->compressPhotosFromCollectionSliced($next_page->getCollection(), $album, $zip, $variant, $included_map);
		}
	}

	/**
	 * Streams photos from a collection using the inclusion map.
	 *
	 * @param Collection<int,Photo>|iterable<Photo> $photos
	 * @param AbstractAlbum                         $album
	 * @param ZipStream                             $zip
	 * @param DownloadVariantType                   $variant
	 * @param array<string,string>                  $included_map photo_id => zip_path
	 */
	private function compressPhotosFromCollectionSliced(Collection|iterable $photos, AbstractAlbum $album, ZipStream $zip, DownloadVariantType $variant, array $included_map): void
	{
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			if (!array_key_exists($photo->id, $included_map)) {
				continue;
			}

			try {
				if (
					($album instanceof BaseSmartAlbum || $album instanceof TagAlbum) &&
					!Gate::check(PhotoPolicy::CAN_DOWNLOAD, $photo)
				) {
					// @codeCoverageIgnoreStart
					continue;
					// @codeCoverageIgnoreEnd
				}

				$size_variant_type = $variant->getSizeVariantType();
				$size_variant = $size_variant_type !== null
					? $photo->size_variants->getSizeVariant($size_variant_type)
					: null;
				if ($size_variant === null) {
					$size_variant = $photo->size_variants->getOriginal();
				}
				if ($size_variant === null) {
					continue;
				}
				$file = $size_variant->getFile();

				$file_name = $included_map[$photo->id];

				try {
					set_time_limit(intval(ini_get('max_execution_time')));
				} catch (InfoException) {
					// Silently do nothing, if `set_time_limit` is denied.
				}
				$this->addFileToZip($zip, $file_name, $file, $photo);
				$file->close();
				// @codeCoverageIgnoreStart
			} catch (\Throwable $e) {
				Handler::reportSafely($e);
			}
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Compresses photos from a paginator by iterating through all pages.
	 *
	 * @param LengthAwarePaginator<int,Photo> $paginator
	 * @param AbstractAlbum                   $album
	 * @param string                          $full_name_of_directory
	 * @param array<string>                   $used_file_names
	 * @param ZipStream                       $zip
	 * @param DownloadVariantType             $variant
	 */
	private function compressPhotosFromPaginator(LengthAwarePaginator $paginator, AbstractAlbum $album, string $full_name_of_directory, array &$used_file_names, ZipStream $zip, DownloadVariantType $variant): void
	{
		$current_page = 1;
		$last_page = $paginator->lastPage();

		// Process first page (already loaded)
		$this->compressPhotosFromCollection($paginator->getCollection(), $album, $full_name_of_directory, $used_file_names, $zip, $variant);

		// Process remaining pages
		while ($current_page < $last_page) {
			$current_page++;
			/** @var LengthAwarePaginator<int,Photo> $next_page */
			$next_page = $album->photos()->paginate($paginator->perPage(), ['*'], 'page', $current_page);
			$this->compressPhotosFromCollection($next_page->getCollection(), $album, $full_name_of_directory, $used_file_names, $zip, $variant);
		}
	}

	/**
	 * Compresses photos from a collection.
	 *
	 * @param Collection<int,Photo>|iterable<Photo> $photos
	 * @param AbstractAlbum                         $album
	 * @param string                                $full_name_of_directory
	 * @param array<string>                         $used_file_names
	 * @param ZipStream                             $zip
	 * @param DownloadVariantType                   $variant
	 */
	private function compressPhotosFromCollection(Collection|iterable $photos, AbstractAlbum $album, string $full_name_of_directory, array &$used_file_names, ZipStream $zip, DownloadVariantType $variant): void
	{
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			try {
				// For photos in smart or tag albums, skip the ones that are not
				// downloadable based on their actual parent album.  The test for
				// album_id === null shouldn't really be needed as all such photos
				// in smart albums should be owned by the current user...
				if (
					($album instanceof BaseSmartAlbum || $album instanceof TagAlbum) &&
					!Gate::check(PhotoPolicy::CAN_DOWNLOAD, $photo)
				) {
					// @codeCoverageIgnoreStart
					continue;
					// @codeCoverageIgnoreEnd
				}

				// Use the requested size variant; fall back to ORIGINAL if unavailable
				$size_variant_type = $variant->getSizeVariantType();
				$size_variant = $size_variant_type !== null
					? $photo->size_variants->getSizeVariant($size_variant_type)
					: null;
				if ($size_variant === null) {
					$size_variant = $photo->size_variants->getOriginal();
				}
				if ($size_variant === null) {
					continue;
				}
				$file = $size_variant->getFile();

				// Generate name for file inside the ZIP archive
				$file_base_name = $this->makeUnique(self::createValidTitle($photo->title), $used_file_names);
				$file_name = $full_name_of_directory . '/' . $file_base_name . $file->getExtension();

				// Reset the execution timeout for every iteration.
				try {
					set_time_limit(intval(ini_get('max_execution_time')));
				} catch (InfoException) {
					// Silently do nothing, if `set_time_limit` is denied.
				}
				$this->addFileToZip($zip, $file_name, $file, $photo);
				$file->close();
				// @codeCoverageIgnoreStart
			} catch (\Throwable $e) {
				Handler::reportSafely($e);
			}
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * @param ZipStream                   $zip
	 * @param string                      $file_name
	 * @param FlysystemFile|BaseMediaFile $file
	 * @param Photo|null                  $photo
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	abstract protected function addFileToZip(ZipStream $zip, string $file_name, FlysystemFile|BaseMediaFile $file, Photo|null $photo): void;
}
