<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private const TAG_ID = 'tag_id';
	private const ALBUM_ID = 'album_id';
	private const SHOW_TAGS = 'show_tags';
	private const RANDOM_ID_LENGTH = 24;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('tag_albums_tags', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedBigInteger(self::TAG_ID)->nullable(false);
			$table->char(self::ALBUM_ID, self::RANDOM_ID_LENGTH)->nullable(false);

			$table->index([self::TAG_ID]);
			$table->index([self::ALBUM_ID]);
			$table->index([self::TAG_ID, self::ALBUM_ID]);
			$table->unique([self::TAG_ID, self::ALBUM_ID]);
			$table->foreign(self::TAG_ID)->references('id')->on('tags')->cascadeOnUpdate()->cascadeOnDelete();
			$table->foreign(self::ALBUM_ID)->references('id')->on('tag_albums')->cascadeOnUpdate()->cascadeOnDelete();
		});

		$all_tags = DB::table('tags')->select(['id'])->pluck('id')->all();
		DB::table('tag_albums')->orderBy('id')->chunk(100, function ($tag_albums) use ($all_tags) {
			$to_insert = [];
			if ($tag_albums->show_Tags === null) {
				return;
			}
			foreach ($tag_albums as $tag_album) {
				$tags = explode(' OR ', $tag_album->show_tags);
				foreach ($tags as $tag) {
					$tag = intval(trim($tag));
					if (!in_array($tag, $all_tags, true)) {
						// skip tags that do not exist in the new tags
						// this can happen if the tag was removed from the photo
						// but still exists in the tag_album's show_tags field
						continue;
					}
					$to_insert[] = [
						self::TAG_ID => $tag,
						self::ALBUM_ID => $tag_album->id,
					];
				}
			}

			if (count($to_insert) === 0) {
				return;
			}

			DB::table('tag_albums_tags')->insert($to_insert);
		});

		if (Schema::hasColumn('tag_albums', self::SHOW_TAGS)) {
			Schema::table('tag_albums', function (Blueprint $table) {
				$table->dropColumn(self::SHOW_TAGS);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if (!Schema::hasColumn('tag_albums', self::SHOW_TAGS)) {
			Schema::table('tag_albums', function (Blueprint $table) {
				$table->text(self::SHOW_TAGS)->nullable()->after('id');
			});
		}

		DB::table('tag_albums')->orderBy('id')->chunk(100, function ($tag_albums) {
			foreach ($tag_albums as $tag_album) {
				$tag_ids = DB::table('tag_albums_tags')->where(self::ALBUM_ID, '=', $tag_album->id)
					->select(self::TAG_ID)
					->pluck(self::TAG_ID)
					->all();

				$new_show_tag = implode(' OR ', $tag_ids);
				DB::table('tag_albums')
					->where('id', $tag_album->id)
					->update(['show_tags' => $new_show_tag]);
			}
		});

		Schema::dropIfExists('tag_albums_tags');
	}
};
