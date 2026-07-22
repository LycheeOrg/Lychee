<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\DTO\PhotoSortingCriterion;
use App\Factories\AlbumFactory;
use App\Models\AlbumUserThumb;
use App\Models\Extensions\Thumb;
use App\Models\PersonAlbum;
use App\Models\TagAlbum;
use App\Models\User;
use App\Relations\HasManyPhotosByPerson;
use App\Relations\HasManyPhotosByTag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\Skip;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Recomputes the cached thumb (cover photo) of a tag/person/smart album for
 * every viewer (registered user, or `null` for the public/guest view) which
 * already has a row in `album_user_thumbs` for that album.
 *
 * Only already-cached viewers are refreshed - a viewer who has never opened
 * the album has no row yet, and gets one lazily seeded on their next read
 * (see {@link \App\Models\TagAlbum::getThumbAttribute()} and siblings).
 * This avoids recomputing for every user in the system on every photo edit.
 */
class RecomputeAlbumUserThumbsJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public const KIND_TAG = 'tag';
	public const KIND_PERSON = 'person';
	public const KIND_SMART = 'smart';

	private string $jobId;

	/**
	 * Number of times to retry the job.
	 *
	 * @var int
	 */
	public $tries = 3;

	/**
	 * @param string $album_kind one of {@link self::KIND_TAG}, {@link self::KIND_PERSON}, {@link self::KIND_SMART}
	 * @param string $album_id   base_albums.id (tag/person) or a SmartAlbumType value (smart)
	 */
	public function __construct(
		public string $album_kind,
		public string $album_id,
	) {
		$this->jobId = uniqid('job_', true);

		// Register this as the latest job for this album
		Cache::put(
			$this->cacheKey(),
			$this->jobId,
			ttl: now()->plus(days: 1)
		);
	}

	private function cacheKey(): string
	{
		return 'album_user_thumb_latest_job:' . $this->album_kind . ':' . $this->album_id;
	}

	/**
	 * Get the middleware the job should pass through.
	 *
	 * @return array<int,object>
	 */
	public function middleware(): array
	{
		return [
			Skip::when(fn () => $this->hasNewerJobQueued()),
		];
	}

	protected function hasNewerJobQueued(): bool
	{
		$latest_job_id = Cache::get($this->cacheKey());

		// We skip if there is no newer job, or if the latest job is not this one
		$has_newer_job = $latest_job_id !== null && $latest_job_id !== $this->jobId;
		if ($has_newer_job) {
			Log::channel('jobs')->debug("Skipping job {$this->jobId} for {$this->album_kind} album {$this->album_id} due to newer job {$latest_job_id} queued.");
		}

		return $has_newer_job;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(): void
	{
		Log::channel('jobs')->info("Recomputing user thumbs for {$this->album_kind} album {$this->album_id}");
		Cache::forget($this->cacheKey());

		/** @var array<int,int|null> $user_ids */
		$user_ids = AlbumUserThumb::query()->where('album_id', '=', $this->album_id)->pluck('user_id')->all();

		foreach ($user_ids as $user_id) {
			$this->refreshForViewer($user_id);
		}
	}

	private function refreshForViewer(?int $user_id): void
	{
		$user = $user_id !== null ? User::find($user_id) : null;

		$thumb = match ($this->album_kind) {
			self::KIND_TAG => $this->resolveTagThumb($user),
			self::KIND_PERSON => $this->resolvePersonThumb($user),
			self::KIND_SMART => $this->resolveSmartThumb($user),
			default => null,
		};

		if ($thumb === null) {
			// No photo qualifies anymore (e.g. album emptied, or last-matching photo deleted).
			AlbumUserThumb::query()->where('album_id', '=', $this->album_id)->where('user_id', '=', $user_id)->delete();

			return;
		}

		AlbumUserThumb::query()->updateOrCreate(
			['album_id' => $this->album_id, 'user_id' => $user_id],
			['photo_id' => $thumb->id]
		);
	}

	private function resolveTagThumb(?User $user): ?Thumb
	{
		$album = TagAlbum::find($this->album_id);
		if ($album === null) {
			return null;
		}

		$relation = new HasManyPhotosByTag($album, for_user: $user, user_is_set: true);

		return Thumb::createFromQueryable($relation, $album->getEffectivePhotoSorting());
	}

	private function resolvePersonThumb(?User $user): ?Thumb
	{
		$album = PersonAlbum::find($this->album_id);
		if ($album === null) {
			return null;
		}

		$relation = new HasManyPhotosByPerson($album, for_user: $user, user_is_set: true);

		return Thumb::createFromQueryable($relation, $album->getEffectivePhotoSorting());
	}

	private function resolveSmartThumb(?User $user): ?Thumb
	{
		$smart_album_class = AlbumFactory::BUILTIN_SMARTS_CLASS[$this->album_id] ?? null;
		if ($smart_album_class === null) {
			return null;
		}

		$album = $smart_album_class::getInstance()->forUser($user);

		return Thumb::createFromQueryable($album->photos(), PhotoSortingCriterion::createDefault());
	}

	/**
	 * Handle job failure after all retries exhausted.
	 *
	 * @param \Throwable $exception
	 *
	 * @return void
	 */
	public function failed(\Throwable $exception): void
	{
		Log::channel('jobs')->error("Job failed permanently for {$this->album_kind} album {$this->album_id}: " . $exception->getMessage());
	}
}
