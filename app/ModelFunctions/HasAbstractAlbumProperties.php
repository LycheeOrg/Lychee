<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\ModelFunctions;

use App\Models\AccessPermission;
use App\Models\Extensions\Thumb;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property string                                $id
 * @property string                                $title
 * @property Collection<int,Photo>|null            $photos
 * @property Thumb|null                            $thumb
 * @property Collection<int,AccessPermission>|null $access_permissions
 */
trait HasAbstractAlbumProperties
{
	public function get_id(): string
	{
		return $this->id;
	}

	public function get_thumb(): Thumb|null
	{
		return $this->thumb;
	}

	public function get_title(): string
	{
		return $this->title;
	}

	/**
	 * @return Collection<int,AccessPermission>
	 */
	public function get_access_permissions(): Collection
	{
		return $this->access_permissions ?? collect();
	}

	/**
	 * @return Collection<int,Photo>
	 */
	public function get_photos(): Collection
	{
		return $this->photos ?? collect();
	}
}