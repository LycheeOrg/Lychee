<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Actions\Import\Exec;
use App\Contracts\Exceptions\ExternalLycheeException;
use App\DTO\ImportMode;
use App\Exceptions\EmptyFolderException;
use App\Exceptions\InvalidDirectoryException;
use App\Exceptions\UnexpectedException;
use App\Http\Requests\Admin\ImportFromServerOptionsRequest;
use App\Http\Requests\Admin\ImportFromServerRequest;
use App\Http\Resources\Admin\ImportDirectoryResource;
use App\Http\Resources\Admin\ImportFromServerOptionsResource;
use App\Http\Resources\Admin\ImportFromServerResource;
use App\Models\Configs;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class ImportFromServerController extends Controller
{
	public function options(ImportFromServerOptionsRequest $request): ImportFromServerOptionsResource
	{
		return new ImportFromServerOptionsResource();
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
		// Configure import settings
		$import_mode = new ImportMode(
			delete_imported: $request->delete_imported,
			skip_duplicates: $request->skip_duplicates,
			import_via_symlink: $request->import_via_symlink,
			resync_metadata: $request->resync_metadata,
			shall_rename_photo_title: Configs::getValueAsBool('renamer_photo_title_enabled'),
			shall_rename_album_title: Configs::getValueAsBool('renamer_album_title_enabled'),
		);

		// Create the executor with should_execute_jobs set to false to collect jobs instead of executing them directly
		$exec = new Exec(
			import_mode: $import_mode,
			intended_owner_id: Configs::getValueAsInt('owner_id'),
			delete_missing_photos: $request->delete_missing_photos,
			delete_missing_albums: $request->delete_missing_albums,
			is_dry_run: false,
			should_execute_jobs: false,
		);

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
			} catch (\Exception $e) {
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
}
