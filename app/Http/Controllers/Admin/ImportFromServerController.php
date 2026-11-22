<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Actions\Import\Exec;
use App\Actions\Import\ImportPathGuard;
use App\Contracts\Exceptions\ExternalLycheeException;
use App\DTO\ImportMode;
use App\Exceptions\EmptyFolderException;
use App\Exceptions\InvalidDirectoryException;
use App\Exceptions\InvalidOptionsException;
use App\Exceptions\UnexpectedException;
use App\Http\Requests\Admin\ImportFromServerBrowseRequest;
use App\Http\Requests\Admin\ImportFromServerOptionsRequest;
use App\Http\Requests\Admin\ImportFromServerRequest;
use App\Http\Resources\Admin\ImportDirectoryResource;
use App\Http\Resources\Admin\ImportFromServerOptionsResource;
use App\Http\Resources\Admin\ImportFromServerResource;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImportFromServerController extends Controller
{
	/**
	 * @throws InvalidOptionsException when the import root of a restricted user is gone
	 */
	public function options(ImportFromServerOptionsRequest $request): ImportFromServerOptionsResource
	{
		$import_root = $request->importRoot();
		if (!self::isAvailable($import_root)) {
			Log::warning('import root unavailable', ['user_id' => Auth::id(), 'server_path' => $import_root]);
			throw new InvalidOptionsException('Import directory is not available');
		}

		return new ImportFromServerOptionsResource($import_root);
	}

	/**
	 * Full access (`null`) is always available; a restricted root must be a readable directory.
	 */
	private static function isAvailable(?string $import_root): bool
	{
		return $import_root === null || (is_dir($import_root) && is_readable($import_root));
	}

	/**
	 * Given a path, return the list of directories inside that path.
	 *
	 * @param ImportFromServerBrowseRequest $request
	 *
	 * @return string[]
	 *
	 * @throws InvalidDirectoryException
	 */
	public function browse(ImportFromServerBrowseRequest $request): array
	{
		// This is CHECKED after the validation of the request to avoid revealing part of the file system to unauthorized users.
		if (!is_dir($request->directory) || ImportPathGuard::isLink($request->directory)) {
			throw new InvalidDirectoryException('The specified directory is not valid: ' . $request->directory);
		}

		$import_root = $request->importRoot();
		$dirs = [];
		if (!self::isTop($request->directory, $import_root)) {
			$dirs[] = '..';
		}
		try {
			foreach (new \DirectoryIterator($request->directory) as $file_info) {
				// We only import directories here. Files are imported by the Importer when parsing the directories.
				// Links are never listed, wherever they point (FR-115-05).
				if ($file_info->isDir() && !$file_info->isDot() && !$file_info->isLink()) {
					$dirs[] = $file_info->getFilename();
				}
			}
		} catch (\Throwable $e) {
			// We ignore unreadable directories.
			Log::warning(__LINE__ . ':' . __FILE__ . ' ' . $e->getMessage(), $e->getTrace());
		}

		return $dirs;
	}

	/**
	 * Whether $directory is the top of what the user may browse (no `..` entry).
	 */
	private static function isTop(string $directory, ?string $import_root): bool
	{
		return $directory === '/' || ($import_root !== null && ImportPathGuard::resolve($directory) === $import_root);
	}

	/**
	 * Import photos from server directory into Lychee.
	 *
	 * @param ImportFromServerRequest $request Request containing import parameters
	 *
	 * @return ImportFromServerResource
	 *
	 * @throws ExternalLycheeException
	 */
	public function __invoke(ImportFromServerRequest $request): ImportFromServerResource
	{
		// This is CHECKED after the validation of the request to avoid revealing part of the file system to unauthorized users.
		foreach ($request->directories as $directory) {
			if (!is_dir($directory) || ImportPathGuard::isLink($directory)) {
				throw new InvalidOptionsException('directory does not exist or is a symbolic link: ' . $directory);
			}
		}

		// Create the executor with should_execute_jobs set to false to collect jobs instead of executing them directly
		$exec = $this->get_exec($request);

		$directory_results = [];
		$all_jobs = [];

		// Execute import for each directory and collect jobs
		foreach ($request->directories as $directory) {
			try {
				$jobs = $exec->do($directory, $request->album());
				$all_jobs = array_merge($all_jobs, $jobs);
				$directory_results[] = new ImportDirectoryResource(
					directory: $directory,
					status: true,
					jobs_count: count($jobs)
				);
			} catch (EmptyFolderException $e) {
				$directory_results[] = new ImportDirectoryResource(
					directory: $directory,
					status: false,
					message: 'Empty folder: ' . $e->getMessage()
				);
			} catch (InvalidDirectoryException $e) {
				$directory_results[] = new ImportDirectoryResource(
					directory: $directory,
					status: false,
					message: 'Invalid directory: ' . $e->getMessage()
				);
			} catch (\Throwable $e) {
				$directory_results[] = new ImportDirectoryResource(
					directory: $directory,
					status: false,
					message: $e->getMessage()
				);
				throw new UnexpectedException($e);
			}
		}

		// Dispatch all collected jobs at once
		foreach ($all_jobs as $job) {
			try {
				dispatch($job);
			} catch (\Throwable $e) {
				// Fail silently if dispatched sync.
				Log::error(__LINE__ . ':' . __FILE__ . ' ' . $e->getMessage(), $e->getTrace());
			}
		}

		// Create the overall result resource
		$result = new ImportFromServerResource(
			status: true,
			message: 'Import process completed',
			results: $directory_results,
			job_count: count($all_jobs)
		);

		return $result;
	}

	protected function get_exec(ImportFromServerRequest $request): Exec
	{
		// Configure import settings
		$import_mode = new ImportMode(
			delete_imported: $request->delete_imported,
			skip_duplicates: $request->skip_duplicates,
			import_via_symlink: $request->import_via_symlink,
			resync_metadata: $request->resync_metadata,
			shall_rename_photo_title: $request->configs()->getValueAsBool('renamer_photo_title_enabled'),
			shall_rename_album_title: $request->configs()->getValueAsBool('renamer_album_title_enabled'),
			scope_to_owner: $request->configs()->getValueAsBool('import_scope_to_owner'),
		);

		return new Exec(
			import_mode: $import_mode,
			intended_owner_id: intval(Auth::id()),
			delete_missing_photos: $request->delete_missing_photos,
			delete_missing_albums: $request->delete_missing_albums,
			is_dry_run: false,
			should_execute_jobs: false,
			import_root: $request->importRoot(),
			root_folder_as_album: $request->configs()->getValueAsBool('import_root_folder_as_album'),
			scope_to_owner: $request->configs()->getValueAsBool('import_scope_to_owner'),
		);
	}
}
