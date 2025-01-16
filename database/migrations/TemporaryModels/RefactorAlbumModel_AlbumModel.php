<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\Node;
use Kalnoy\Nestedset\NodeTrait;

/**
 * Model class specific for this migration.
 *
 * Migrations are required to be also runnable in the future after the code
 * base will have evolved.
 * To this end, migrations must not rely on a specific implementation of
 * models, because these models may change in the future, but the migration
 * must conduct its task with respect to a table layout which was valid at
 * the time when the migration was written.
 * In conclusion, this implies that migration should not use models but use
 * low-level DB queries when necessary.
 * Unfortunately, we need the `fixTree()` algorithm and there is no
 * implementation which uses low-level DB queries.
 *
 * @implements Node<RefactorAlbumModel_AlbumModel>
 */
class RefactorAlbumModel_AlbumModel extends Model implements Node
{
	/** @phpstan-use NodeTrait<RefactorAlbumModel_AlbumModel> */
	use NodeTrait;

	protected $table = 'albums';

	protected $keyType = 'string';

	public $timestamps = false;
}
