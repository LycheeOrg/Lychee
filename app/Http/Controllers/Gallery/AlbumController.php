<?php

namespace App\Http\Controllers\Gallery;

use App\Actions\Album\SetProtectionPolicy;
use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Requests\Album\GetAlbumRequest;
use App\Http\Requests\Album\SetAlbumProtectionPolicyRequest;
use App\Http\Requests\Album\UpdateAlbumRequest;
use App\Http\Requests\Album\UpdateTagAlbumRequest;
use App\Http\Resources\GalleryConfigs\AlbumConfig;
use App\Http\Resources\Models\AbstractAlbumResource;
use App\Http\Resources\Models\AlbumResource;
use App\Http\Resources\Models\SmartAlbumResource;
use App\Http\Resources\Models\TagAlbumResource;
use App\Models\Album;
use App\Models\TagAlbum;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Routing\Controller;

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

	public function updateAlbum(UpdateAlbumRequest $request): void
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
	}

	public function updateTagAlbum(UpdateTagAlbumRequest $request): void
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
	}

	public function updateProtectionPolicy(SetAlbumProtectionPolicyRequest $request, SetProtectionPolicy $setProtectionPolicy): void
	{
		$setProtectionPolicy->do(
			$request->album(),
			$request->albumProtectionPolicy(),
			$request->isPasswordProvided(),
			$request->password()
		);
	}
}