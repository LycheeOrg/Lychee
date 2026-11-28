<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Actions\Album\AlbumUserThumbTargets;
use App\Actions\Shared\DispatchAfterDelete;
use App\Actions\Shop\PurchasableService;
use App\Assets\Features;
use App\Constants\PhotoAlbum as PA;
use App\Constants\QueryLimits;
use App\Enum\PhotoWebhookEvent;
use App\Enum\SizeVariantType;
use App\Events\AlbumListingCacheFlushRequested;
use App\Events\PhotoDeleted;
use App\Events\PhotoWillBeDeleted;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\ModelDBException;
use App\Jobs\PurgeTrashJob;
use App\Models\SizeVariant;
use App\Models\Webhook;
use Illuminate\Support\Facades\DB;

/**
 * Deletes the photos with the designated IDs **efficiently**.
 *
 * This class deliberately violates the principle of separations of concerns.
 * In an ideal world, the method would simply call `->delete()` on every
 * `Photo` model and the `Photo` model would take care of deleting its
 * associated size variants including the media files.
 * But this is extremely inefficient due to Laravel's architecture:
 *
 *  - Models are heavyweight god classes such that every instance also carries
 *    the whole code for serialization/deserialization
 *  - Models are active records (and don't use the unit-of-work pattern), i.e.
 *    every deletion of a model directly triggers a DB operation; they are
 *    not deferred into a batch operation
 *
 * Moreover, while removing the records for photos and size variants from the
 * DB can be implemented rather efficiently, the actual file operations may
 * take some time.
 * Especially, if the files are not stored locally but on a remote file system.
 * Hence, this method collects all files which need to be removed.
 * The caller can then decide to delete them asynchronously.
 */
readonly class Delete
{
	private PurchasableService $purchasable_service;
	private AlbumUserThumbTargets $thumb_targets;
	private Trash $trash;

	public function __construct(
	) {
		$this->purchasable_service = resolve(PurchasableService::class);
		$this->thumb_targets = resolve(AlbumUserThumbTargets::class);
		$this->trash = resolve(Trash::class);
	}

	/**
	 * Deletes the designated photos from the album `$from_id`.
	 *
	 * The photos lose their link to `$from_id`. Those left without any album
	 * link go to the trash and are hidden at once; a purge deletes their rows
	 * and files later (Feature 119, FR-119-02). With Unsorted no link is
	 * removed and the photos without an album are trashed. The request has
	 * checked that every photo is in `$from_id` (FR-119-09).
	 *
	 * @param string[]    $photo_ids the photo IDs
	 * @param string|null $from_id   the ID of the album from which the photos are deleted
	 *
	 * @throws ModelDBException
	 */
	public function do(array $photo_ids, string|null $from_id): void
	{
		if ($from_id === null) {
			throw new LycheeLogicException('The $from_id must be provided with the $photo_ids.');
		}
		$photo_ids = array_values(array_unique($photo_ids));
		if ($photo_ids === []) {
			return;
		}

		$this->purchasable_service->deleteMulitplePhotoPurchasables($photo_ids, [$from_id]);
		$trashed = $this->trash->fromAlbum($photo_ids, $from_id);

		$this->afterTrash($trashed, [$from_id], fn (string $photo_id): string => $from_id);
	}

	/**
	 * Force-delete a single photo unconditionally from all albums.
	 *
	 * Used by NSFW block actions where the photo must be removed
	 * regardless of album context.
	 *
	 * @throws ModelDBException
	 */
	public function forceDeletePhoto(string $photo_id): void
	{
		$this->deleteEverywhere([$photo_id]);
	}

	/**
	 * Deletes the photos themselves: every album link is removed and they
	 * go to the trash (NSFW block action, Moderation; FR-119-02, FR-119-10).
	 *
	 * @param string[] $photo_ids
	 *
	 * @throws ModelDBException
	 */
	public function deleteEverywhere(array $photo_ids): void
	{
		$photo_ids = array_values(array_unique($photo_ids));
		if ($photo_ids === []) {
			return;
		}

		// photo id => one of its albums, for the webhook payload (as before: the first one).
		$album_of = [];
		foreach (array_chunk($photo_ids, QueryLimits::ID_CHUNK_SIZE) as $chunk) {
			foreach (DB::table(PA::PHOTO_ALBUM)->whereIn(PA::PHOTO_ID, $chunk)->orderBy(PA::ALBUM_ID)->get([PA::PHOTO_ID, PA::ALBUM_ID]) as $link) {
				$album_of[$link->photo_id] ??= $link->album_id;
			}
		}
		$album_ids = array_values(array_unique($album_of));

		$this->purchasable_service->deleteMulitplePhotoPurchasables($photo_ids, $album_ids);
		$trashed = $this->trash->everywhere($photo_ids);

		$this->afterTrash($trashed, $album_ids, fn (string $photo_id): string => $album_of[$photo_id] ?? '');
	}

	/**
	 * Side effects of a delete once the photos are in the trash (FR-119-06).
	 * Their rows, tags and faces stay until the purge, so the thumb targets
	 * and the webhook payloads are computed from the trashed ids now.
	 *
	 * @param string[]                $trashed   photo ids put into the trash
	 * @param string[]                $album_ids albums that lost links
	 * @param \Closure(string):string $album_of  album id reported to webhooks for a photo
	 */
	private function afterTrash(array $trashed, array $album_ids, \Closure $album_of): void
	{
		$thumb_targets = $this->thumb_targets->resolve($trashed);
		$this->dispatchWillBeDeletedEvents($trashed, $album_of);

		// Links were removed by raw writes, bypassing the photo-level model events.
		AlbumListingCacheFlushRequested::dispatch();
		foreach ($album_ids as $album_id) {
			PhotoDeleted::dispatch($album_id);
		}

		// Thumb jobs and the purge: after the response on `sync` (FR-112-04, FR-119-05).
		$jobs = $this->thumb_targets->jobs($thumb_targets);
		if ($trashed !== []) {
			$jobs[] = new PurgeTrashJob();
		}
		DispatchAfterDelete::dispatch($jobs);
	}

	/**
	 * Fire PhotoWillBeDeleted for each photo scheduled for hard deletion.
	 *
	 * The event only feeds `photo.delete` webhooks: without an enabled one,
	 * nothing is loaded and no event is fired (FR-112-06).
	 *
	 * Uses a lean DB query (no full Eloquent hydration, so trashed photos are
	 * read) to load photo title and size variant URLs before the purge
	 * removes the records.
	 *
	 * @param string[]                $photo_ids IDs of the photos put into the trash
	 * @param \Closure(string):string $album_of  the album a photo is reported as deleted from
	 */
	private function dispatchWillBeDeletedEvents(array $photo_ids, \Closure $album_of): void
	{
		if ($photo_ids === [] || !$this->hasDeleteWebhook()) {
			return;
		}

		foreach (array_chunk($photo_ids, QueryLimits::ID_CHUNK_SIZE) as $photo_ids_chunked) {
			// Load minimal photo data.
			$photos_data = DB::table('photos')
				->whereIn('id', $photo_ids_chunked)
				->select(['id', 'title'])
				->get();

			// Load size variant data for all photos of the chunk in one query.
			$size_variants = SizeVariant::whereIn('photo_id', $photo_ids_chunked)
				->select(['photo_id', 'type', 'short_path', 'storage_disk'])
				->get()
				->groupBy('photo_id');

			foreach ($photos_data as $photo) {
				$variants = [];
				$raw_variants = $size_variants->get($photo->id, collect());
				/** @var SizeVariant $sv */
				foreach ($raw_variants as $sv) {
					$url = $sv->type === SizeVariantType::PLACEHOLDER ? $sv->short_path : $sv->getDownloadUrlAttribute();
					$variants[] = [
						'type' => $sv->type->name(),
						'url' => $url,
					];
				}

				PhotoWillBeDeleted::dispatch(
					$photo->id,
					$album_of($photo->id),
					$photo->title,
					$variants,
				);
			}
		}
	}

	/**
	 * Whether a `photo.delete` webhook can consume {@link PhotoWillBeDeleted}:
	 * the webhook feature is on and one such webhook is enabled.
	 */
	private function hasDeleteWebhook(): bool
	{
		return Features::active('webhook') &&
			Webhook::query()->enabled()->forEvent(PhotoWebhookEvent::DELETE)->exists();
	}
}
