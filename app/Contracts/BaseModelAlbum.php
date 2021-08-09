<?php

namespace App\Contracts;

use App\Models\BaseModelAlbumImpl;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
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
 * @property bool               $viewable      // rename, on different layer of this application this attribute goes by different names: "hidden", "need_direct_link", etc.
 * @property bool               $nsfw
 * @property bool               $full_photo
 * @property int                $owner_id
 * @property User               $owner
 * @property Collection         $shared_with
 * @property string|null        $password
 * @property bool               $has_password
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
}
