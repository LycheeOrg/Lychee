<?php

namespace App\Contracts;

use App\Models\BaseModelAlbumImpl;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Interface BaseModelAlbum.
 *
 * This is the common interface for all albums which can be created and
 * deleted by a user at runtime or more accurately which can be persisted
 * to the DB.
 *
 * @property int                $id
 * @property Carbon             $created_at
 * @property Carbon             $updated_at
 * @property string|null        $description
 * @property bool               $nsfw
 * @property bool               $full_photo
 * @property int                $owner_id
 * @property User               $owner
 * @property Collection         $shared_with
 * @property bool               $requires_link
 * @property string|null        $password
 * @property bool               $has_password
 * @property Carbon|null        $min_taken_at
 * @property Carbon|null        $max_taken_at
 * @property string|null        $sorting_col
 * @property string|null        $sorting_order
 * @property BaseModelAlbumImpl $base_class
 */
interface BaseModelAlbum extends BaseAlbum
{
	/**
	 * Save the model to the database.
	 *
	 * @param array $options
	 *
	 * @return bool
	 */
	public function save(array $options = []);

	public function owner(): BelongsTo;

	public function shared_with(): BelongsToMany;
}
