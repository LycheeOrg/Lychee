<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\Album\AlbumTagsRequest;
use App\Models\Album;
use App\Models\Tag;
use App\Models\TagAlbum;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * Controller for returning tags available in an album.
 *
 * Returns all unique tags from photos within the specified album,
 * ordered alphabetically by tag name.
 */
class AlbumTagsController extends Controller
{
	/**
	 * Get tags available in an album.
	 *
	 * @param AlbumTagsRequest $request the request with validated album_id
	 *
	 * @return array{tags: array<int,array{id: int, name: string, description: string|null}>}
	 */
	public function get(AlbumTagsRequest $request): array
	{
		$album = $request->album();

		$tags = $this->getTagsForAlbum($album);

		return ['tags' => $tags];
	}

	/**
	 * Get tags for different album types.
	 *
	 * @param AbstractAlbum $album the album to get tags for
	 *
	 * @return array<int,array{id: int, name: string, description: string|null}>
	 */
	private function getTagsForAlbum(AbstractAlbum $album): array
	{
		if ($album instanceof BaseSmartAlbum) {
			return $this->getTagsForSmartAlbum($album);
		}

		if ($album instanceof TagAlbum) {
			return $this->getTagsForTagAlbum($album);
		}

		/** @var Album $album */
		return $this->getTagsForRegularAlbum($album);
	}

	/**
	 * Get tags for a regular album.
	 *
	 * @param Album $album the regular album
	 *
	 * @return array<int,array{id: int, name: string, description: string|null}>
	 */
	private function getTagsForRegularAlbum(Album $album): array
	{
		// Query tags from photos that are directly in this album
		$tags = DB::table('tags')
			->select('tags.id', 'tags.name', 'tags.description')
			->join('photos_tags', 'tags.id', '=', 'photos_tags.tag_id')
			->join('photo_album', 'photos_tags.photo_id', '=', 'photo_album.photo_id')
			->where('photo_album.album_id', '=', $album->id)
			->distinct()
			->orderByRaw('LOWER(tags.name) ASC')
			->get()
			->map(fn ($tag) => [
				'id' => $tag->id,
				'name' => $tag->name,
				'description' => $tag->description,
			])
			->toArray();

		return $tags;
	}

	/**
	 * Get tags for a tag album.
	 *
	 * @param TagAlbum $album the tag album
	 *
	 * @return array<int,array{id: int, name: string, description: string|null}>
	 */
	private function getTagsForTagAlbum(TagAlbum $album): array
	{
		// For TagAlbum, query tags from photos within that tag album
		/** @phpstan-ignore method.private (It is NOT private and it works.) */
		$photoIds = $album->photos()->pluck('id')->toArray();

		if (count($photoIds) === 0) {
			return [];
		}

		$tags = DB::table('tags')
			->select('tags.id', 'tags.name', 'tags.description')
			->join('photos_tags', 'tags.id', '=', 'photos_tags.tag_id')
			->whereIn('photos_tags.photo_id', $photoIds)
			->distinct()
			->orderByRaw('LOWER(tags.name) ASC')
			->get()
			->map(fn ($tag) => [
				'id' => $tag->id,
				'name' => $tag->name,
				'description' => $tag->description,
			])
			->toArray();

		return $tags;
	}

	/**
	 * Get tags for a smart album.
	 *
	 * @param BaseSmartAlbum $album the smart album
	 *
	 * @return array<int,array{id: int, name: string, description: string|null}>
	 */
	private function getTagsForSmartAlbum(BaseSmartAlbum $album): array
	{
		// For smart albums, get photos from the smart album's query
		$photoIds = $album->photos()->pluck('id')->toArray();

		if (count($photoIds) === 0) {
			return [];
		}

		$tags = DB::table('tags')
			->select('tags.id', 'tags.name', 'tags.description')
			->join('photos_tags', 'tags.id', '=', 'photos_tags.tag_id')
			->whereIn('photos_tags.photo_id', $photoIds)
			->distinct()
			->orderByRaw('LOWER(tags.name) ASC')
			->get()
			->map(fn ($tag) => [
				'id' => $tag->id,
				'name' => $tag->name,
				'description' => $tag->description,
			])
			->toArray();

		return $tags;
	}
}
