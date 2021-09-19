<?php

namespace App\Contracts;

use App\Exceptions\ModelDBException;
use App\Models\Extensions\Thumb;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

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
 * See {@link \App\Contracts\BaseAlbum} for the common interface of
 * all models which are persisted to DB.
 *
 * @property string     $title
 * @property Collection $photos
 * @property Thumb|null $thumb
 * @property bool       $is_public
 * @property bool       $is_downloadable
 * @property bool       $is_share_button_visible
 */
interface AbstractAlbum extends \JsonSerializable, Arrayable, SupportsRelationships
{
	public function photos(): Relation;

	/**
	 * @return bool always return true
	 *
	 * @throws ModelDBException thrown on failure
	 */
	public function delete(): bool;
}
