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
 * Add cluster_label (nullable INT) to the faces table.
 *
 * The AI-vision Python service populates this column after running DBSCAN
 * clustering over all stored face embeddings.  A value of -1 means the face
 * was classified as noise (no clear cluster match); NULL means clustering has
 * not been run yet for that face.
 *
 * The composite index on (cluster_label, person_id, is_dismissed) supports
 * the cluster-review UI query that groups unassigned, non-dismissed faces by
 * their cluster label.
 */
return new class() extends Migration {
	public function up(): void
	{
		Schema::table('faces', function (Blueprint $table): void {
			$table->integer('cluster_label')->nullable()->after('is_dismissed');
			$table->index(['cluster_label', 'person_id', 'is_dismissed'], 'faces_cluster_label_person_id_is_dismissed_index');
		});
	}

	public function down(): void
	{
		Schema::table('faces', function (Blueprint $table): void {
			$table->dropIndex('faces_cluster_label_person_id_is_dismissed_index');
			$table->dropColumn('cluster_label');
		});
	}
};
