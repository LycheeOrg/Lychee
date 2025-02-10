<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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
use App\Events\AlbumRouteCacheUpdated;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\UnauthenticatedException;
use App\Http\Requests\Album\AddAlbumRequest;
use App\Http\Requests\Album\AddTagAlbumRequest;
use App\Http\Requests\Album\DeleteAlbumsRequest;
use App\Http\Requests\Album\DeleteTrackRequest;
use App\Http\Requests\Album\GetAlbumRequest;
use App\Http\Requests\Album\MergeAlbumsRequest;
use App\Http\Requests\Album\MoveAlbumsRequest;
use App\Http\Requests\Album\RenameAlbumRequest;
use App\Http\Requests\Album\SetAlbumProtectionPolicyRequest;
use App\Http\Requests\Album\SetAlbumTrackRequest;
use App\Http\Requests\Album\SetAsCoverRequest;
use App\Http\Requests\Album\SetAsHeaderRequest;
use App\Http\Requests\Album\TargetListAlbumRequest;
use App\Http\Requests\Album\TransferAlbumRequest;
use App\Http\Requests\Album\UnlockAlbumRequest;
use App\Http\Requests\Album\UpdateAlbumRequest;
use App\Http\Requests\Album\UpdateTagAlbumRequest;
use App\Http\Requests\Album\ZipRequest;
use App\Http\Resources\Editable\EditableBaseAlbumResource;
use App\Http\Resources\GalleryConfigs\AlbumConfig;
use App\Http\Resources\Models\AbstractAlbumResource;
use App\Http\Resources\Models\AlbumResource;
use App\Http\Resources\Models\SmartAlbumResource;
use App\Http\Resources\Models\TagAlbumResource;
use App\Http\Resources\Models\TargetAlbumResource;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use App\Models\TagAlbum;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller responsible for an album.
 */
class AlbumController extends Controller
{
	public const COMPACT_HEADER = 'compact';

	/**
	 * Provided an albumID, returns the album.
	 *
	 * @param GetAlbumRequest $request
	 *
	 * @return AbstractAlbumResource
	 */
	public function get(GetAlbumRequest $request): AbstractAlbumResource
	{
		$config = new AlbumConfig($request->album());
		$albumResource = null;

		if ($config->is_accessible) {
			$albumResource = match (true) {
				$request->album() instanceof BaseSmartAlbum => new SmartAlbumResource($request->album()),
				$request->album() instanceof TagAlbum => new TagAlbumResource($request->album()),
				$request->album() instanceof Album => new AlbumResource($request->album()),
				default => throw new LycheeLogicException('This should not happen'),
			};
		}

		return new AbstractAlbumResource($config, $albumResource);
	}

	/**
	 * Create an album.
	 *
	 * @param AddAlbumRequest $request
	 *
	 * @return string
	 */
	public function createAlbum(AddAlbumRequest $request): string
	{
		/** @var int $ownerId */
		$ownerId = Auth::id() ?? throw new UnauthenticatedException();
		$create = new Create($ownerId);

		return $create->create($request->title(), $request->parent_album())->id;
	}

	/**
	 * Create a tag album.
	 *
	 * @param AddTagAlbumRequest $request
	 *
	 * @return string
	 */
	public function createTagAlbum(AddTagAlbumRequest $request, CreateTagAlbum $create): string
	{
		// Root
		AlbumRouteCacheUpdated::dispatch('');

		return $create->create($request->title(), $request->tags())->id;
	}

	/**
	 * Update the info of an Album.
	 *
	 * @param UpdateAlbumRequest $request
	 *
	 * @return EditableBaseAlbumResource
	 */
	public function updateAlbum(UpdateAlbumRequest $request, SetHeader $setHeader): EditableBaseAlbumResource
	{
		$album = $request->album();
		if ($album === null) {
			throw new LycheeLogicException('album is null');
		}
		$album->title = $request->title();
		$album->description = $request->description();
		$album->license = $request->license();
		$album->album_thumb_aspect_ratio = $request->aspectRatio();
		$album->copyright = $request->copyright();
		$album->photo_sorting = $request->photoSortingCriterion();
		$album->album_sorting = $request->albumSortingCriterion();
		$album->photo_layout = $request->photoLayout();

		$album->album_timeline = $request->album_timeline();
		$album->photo_timeline = $request->photo_timeline();

		$album = $setHeader->do(
			album: $album,
			is_compact: $request->is_compact(),
			photo: $request->photo(),
			shall_override: true
		);

		return EditableBaseAlbumResource::fromModel($album);
	}

	/**
	 * Update the info of a Tag Album.
	 *
	 * @param UpdateTagAlbumRequest $request
	 *
	 * @return EditableBaseAlbumResource
	 */
	public function updateTagAlbum(UpdateTagAlbumRequest $request): EditableBaseAlbumResource
	{
		$album = $request->album();
		if ($album === null) {
			throw new LycheeLogicException('album is null');
		}
		$album->title = $request->title();
		$album->description = $request->description();
		$album->show_tags = $request->tags();
		$album->copyright = $request->copyright();
		$album->photo_sorting = $request->photoSortingCriterion();
		$album->photo_layout = $request->photoLayout();
		$album->photo_timeline = $request->photo_timeline();
		$album->save();

		// Root
		return EditableBaseAlbumResource::fromModel($album);
	}

	/**
	 * Update the protection policy of an Abstract Album.
	 *
	 * @param SetAlbumProtectionPolicyRequest $request
	 * @param SetProtectionPolicy             $setProtectionPolicy
	 * @param SetSmartProtectionPolicy        $setSmartProtectionPolicy
	 *
	 * @return AlbumProtectionPolicy
	 */
	public function updateProtectionPolicy(
		SetAlbumProtectionPolicyRequest $request,
		SetProtectionPolicy $setProtectionPolicy,
		SetSmartProtectionPolicy $setSmartProtectionPolicy,
	): AlbumProtectionPolicy {
		if ($request->album() instanceof BaseSmartAlbum) {
			return $this->updateProtectionPolicySmart($request->album(), $request->albumProtectionPolicy()->is_public, $setSmartProtectionPolicy);
		}

		/** @var BaseAlbum $album */
		$album = $request->album();

		return $this->updateProtectionPolicyBase(
			$album,
			$request->albumProtectionPolicy(),
			$request->isPasswordProvided(),
			$request->password(),
			$setProtectionPolicy
		);
	}

	private function updateProtectionPolicySmart(BaseSmartAlbum $album, bool $is_public, SetSmartProtectionPolicy $setSmartProtectionPolicy): AlbumProtectionPolicy
	{
		$setSmartProtectionPolicy->do($album, $is_public);

		return AlbumProtectionPolicy::ofSmartAlbum($album);
	}

	private function updateProtectionPolicyBase(
		BaseAlbum $album,
		AlbumProtectionPolicy $protectionPolicy,
		bool $shallSetPassword,
		?string $password,
		SetProtectionPolicy $setProtectionPolicy): AlbumProtectionPolicy
	{
		$setProtectionPolicy->do(
			$album,
			$protectionPolicy,
			$shallSetPassword,
			$password
		);

		return AlbumProtectionPolicy::ofBaseAlbum($album->refresh());
	}

	/**
	 * Delete the album and all of its pictures.
	 *
	 * @param DeleteAlbumsRequest $request the request
	 * @param Delete              $delete  the delete action
	 *
	 * @return void
	 */
	public function delete(DeleteAlbumsRequest $request, Delete $delete): void
	{
		$fileDeleter = $delete->do($request->albumIds());
		App::terminating(fn () => $fileDeleter->do());
	}

	/**
	 * Get the list of albums.
	 *
	 * @param TargetListAlbumRequest $request
	 * @param ListAlbums             $listAlbums
	 *
	 * @return array<string|int,TargetAlbumResource>
	 */
	public function getTargetListAlbums(TargetListAlbumRequest $request, ListAlbums $listAlbums)
	{
		$albums = $request->albums();
		$parent_id = $albums->count() > 0 ? $albums->first()->parent_id : null;

		return TargetAlbumResource::collect($listAlbums->do($albums, $parent_id));
	}

	/**
	 * Merge albums. The first of the list is the destination of the merge.
	 *
	 * @param MergeAlbumsRequest $request
	 * @param Merge              $merge
	 *
	 * @return void
	 */
	public function merge(MergeAlbumsRequest $request, Merge $merge): void
	{
		$request->albums()->each(fn (Album $album) => AlbumRouteCacheUpdated::dispatch($album->id));
		$merge->do($request->album(), $request->albums());
	}

	/**
	 * Move multiple albums into another album.
	 *
	 * @param MoveAlbumsRequest $request
	 * @param Move              $move
	 *
	 * @return void
	 */
	public function move(MoveAlbumsRequest $request, Move $move): void
	{
		$request->albums()->each(fn (Album $album) => AlbumRouteCacheUpdated::dispatch($album->id));
		$move->do($request->album(), $request->albums());
	}

	/**
	 * Transfer the ownership of the album to another user.
	 *
	 * @param TransferAlbumRequest $request
	 * @param Transfer             $transfer
	 *
	 * @return void
	 */
	public function transfer(TransferAlbumRequest $request, Transfer $transfer): void
	{
		$transfer->do($request->album(), $request->user2()->id);
	}

	/**
	 * Set the album cover (the square thumb).
	 *
	 * @param SetAsCoverRequest $request
	 *
	 * @return void
	 */
	public function cover(SetAsCoverRequest $request): void
	{
		$album = $request->album();
		$album->cover_id = ($album->cover_id === $request->photo()->id) ? null : $request->photo()->id;
		$album->save();
	}

	/**
	 * Set the album header (the hero banner).
	 *
	 * @param $request
	 *
	 * @return void
	 */
	public function header(SetAsHeaderRequest $request, SetHeader $setHeader): void
	{
		$setHeader->do($request->album(), $request->is_compact(), $request->photo());
	}

	/**
	 * Rename an album.
	 *
	 * @param RenameAlbumRequest $request
	 *
	 * @return void
	 */
	public function rename(RenameAlbumRequest $request): void
	{
		$album = $request->album();
		$album->title = $request->title();
		$album->save();
	}

	/**
	 * Return the archive of the pictures of the album and its sub-albums.
	 *
	 * @param ZipRequest $request
	 *
	 * @return StreamedResponse
	 */
	public function getArchive(ZipRequest $request): StreamedResponse
	{
		if ($request->albums()->count() > 0) {
			return AlbumBaseArchive::resolve()->do($request->albums());
		}

		return PhotoBaseArchive::resolve()->do($request->photos(), $request->sizeVariant());
	}

	/**
	 * Provided the albumID and password, return whether the album can be accessed or not.
	 *
	 * @param UnlockAlbumRequest $request
	 * @param Unlock             $unlock
	 *
	 * @return void
	 */
	public function unlock(UnlockAlbumRequest $request, Unlock $unlock): void
	{
		$unlock->do($request->album(), $request->password());
	}

	/**
	 * Upload a track for the Album.
	 *
	 * @param SetAlbumTrackRequest $request
	 *
	 * @return void
	 */
	public function setTrack(SetAlbumTrackRequest $request): void
	{
		$request->album()->setTrack($request->file);
	}

	/**
	 * Delete a track from the Album.
	 *
	 * @param DeleteTrackRequest $request
	 *
	 * @return void
	 */
	public function deleteTrack(DeleteTrackRequest $request): void
	{
		$request->album()->deleteTrack();
	}
}
