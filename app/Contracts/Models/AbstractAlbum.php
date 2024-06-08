<?php

namespace App\Contracts\Models;

use App\Models\AccessPermission;
use App\Models\Extensions\Thumb;
use App\Models\Photo;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

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
 * @property string                           $id
 * @property string                           $title
 * @property Collection<int,Photo>            $photos
 * @property Thumb|null                       $thumb
 * @property Collection<int,AccessPermission> $access_permissions
 *
 * @extends Arrayable<string,string|int|float|string[]|null>
 */
interface AbstractAlbum extends \JsonSerializable, Arrayable, Jsonable
{
	/**
	 * @return Relation<Photo>|Builder<Photo>
	 */
	public function photos(): Relation|Builder;

	/**
	 * Returns the permissions for the public user.
	 *
	 * @return ?AccessPermission
	 */
	public function public_permissions(): AccessPermission|null;
}
