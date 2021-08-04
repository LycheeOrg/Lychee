<?php

namespace App\Contracts;

use App\Models\BaseAlbumImpl;
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
 * @property int           $id
 * @property Carbon        $created_at
 * @property Carbon        $updated_at
 * @property string|null   $description
 * @property bool          $viewable             // rename, on different layer of this application this attribute goes by different names: "hidden", "need_direct_link", etc.
 * @property bool          $downloadable
 * @property bool          $share_button_visible
 * @property bool          $nsfw
 * @property User          $owner
 * @property Collection    $shared_with
 * @property string|null   $password
 * @property string|null   $sorting_col
 * @property string|null   $sorting_order
 * @property BaseAlbumImpl $base_class
 */
interface BaseModelAlbum extends BaseAlbum
{
}
