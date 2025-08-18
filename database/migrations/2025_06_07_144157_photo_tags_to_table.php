<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::transaction(fn () => $this->applyUp());
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::transaction(fn () => $this->applyDown());
	}

	/**
	 * We itergate over all photos with tags and create a new tag for each
	 * unique tag name. Then we create a link between the tag and the photo.
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	private function applyUp(): void
	{
		// Empty pivot first to satisfy FK constraints, then tags
		DB::table('photos_tags')->delete();
		DB::table('tags')->delete();

		DB::transaction(function () {
			$tags_to_create = [];
			$tag_photo_links = [];

			$tag_idx = 0;
			DB::table('photos')->select(['id', 'tags'])
				->whereNotNull('tags')
				->where('tags', '!=', '')
				->orderBy('id')
				->chunk(100, function ($photos) use (&$tags_to_create, &$tag_photo_links, &$tag_idx) {
					foreach ($photos as $photo) {
						$tags = explode(',', $photo->tags);
						$seen = [];
						foreach ($tags as $rawTag) {
							$tag = ucwords(strtolower(trim($rawTag)));
							if ($tag === '' || isset($seen[$tag])) {
								continue; // skip empty tokens or duplicates within the same photo
							}
							$seen[$tag] = true;
							// Add the tag to the tags_to_create array if it doesn't exist
							if (!array_key_exists($tag, $tags_to_create)) {
								$tags_to_create[$tag] = [
									'id' => ++$tag_idx,
									'name' => $tag,
									'description' => null, // No description provided
								];
							}

							// Create a link between the tag and the photo
							$tag_photo_links[] = [
								'tag_id' => $tags_to_create[$tag]['id'],
								'photo_id' => $photo->id,
							];
						}
					}
				});

			$tags = collect(array_values($tags_to_create));
			$tags_chuncked = $tags->chunk(100);
			foreach ($tags_chuncked as $chunk) {
				DB::table('tags')->insert($chunk->all());
			}

			$tag_photo_links_collection = collect($tag_photo_links);
			$tag_photo_links_collection_chuncked = $tag_photo_links_collection->chunk(100);
			foreach ($tag_photo_links_collection_chuncked as $chunk) {
				DB::table('photos_tags')->insert($chunk->all());
			}

			DB::table('tag_albums')->orderBy('id')->chunk(100, function ($tag_albums) use (&$tags_to_create) {
				foreach ($tag_albums as $tag_album) {
					$new_show_tag = '';
					$tags = explode(',', $tag_album->show_tags);
					foreach ($tags as $tag) {
						$tag = trim($tag);
						if (!array_key_exists($tag, $tags_to_create)) {
							// skip tags that do not exist in the new tags
							// this can happen if the tag was removed from the photo
							// but still exists in the tag_album's show_tags field
							continue;
						}
						$new_show_tag .= ($new_show_tag !== '' ? ' OR ' : '') . $tags_to_create[$tag]['id'];
					}
					DB::table('tag_albums')
						->where('id', $tag_album->id)
						->update(['show_tags' => $new_show_tag]);
				}
			});
		});
	}

	/**
	 * Reversely, we iterate over all photos with tags and merge the tags in
	 * a comma-separated string before updating the photo's tags field.
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	private function applyDown(): void
	{
		DB::transaction(function () {
			DB::table('photos_tags')->distinct()->select('photo_id')
				->orderBy('photo_id')
				->chunk(100, function ($photo_ids) {
					foreach ($photo_ids as $photo_id_line) {
						$tags = DB::table('tags')
							->select(['tags.name'])
							->join('photos_tags', 'tags.id', '=', 'photos_tags.tag_id')
							->where('photos_tags.photo_id', '=', $photo_id_line->photo_id)->pluck('name');
						$tags = implode(',', $tags->toArray());
						DB::table('photos')->where('id', $photo_id_line->photo_id)->update(['tags' => $tags]);
					}
				});

			// Build mapping id => name for the tags.
			$id_to_tag = DB::table('tags')->select(['id', 'name'])->pluck('name', 'id')->toArray();
			DB::table('tag_albums')->orderBy('id')->chunk(100, function ($tag_albums) use (&$id_to_tag) {
				foreach ($tag_albums as $tag_album) {
					if (str_contains($tag_album->show_tags, ' AND ')) {
						// We skip, this is not supported.
						continue;
					}

					$new_show_tag = '';
					$tags_ids = explode(' OR ', $tag_album->show_tags);
					foreach ($tags_ids as $tag_id) {
						$tag_id = trim($tag_id);
						if (!array_key_exists($tag_id, $id_to_tag)) {
							// skip tags that do not exist in the new tags
							// this can happen if the tag was removed from the photo
							// but still exists in the tag_album's show_tags field
							continue;
						}
						$new_show_tag .= ($new_show_tag !== '' ? ',' : '') . $id_to_tag[$tag_id];
					}
					DB::table('tag_albums')
						->where('id', $tag_album->id)
						->update(['show_tags' => $new_show_tag]);
				}
			});

			DB::table('photos_tags')->delete();
			DB::table('tags')->delete();
		});
	}
};
