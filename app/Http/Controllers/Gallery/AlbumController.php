<?php

namespace App\Http\Controllers\Gallery;

use App\Actions\Album\Delete;
use App\Actions\Album\ListAlbums;
use App\Actions\Album\Merge;
use App\Actions\Album\Move;
use App\Actions\Album\SetProtectionPolicy;
use App\Actions\Album\Transfer;
use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Requests\Album\DeleteAlbumsRequest;
use App\Http\Requests\Album\GetAlbumRequest;
use App\Http\Requests\Album\MergeAlbumsRequest;
use App\Http\Requests\Album\MoveAlbumsRequest;
use App\Http\Requests\Album\SetAlbumProtectionPolicyRequest;
use App\Http\Requests\Album\TargetListAlbumRequest;
use App\Http\Requests\Album\TransferAlbumRequest;
use App\Http\Requests\Album\UpdateAlbumRequest;
use App\Http\Requests\Album\UpdateTagAlbumRequest;
use App\Http\Resources\Editable\EditableBaseAlbumResource;
use App\Http\Resources\GalleryConfigs\AlbumConfig;
use App\Http\Resources\Models\AbstractAlbumResource;
use App\Http\Resources\Models\AlbumResource;
use App\Http\Resources\Models\SmartAlbumResource;
use App\Http\Resources\Models\TagAlbumResource;
use App\Http\Resources\Models\TargetAlbumResource;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Models\Album;
use App\Models\TagAlbum;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;

/**
 * Controller responsible for the config.
 */
class AlbumController extends Controller
{
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
				default => throw new LycheeLogicException('This should not happen')
			};
		}

		return new AbstractAlbumResource($config, $albumResource);
	}

	public function updateAlbum(UpdateAlbumRequest $request): EditableBaseAlbumResource
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
		$album->save();

		return EditableBaseAlbumResource::fromModel($album);
	}

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
		$album->save();

		return EditableBaseAlbumResource::fromModel($album);
	}

	public function updateProtectionPolicy(SetAlbumProtectionPolicyRequest $request, SetProtectionPolicy $setProtectionPolicy): AlbumProtectionPolicy
	{
		$setProtectionPolicy->do(
			$request->album(),
			$request->albumProtectionPolicy(),
			$request->isPasswordProvided(),
			$request->password()
		);

		return AlbumProtectionPolicy::ofBaseAlbum($request->album()->refresh());
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
		if ($request->album() instanceof Album) {
			/** @var Album $album */
			$album = $request->album();

			return TargetAlbumResource::collect($listAlbums->do($album->_lft, $album->_rgt, $album->parent_id));
		}

		return TargetAlbumResource::collect($listAlbums->do(null, null, null));
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
		$move->do($request->album(), $request->albums());
	}

	/**
	 * @param TransferAlbumRequest $request
	 * @param Transfer             $transfer
	 *
	 * @return void
	 */
	public function transfer(TransferAlbumRequest $request, Transfer $transfer): void
	{
		$transfer->do($request->album(), $request->userId());
	}
}