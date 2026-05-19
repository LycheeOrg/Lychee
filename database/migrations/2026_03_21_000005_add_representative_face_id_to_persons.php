<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add representative_face_id (nullable FK → faces) to the persons table.
 *
 * This column is added in a separate migration (after the faces table has been
 * created) to avoid a circular foreign-key dependency between persons and faces.
 *
 * When set, the referenced face is used as the representative thumbnail for the
 * person in the UI.  When NULL the UI falls back to the highest-confidence
 * non-dismissed face that has a crop_token.
 */
return new class() extends Migration {
	public function up(): void
	{
		Schema::table('persons', function (Blueprint $table): void {
			$table->char('representative_face_id', 24)->nullable()->after('is_searchable');
			$table->foreign('representative_face_id')
				->references('id')
				->on('faces')
				->onDelete('set null');
		});
	}

	public function down(): void
	{
		Schema::table('persons', function (Blueprint $table): void {
			$table->dropForeign(['representative_face_id']);
			$table->dropColumn('representative_face_id');
		});
	}
};
