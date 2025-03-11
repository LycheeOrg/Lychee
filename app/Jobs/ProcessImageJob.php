<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Jobs;

use App\Actions\Photo\Create;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\ImportMode;
use App\Enum\JobStatus;
use App\Exceptions\OwnerRequiredException;
use App\Factories\AlbumFactory;
use App\Image\Files\ProcessableJobFile;
use App\Image\Files\TemporaryJobFile;
use App\Models\Album;
use App\Models\Configs;
use App\Models\JobHistory;
use App\Models\Photo;
use App\Models\TagAlbum;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * This allows to process images on serverside while making the responses faster.
 * Note that the user will NOT see that the image is processed directly in upload when using queues.
 */
class ProcessImageJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	protected JobHistory $history;

	public string $filePath;
	public string $originalBaseName;
	public ?string $albumID;
	public int $userId;
	public ?int $fileLastModifiedTime;

	/**
	 * Create a new job instance.
	 */
	public function __construct(
		ProcessableJobFile $file,
		string|AbstractAlbum|null $abstractAlbum,
		?int $fileLastModifiedTime,
	) {
		$this->filePath = $file->getPath();
		$this->originalBaseName = $file->getOriginalBasename();

		$this->albumID = null;

		/** @var AbstractAlbum|null */
		$album = null;

		if (is_string($abstractAlbum)) {
			$album = resolve(AlbumFactory::class)->findAbstractAlbumOrFail($abstractAlbum);
		} elseif ($abstractAlbum instanceof AbstractAlbum) {
			$album = $abstractAlbum;
		}

		$this->albumID = $album?->id;
		$album_name = $album?->title ?? __('gallery.smart_album.unsorted');
		$user_id = Auth::user()?->id;
		if ($user_id === null && ($album === null || $album instanceof BaseSmartAlbum)) {
			throw new OwnerRequiredException();
		} else {
			/** @var Album|TagAlbum $album */
			$this->userId = $user_id ?? $album?->owner_id ?? throw new OwnerRequiredException();
		}

		$this->fileLastModifiedTime = $fileLastModifiedTime;

		// Set up our new history record.
		$this->history = new JobHistory();
		$this->history->owner_id = $this->userId;
		$this->history->job = Str::limit(sprintf('Process Image: %s added to %s.', $this->originalBaseName, $album_name), 200);
		$this->history->status = JobStatus::READY;

		$this->history->save();
	}

	/**
	 * Execute the job.
	 *
	 * Here we handle the execution of the image processing.
	 * This will create the model, reformat the image etc.
	 */
	public function handle(AlbumFactory $albumFactory): Photo
	{
		$this->history->status = JobStatus::STARTED;
		$this->history->save();

		$copiedFile = new TemporaryJobFile($this->filePath, $this->originalBaseName);

		// As the file has been uploaded, the (temporary) source file shall be
		// deleted
		$create = new Create(
			new ImportMode(deleteImported: true, skipDuplicates: Configs::getValueAsBool('skip_duplicates')),
			$this->userId
		);

		$album = null;
		if ($this->albumID !== null) {
			$album = $albumFactory->findAbstractAlbumOrFail($this->albumID);
		}

		$photo = $create->add($copiedFile, $album, $this->fileLastModifiedTime);

		// Once the job has finished, set history status to 1.
		$this->history->status = JobStatus::SUCCESS;
		$this->history->save();

		return $photo;
	}

	/**
	 * Catch failures.
	 *
	 * @param \Throwable $th
	 *
	 * @return void
	 */
	public function failed(\Throwable $th): void
	{
		$this->history->status = JobStatus::FAILURE;
		$this->history->save();

		if ($th->getCode() === 999) {
			$this->release();
		} else {
			Log::error(__LINE__ . ':' . __FILE__ . ' ' . $th->getMessage(), $th->getTrace());
		}
	}
}
