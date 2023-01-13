<?php

namespace App\Contracts\Models;

use App\Models\Extensions\Thumb;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Wireable;

/**
 * Interface AbsractAlbum.
 *
 * This is the common interface with the minimal set of functions which is
 * provided by *all* albums even the true smart albums like the album
 * of recent photos, starred photos etc. which exist purely virtual and are
 * not persisted to DB.
 * Hence, this interface does *not* declares properties which are typical
 * for persistable models like `created_at`, etc., because the built-in
 * smart models exist "forever".
 * See {@link \App\Models\Extensions\BaseAlbum} for the common interface of
 * all models which are persisted to DB.
 *
 * @property string     $id
 * @property string     $title
 * @property Collection $photos
 * @property Thumb|null $thumb
 * @property bool       $is_public
 * @property bool       $grants_download
 * @property bool       $grants_full_photo_access
 */
interface AbstractAlbum extends \JsonSerializable, Arrayable, Jsonable, Wireable
{
	/**
	 * @return Relation|Builder
	 */
	public function photos(): Relation|Builder;
}
