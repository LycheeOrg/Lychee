<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Album\BaseArchive as AlbumBaseArchive;
use App\Actions\Album\Create;
use App\Actions\Album\CreateTagAlbum;
use App\Actions\Album\Delete;
use App\Actions\Album\ListAlbums;
use App\Actions\Album\Merge;
use App\Actions\Album\Move;
use App\Actions\Album\SetHeader;
use App\Actions\Album\SetProtectionPolicy;
use App\Actions\Album\SetSmartProtectionPolicy;
use App\Actions\Album\Transfer;
use App\Actions\Album\Unlock;
use App\Actions\Photo\BaseArchive as PhotoBaseArchive;
use App\Enum\SizeVariantType;
use App\Events\AlbumRouteCacheUpdated;
use App\Events\Metrics\AlbumDownload;
use App\Events\Metrics\PhotoDownload;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\UnauthenticatedException;
use App\Http\Controllers\MetricsController;
use App\Http\Requests\Album\AddAlbumRequest;
use App\Http\Requests\Album\AddTagAlbumRequest;
use App\Http\Requests\Album\DeleteAlbumsRequest;
use App\Http\Requests\Album\DeleteTrackRequest;
use App\Http\Requests\Album\MergeAlbumsRequest;
use App\Http\Requests\Album\MoveAlbumsRequest;
use App\Http\Requests\Album\RenameAlbumRequest;
use App\Http\Requests\Album\SetAlbumProtectionPolicyRequest;
use App\Http\Requests\Album\SetAlbumTrackRequest;
use App\Http\Requests\Album\SetAsCoverRequest;
use App\Http\Requests\Album\SetAsHeaderRequest;
use App\Http\Requests\Album\SetPinnedRequest;
use App\Http\Requests\Album\TargetListAlbumRequest;
use App\Http\Requests\Album\TransferAlbumRequest;
use App\Http\Requests\Album\UnlockAlbumRequest;
use App\Http\Requests\Album\UpdateAlbumRequest;
use App\Http\Requests\Album\UpdateTagAlbumRequest;
use App\Http\Requests\Album\WatermarkAlbumRequest;
use App\Http\Requests\Album\ZipRequest;
use App\Http\Requests\Traits\HasVisitorIdTrait;
use App\Http\Resources\Editable\EditableBaseAlbumResource;
use App\Http\Resources\Models\TargetAlbumResource;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Jobs\WatermarkerJob;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\Tag;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller responsible for an album.
 */
class AlbumController extends Controller
{
	use HasVisitorIdTrait;

	public const COMPACT_HEADER = 'compact';

	/**
	 * Create an album.
	 */
	public function createAlbum(AddAlbumRequest $request): string
	{
		$owner_id = Auth::id() ?? throw new UnauthenticatedException();
		$create = new Create($owner_id);

		return $create->create($request->title(), $request->parent_album())->id;
	}

	/**
	 * Create a tag album.
	 */
	public function createTagAlbum(AddTagAlbumRequest $request, CreateTagAlbum $create): string
	{
		// Root
		AlbumRouteCacheUpdated::dispatch('');

		return $create->create($request->title(), $request->tags(), $request->is_and())->id;
	}

	/**
	 * Update the info of an Album.
	 */
	public function updateAlbum(UpdateAlbumRequest $request, SetHeader $set_header): EditableBaseAlbumResource
	{
		$album = $request->album();
		if ($album === null) {
			// @codeCoverageIgnoreStart
			throw new LycheeLogicException('album is null');
			// @codeCoverageIgnoreEnd
		}
		$album->title = $request->title();
		$album->description = $request->description();
		$album->license = $request->license();
		$album->album_thumb_aspect_ratio = $request->aspectRatio();
		$album->copyright = $request->copyright();
		$album->photo_sorting = $request->photoSortingCriterion();
		$album->album_sorting = $request->albumSortingCriterion();
		$album->photo_layout = $request->photoLayout();
		$album->is_pinned = $request->is_pinned();

		$album->album_timeline = $request->album_timeline();
		$album->photo_timeline = $request->photo_timeline();

		$album = $set_header->do(
			album: $album,
			is_compact: $request->is_compact(),
			photo: $request->photo(),
			shall_override: true
		);

		return EditableBaseAlbumResource::fromModel($album);
	}

	/**
	 * Update the info of a Tag Album.
	 */
	public function updateTagAlbum(UpdateTagAlbumRequest $request): EditableBaseAlbumResource
	{
		$album = $request->album();
		if ($album === null) {
			// @codeCoverageIgnoreStart
			throw new LycheeLogicException('album is null');
			// @codeCoverageIgnoreEnd
		}
		$album->title = $request->title();
		$album->description = $request->description();
		$album->copyright = $request->copyright();
		$album->photo_sorting = $request->photoSortingCriterion();
		$album->photo_layout = $request->photoLayout();
		$album->photo_timeline = $request->photo_timeline();
		$album->is_pinned = $request->is_pinned();
		$album->is_and = $request->is_and();
		$album->save();

		$tag_models = Tag::from($request->tags());
		$album->tags()->sync($tag_models->pluck('id')->all());

		// Root
		return EditableBaseAlbumResource::fromModel($album);
	}

	/**
	 * Update the protection policy of an Abstract Album.
	 */
	public function updateProtectionPolicy(
		SetAlbumProtectionPolicyRequest $request,
		SetProtectionPolicy $set_protection_policy,
		SetSmartProtectionPolicy $set_smart_protection_policy,
	): AlbumProtectionPolicy {
		if ($request->album() instanceof BaseSmartAlbum) {
			return $this->updateProtectionPolicySmart($request->album(), $request->albumProtectionPolicy()->is_public, $set_smart_protection_policy);
		}

		/** @var BaseAlbum $album */
		$album = $request->album();

		return $this->updateProtectionPolicyBase(
			$album,
			$request->albumProtectionPolicy(),
			$request->isPasswordProvided(),
			$request->password(),
			$set_protection_policy
		);
	}

	private function updateProtectionPolicySmart(BaseSmartAlbum $album, bool $is_public, SetSmartProtectionPolicy $set_smart_protection_policy): AlbumProtectionPolicy
	{
		$set_smart_protection_policy->do($album, $is_public);

		return AlbumProtectionPolicy::ofSmartAlbum($album);
	}

	private function updateProtectionPolicyBase(
		BaseAlbum $album,
		AlbumProtectionPolicy $protection_policy,
		bool $shall_set_password,
		?string $password,
		SetProtectionPolicy $set_protection_policy): AlbumProtectionPolicy
	{
		$set_protection_policy->do(
			$album,
			$protection_policy,
			$shall_set_password,
			$password
		);

		return AlbumProtectionPolicy::ofBaseAlbum($album->refresh());
	}

	/**
	 * Delete the album and all of its pictures.
	 *
	 * @param DeleteAlbumsRequest $request the request
	 * @param Delete              $delete  the delete action
	 */
	public function delete(DeleteAlbumsRequest $request, Delete $delete): void
	{
		$delete->do($request->albumIds());
	}

	/**
	 * Get the list of albums.
	 *
	 * @return array<string|int,TargetAlbumResource>
	 */
	public function getTargetListAlbums(TargetListAlbumRequest $request, ListAlbums $list_albums): array
	{
		$albums = $request->albums();
		$parent_id = $albums->count() > 0 ? $albums->first()->parent_id : null;

		return TargetAlbumResource::collect($list_albums->do($albums, $parent_id, null));
	}

	/**
	 * Merge albums. The first of the list is the destination of the merge.
	 */
	public function merge(MergeAlbumsRequest $request, Merge $merge): void
	{
		$request->albums()->each(fn (Album $album) => AlbumRouteCacheUpdated::dispatch($album->id));
		$merge->do($request->album(), $request->albums());
	}

	/**
	 * Move multiple albums into another album.
	 */
	public function move(MoveAlbumsRequest $request, Move $move): void
	{
		$request->albums()->each(fn (Album $album) => AlbumRouteCacheUpdated::dispatch($album->id));
		$move->do($request->album(), $request->albums());
	}

	/**
	 * Transfer the ownership of the album to another user.
	 */
	public function transfer(TransferAlbumRequest $request, Transfer $transfer): void
	{
		$transfer->do($request->album(), $request->user2()->id);
	}

	/**
	 * Set the album cover (the square thumb).
	 */
	public function cover(SetAsCoverRequest $request): void
	{
		$album = $request->album();
		$album->cover_id = ($album->cover_id === $request->photo()->id) ? null : $request->photo()->id;
		$album->save();
	}

	/**
	 * Set the album header (the hero banner).
	 */
	public function header(SetAsHeaderRequest $request, SetHeader $set_header): void
	{
		$set_header->do($request->album(), $request->is_compact(), $request->photo());
	}

	/**
	 * Rename an album.
	 */
	public function rename(RenameAlbumRequest $request): void
	{
		$album = $request->album();
		$album->title = $request->title();
		$album->save();
	}

	/**
	 * Set the pinned status of an album.
	 */
	public function setPinned(SetPinnedRequest $request): void
	{
		$album = $request->album();
		$album->is_pinned = $request->is_pinned();
		$album->save();
	}

	/**
	 * Return the archive of the pictures of the album and its sub-albums.
	 */
	public function getArchive(ZipRequest $request): StreamedResponse
	{
		$should_measure = MetricsController::shouldMeasure();
		if ($request->albums()->count() > 0) {
			// We dispatch one event per album.
			foreach ($request->albums() as $album) {
				AlbumDownload::dispatchIf($should_measure, $this->visitorId(), $album->get_id());
			}

			return AlbumBaseArchive::resolve()->do($request->albums());
		}

		// We dispatch one event per photo.
		foreach ($request->photos() as $photo) {
			PhotoDownload::dispatchIf($should_measure && $request->from_id() !== null, $this->visitorId(), $photo->id, $request->from_id());
		}

		return PhotoBaseArchive::resolve()->do($request->photos(), $request->sizeVariant());
	}

	/**
	 * Provided the albumID and password, return whether the album can be accessed or not.
	 */
	public function unlock(UnlockAlbumRequest $request, Unlock $unlock): void
	{
		$unlock->do($request->album(), $request->password());
	}

	/**
	 * Upload a track for the Album.
	 */
	public function setTrack(SetAlbumTrackRequest $request): void
	{
		$request->album()->setTrack($request->file);
	}

	/**
	 * Delete a track from the Album.
	 */
	public function deleteTrack(DeleteTrackRequest $request): void
	{
		$request->album()->deleteTrack();
	}

	/**
	 * Watermark all SizeVariants of photos in an album.
	 *
	 * Dispatches a WatermarkerJob for each SizeVariant where short_path_watermarked is not set.
	 */
	public function watermarkAlbumPhotos(WatermarkAlbumRequest $request): void
	{
		$album = $request->album();

		// Get all photos in the album and process their size variants
		// Filter variants that need watermarking and dispatch jobs
		/** @phpstan-ignore return.type (stupid covariance...) */
		$album->photos->each(fn (Photo $photo) => $photo->size_variants->toCollection()
			->filter(fn (?SizeVariant $v) => $this->shouldWatermark($v))
			->each(fn (?SizeVariant $v) => WatermarkerJob::dispatch($v, $photo->owner_id)));
	}

	private function shouldWatermark(?SizeVariant $size_variant): bool
	{
		if ($size_variant === null || $size_variant->type === SizeVariantType::PLACEHOLDER) {
			return false;
		}
		if ($size_variant->type === SizeVariantType::ORIGINAL && !request()->configs()->getValueAsBool('watermark_original')) {
			return false;
		}

		return !$size_variant->is_watermarked;
	}
}
