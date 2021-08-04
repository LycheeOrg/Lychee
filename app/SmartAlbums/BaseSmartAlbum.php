<?php

namespace App\SmartAlbums;

use App\Contracts\BaseModelAlbum;

/**
 * Class BaseSmartAlbum.
 *
 * The common base class for all built-in smart albums which can neither
 * be created to deleted, but always exists.
 * Photos cannot explicitly be added or removed from these albums, but
 * photos belong to these albums due to certain properties like being
 * starred, being recently added, etc.
 */
class BaseSmartAlbum implements BaseModelAlbum
{
	protected string $id;

	protected function _construct(string $id)
	{
		$this->id = $id;
	}
}