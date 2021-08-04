<?php

namespace App\Contracts;

use App\Models\Photo;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface BaseAlbum.
 *
 * This is the common interface with the minimal set of functions which is
 * provided by *all* albums even the true smart albums like the album
 * of recent photos, starred photos etc. which exist purely virtual and are
 * not persisted to DB.
 * Hence, this interface does *not* declares properties which are typical
 * for persistable models like `created_at`, etc., because the the built-in
 * smart models exist "forever".
 * See {@link \App\Contracts\BaseModelAlbum} for the common interface of
 * all models which are persisted to DB.
 *
 * @property string     title
 * @property Collection photos
 * @property Photo      cover
 * @property bool       public
 * @property bool       full_photo
 */
interface BaseAlbum extends \JsonSerializable, Arrayable
{
}
