<?php

namespace App\Models\Extensions;

use App\Contracts\AbstractAlbum;
use App\Models\BaseAlbumImpl;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;

/**
 * Interface BaseAlbum.
 *
 * This is the common interface for all albums which can be created and
 * deleted by a user at runtime or more accurately which can be persisted
 * to the DB.
 *
 * @property int           $id
 * @property Carbon        $created_at
 * @property Carbon        $updated_at
 * @property string|null   $description
 * @property bool          $is_nsfw
 * @property bool          $grants_full_photo
 * @property int           $owner_id
 * @property User          $owner
 * @property Collection    $shared_with
 * @property bool          $requires_link
 * @property string|null   $password
 * @property bool          $has_password
 * @property Carbon|null   $min_taken_at
 * @property Carbon|null   $max_taken_at
 * @property string|null   $sorting_col
 * @property string|null   $sorting_order
 * @property BaseAlbumImpl $base_class
 */
abstract class BaseAlbum extends Model implements AbstractAlbum
{
	abstract public function owner(): BelongsTo;

	abstract public function shared_with(): BelongsToMany;

	abstract public function photos(): Relation;
}
