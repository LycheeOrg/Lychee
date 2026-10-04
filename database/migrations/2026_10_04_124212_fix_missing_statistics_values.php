<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
		DB::transaction(function ()  {
			DB::statement('INSERT INTO statistics (photo_id) SELECT p.id FROM photos p LEFT JOIN statistics s ON s.photo_id = p.id WHERE s.photo_id IS NULL;');
			DB::statement('INSERT INTO statistics (album_id) SELECT a.id FROM albums a LEFT JOIN statistics s ON s.album_id = a.id WHERE s.album_id IS NULL;');
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
